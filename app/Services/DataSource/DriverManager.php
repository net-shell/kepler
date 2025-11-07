<?php

namespace App\Services\DataSource;

use App\Models\DataSource;
use App\Services\DataSource\Contracts\DataSourceDriverInterface;
use App\Services\DataSource\Drivers\DatabaseDriver;
use App\Services\DataSource\Drivers\UrlDriver;
use App\Services\DataSource\Drivers\ApiDriver;
use App\Services\DataSource\Parsers\DataParserFactory;
use Exception;

class DriverManager
{
    /**
     * Registered drivers
     *
     * @var array
     */
    protected array $drivers = [];

    /**
     * Parser factory instance
     *
     * @var DataParserFactory
     */
    protected DataParserFactory $parserFactory;

    /**
     * Create a new driver manager instance
     */
    public function __construct()
    {
        $this->parserFactory = new DataParserFactory();
        $this->registerDefaultDrivers();
    }

    /**
     * Register default drivers
     *
     * @return void
     */
    protected function registerDefaultDrivers(): void
    {
        $this->register(DataSource::TYPE_DATABASE, function () {
            return new DatabaseDriver();
        });

        $this->register(DataSource::TYPE_URL, function () {
            return new UrlDriver($this->parserFactory);
        });

        $this->register(DataSource::TYPE_API, function () {
            return new ApiDriver($this->parserFactory);
        });
    }

    /**
     * Register a custom driver
     *
     * @param string $type
     * @param callable $factory
     * @return void
     */
    public function register(string $type, callable $factory): void
    {
        $this->drivers[$type] = $factory;
    }

    /**
     * Get a driver instance for the given type
     *
     * @param string $type
     * @return DataSourceDriverInterface
     * @throws \Exception
     */
    public function driver(string $type): DataSourceDriverInterface
    {
        if (!isset($this->drivers[$type])) {
            throw new Exception("Unknown data source type: {$type}");
        }

        return call_user_func($this->drivers[$type]);
    }

    /**
     * Get all registered driver types
     *
     * @return array
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Get the parser factory
     *
     * @return DataParserFactory
     */
    public function getParserFactory(): DataParserFactory
    {
        return $this->parserFactory;
    }
}
