<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache TTL
    |--------------------------------------------------------------------------
    |
    | The default cache time-to-live (TTL) for data sources in seconds.
    | This will be used if no TTL is specified when creating a data source.
    |
    */

    'default_cache_ttl' => env('DATA_SOURCE_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Supported Source Types
    |--------------------------------------------------------------------------
    |
    | The types of data sources that are supported by the system.
    |
    */

    'source_types' => [
        'database' => [
            'label' => 'Database Query',
            'description' => 'Fetch data from database using SQL queries',
            'icon' => '🗄️',
        ],
        'url' => [
            'label' => 'URL/File',
            'description' => 'Fetch data from a URL (JSON, XML, CSV, RSS)',
            'icon' => '🌐',
        ],
        'api' => [
            'label' => 'API Endpoint',
            'description' => 'Fetch data from external APIs with authentication',
            'icon' => '🔌',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Authentication Types
    |--------------------------------------------------------------------------
    |
    | Authentication methods available for API data sources.
    |
    */

    'auth_types' => [
        'none' => 'No Authentication',
        'bearer' => 'Bearer Token',
        'api_key' => 'API Key',
        'basic' => 'Basic Auth',
        'oauth2' => 'OAuth 2.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Data Formats
    |--------------------------------------------------------------------------
    |
    | Data formats that can be parsed from URLs and APIs.
    |
    */

    'formats' => [
        'json' => 'JSON',
        'xml' => 'XML',
        'csv' => 'CSV',
        'rss' => 'RSS/Atom Feed',
        'text' => 'Plain Text',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout for HTTP requests in seconds.
    |
    */

    'http_timeout' => env('DATA_SOURCE_HTTP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Items Per Source
    |--------------------------------------------------------------------------
    |
    | Maximum number of items to cache per data source. Set to null for unlimited.
    |
    */

    'max_items_per_source' => env('DATA_SOURCE_MAX_ITEMS', null),

];
