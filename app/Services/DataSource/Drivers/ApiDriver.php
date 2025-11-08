<?php

declare(strict_types=1);

namespace App\Services\DataSource\Drivers;

use App\Models\DataSource;
use App\Services\DataSource\Parsers\DataParserFactory;
use Illuminate\Support\Facades\Http;
use Exception;

class ApiDriver extends AbstractDriver
{
    public function __construct(
        protected DataParserFactory $parserFactory
    ) {}

    /**
     * Fetch data from API source with authentication
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
            $http = Http::timeout($config['timeout'] ?? 30);

            // Add authentication
            $authType = $config['auth_type'] ?? DataSource::AUTH_NONE;
            $http = $this->addAuthentication($http, $authType, $config);

            // Add custom headers
            if (!empty($config['headers'])) {
                foreach ($config['headers'] as $key => $value) {
                    $http = $http->withHeader($key, $value);
                }
            }

            // Make request
            $method = strtolower($config['method'] ?? 'get');
            $response = match ($method) {
                'post' => $http->post($config['url'], $config['body'] ?? []),
                'put' => $http->put($config['url'], $config['body'] ?? []),
                'delete' => $http->delete($config['url']),
                default => $http->get($config['url'], $config['params'] ?? []),
            };

            if (!$response->successful()) {
                throw new Exception("API request failed with status: {$response->status()}");
            }

            $format = $config['format'] ?? 'json';
            $parser = $this->parserFactory->make($format);
            $data = $parser->parse($response->body());

            // Extract data from nested path if specified
            if (!empty($config['data_path'])) {
                $data = $this->extractDataPath($data, $config['data_path']);
            }

            return $data;
        } catch (\Exception $e) {
            throw new Exception("API fetch failed: {$e->getMessage()}");
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
            throw new Exception("API source requires 'url' in config");
        }

        if (!filter_var($config['url'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL format");
        }

        return true;
    }

    /**
     * Add authentication to HTTP request
     *
     * @param \Illuminate\Http\Client\PendingRequest $http
     * @param string $authType
     * @param array $config
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function addAuthentication($http, string $authType, array $config)
    {
        return match ($authType) {
            DataSource::AUTH_BEARER => $http->withToken($config['token'] ?? ''),
            DataSource::AUTH_API_KEY => $http->withHeader(
                $config['api_key_header'] ?? 'X-API-Key',
                $config['api_key'] ?? ''
            ),
            DataSource::AUTH_BASIC => $http->withBasicAuth(
                $config['username'] ?? '',
                $config['password'] ?? ''
            ),
            DataSource::AUTH_OAUTH2 => $http->withToken($config['access_token'] ?? ''),
            default => $http,
        };
    }

    /**
     * Extract data from nested path (e.g., "data.items")
     *
     * @param array $data
     * @param string $path
     * @return array
     * @throws \Exception
     */
    protected function extractDataPath(array $data, string $path): array
    {
        $segments = explode('.', $path);

        foreach ($segments as $segment) {
            if (!is_array($data)) {
                throw new Exception("Invalid data path: {$path}");
            }

            if (!isset($data[$segment])) {
                throw new Exception("Data path not found: {$path}");
            }

            $data = $data[$segment];
        }

        return $this->normalizeData($data);
    }

    /**
     * Get the driver type identifier
     *
     * @return string
     */
    protected function getType(): string
    {
        return DataSource::TYPE_API;
    }
}
