<?php

namespace App\Services;

use App\Models\DataSource;
use App\Services\DataSource\DriverManager;
use Illuminate\Support\Facades\Log;
use Exception;

class DataSourceService
{
    /**
     * Driver manager instance
     *
     * @var DriverManager
     */
    protected DriverManager $driverManager;

    /**
     * Create a new data source service instance
     */
    public function __construct(DriverManager $driverManager)
    {
        $this->driverManager = $driverManager;
    }

    /**
     * Fetch data from a data source
     *
     * @param DataSource $source
     * @param bool $useCache
     * @return array
     * @throws \Exception
     */
    public function fetchData(DataSource $source, bool $useCache = true): array
    {
        // Return cached data if valid and cache is enabled
        if ($useCache && $source->isCacheValid()) {
            return $source->cached_data ?? [];
        }

        // Get the appropriate driver and fetch data
        $driver = $this->driverManager->driver($source->type);
        $data = $driver->fetch($source);

        // Update cache
        $source->update([
            'cached_data' => $data,
            'last_cached_at' => now(),
        ]);

        return $data;
    }

    /**
     * Test a data source connection
     *
     * @param array $config
     * @param string $type
     * @return array
     */
    public function testConnection(array $config, string $type): array
    {
        try {
            $driver = $this->driverManager->driver($type);
            return $driver->testConnection($config);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all data from all enabled sources
     *
     * @param bool $useCache
     * @return array
     */
    public function getAllSourcesData(bool $useCache = true): array
    {
        $sources = DataSource::enabled()->get();
        $allData = [];

        foreach ($sources as $source) {
            try {
                $data = $this->fetchData($source, $useCache);

                // Add source metadata to each item
                foreach ($data as $item) {
                    $allData[] = array_merge($item, [
                        '_source_id' => $source->id,
                        '_source_name' => $source->name,
                        '_source_type' => $source->type,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but continue with other sources
                Log::error("Failed to fetch data from source {$source->id}: {$e->getMessage()}");
            }
        }

        return $allData;
    }

    /**
     * Get the driver manager
     *
     * @return DriverManager
     */
    public function getDriverManager(): DriverManager
    {
        return $this->driverManager;
    }
}
