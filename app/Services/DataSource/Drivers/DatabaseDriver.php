<?php

namespace App\Services\DataSource\Drivers;

use App\Models\DataSource;
use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseDriver extends AbstractDriver
{
    /**
     * Fetch data from database source
     *
     * @param DataSource $source
     * @return array
     * @throws \Exception
     */
    public function fetch(DataSource $source): array
    {
        $config = $source->config;
        $this->validateConfig($config);

        try {
            // Use configured connection or default
            $connection = $config['connection'] ?? config('database.default');

            $results = DB::connection($connection)
                ->select($config['query']);

            return array_map(fn($row) => (array) $row, $results);
        } catch (\Exception $e) {
            throw new Exception("Database query failed: {$e->getMessage()}");
        }
    }

    /**
     * Validate the configuration
     *
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    public function validateConfig(array $config): bool
    {
        if (empty($config['query'])) {
            throw new Exception("Database source requires 'query' in config");
        }

        return true;
    }

    /**
     * Get the driver type identifier
     *
     * @return string
     */
    protected function getType(): string
    {
        return DataSource::TYPE_DATABASE;
    }
}
