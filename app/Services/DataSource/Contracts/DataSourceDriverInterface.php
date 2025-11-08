<?php

declare(strict_types=1);

namespace App\Services\DataSource\Contracts;

use App\Models\DataSource;

interface DataSourceDriverInterface
{
    /**
     * Fetch data from the source
     *
     * @param DataSource $source
     * @return array
     * @throws \Exception
     */
    public function fetch(DataSource $source): array;

    /**
     * Validate the configuration
     *
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    public function validateConfig(array $config): bool;

    /**
     * Test the connection with given configuration
     *
     * @param array $config
     * @return array
     */
    public function testConnection(array $config): array;
}
