<?php

namespace App\Services\DataSource\Drivers;

use App\Models\DataSource;
use App\Services\DataSource\Parsers\DataParserFactory;
use Illuminate\Support\Facades\Http;
use Exception;

class UrlDriver extends AbstractDriver
{
    protected DataParserFactory $parserFactory;

    public function __construct(DataParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
    }

    /**
     * Fetch data from URL source
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
            $response = Http::timeout($config['timeout'] ?? 30)
                ->get($config['url']);

            if (!$response->successful()) {
                throw new Exception("HTTP request failed with status: {$response->status()}");
            }

            $format = $config['format'] ?? $this->detectFormat(
                $response->header('Content-Type'),
                $config['url']
            );

            $parser = $this->parserFactory->make($format);
            return $parser->parse($response->body());
        } catch (\Exception $e) {
            throw new Exception("URL fetch failed: {$e->getMessage()}");
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
        if (empty($config['url'])) {
            throw new Exception("URL source requires 'url' in config");
        }

        if (!filter_var($config['url'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL format");
        }

        return true;
    }

    /**
     * Detect format from content type or URL
     *
     * @param string|null $contentType
     * @param string $url
     * @return string
     */
    protected function detectFormat(?string $contentType, string $url): string
    {
        // Check content type
        if ($contentType) {
            if (str_contains($contentType, 'json')) return 'json';
            if (str_contains($contentType, 'xml')) return 'xml';
            if (str_contains($contentType, 'csv')) return 'csv';
            if (str_contains($contentType, 'rss')) return 'rss';
        }

        // Check URL extension
        if (str_ends_with($url, '.json')) return 'json';
        if (str_ends_with($url, '.xml')) return 'xml';
        if (str_ends_with($url, '.csv')) return 'csv';
        if (str_ends_with($url, '.rss')) return 'rss';

        // Default to JSON
        return 'json';
    }

    /**
     * Get the driver type identifier
     *
     * @return string
     */
    protected function getType(): string
    {
        return DataSource::TYPE_URL;
    }
}
