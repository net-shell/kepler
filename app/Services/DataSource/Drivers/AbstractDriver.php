<?php

declare(strict_types=1);

namespace App\Services\DataSource\Drivers;

use App\Models\DataSource;
use App\Services\DataSource\Contracts\DataSourceDriverInterface;
use Exception;

abstract class AbstractDriver implements DataSourceDriverInterface
{
    /**
     * Fetch data from the source
     *
     * @param DataSource $source
     * @return array
     * @throws \Exception
     */
    abstract public function fetch(DataSource $source): array;

    /**
     * Validate the configuration
     *
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    abstract public function validateConfig(array $config): bool;

    /**
     * Test the connection with given configuration
     *
     * @param array $config
     * @return array
     */
    public function testConnection(array $config): array
    {
        try {
            $this->validateConfig($config);

            // Create a temporary source for testing
            $tempSource = new DataSource([
                'type' => $this->getType(),
                'config' => $config,
                'cache_ttl' => 0,
            ]);

            $data = $this->fetch($tempSource);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'sample_count' => count($data),
                'sample' => array_slice($data, 0, 3),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the driver type identifier
     *
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * Ensure data is an array of items
     *
     * @param mixed $data
     * @return array
     */
    protected function normalizeData($data): array
    {
        if (!is_array($data)) {
            return [$data];
        }

        // If data is not an array of items, wrap it
        if (!isset($data[0]) || !is_array($data[0])) {
            return [$data];
        }

        return $data;
    }
}
