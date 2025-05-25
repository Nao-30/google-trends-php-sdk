<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Trends API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL used for all API requests to the Google Trends API.
    | You typically should not need to change this value.
    |
    */
    'base_url' => env('GTRENDS_BASE_URL', 'https://api.gtrends.app/v1'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Google Trends API key. This is required for authentication with the
    | Google Trends API service. You can obtain one by registering at
    | https://api.gtrends.app
    |
    */
    'api_key' => env('GTRENDS_API_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout value in seconds for API requests. If a request takes longer
    | than this value, it will be aborted.
    |
    */
    'timeout' => env('GTRENDS_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for request retry behavior when API requests fail.
    |
    */
    'retry' => [
        'max_attempts' => env('GTRENDS_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('GTRENDS_RETRY_DELAY', 1000), // in milliseconds
        'multiplier' => env('GTRENDS_RETRY_MULTIPLIER', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching API responses to reduce API calls and improve
    | performance.
    |
    */
    'cache' => [
        'enabled' => env('GTRENDS_CACHE_ENABLED', true),
        'ttl' => env('GTRENDS_CACHE_TTL', 3600), // in seconds (1 hour default)
        'prefix' => env('GTRENDS_CACHE_PREFIX', 'gtrends_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When debug mode is enabled, the client will log additional information
    | about requests and responses. This is useful for debugging issues.
    |
    */
    'debug' => env('GTRENDS_DEBUG', false),
];
