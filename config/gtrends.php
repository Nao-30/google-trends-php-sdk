<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Trends API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Google Trends PHP SDK.
    | You can modify these values as needed for your application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI for the Google Trends API endpoints.
    |
    */
    'base_uri' => env('GTRENDS_BASE_URI', 'https://trends.googleapis.com/v1/'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a response from the API.
    |
    */
    'timeout' => env('GTRENDS_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | HTTP Headers
    |--------------------------------------------------------------------------
    |
    | Default HTTP headers to include with every request to the API.
    |
    */
    'headers' => [
        'User-Agent' => 'Gtrends/Sdk/1.0',
        'Accept' => 'application/json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic retry of failed API requests.
    |
    */
    'retry' => [
        // Maximum number of retry attempts
        'max_attempts' => env('GTRENDS_RETRY_MAX_ATTEMPTS', 3),
        
        // Initial delay in milliseconds before retrying
        'delay' => env('GTRENDS_RETRY_DELAY', 1000),
        
        // Multiplier for exponential backoff
        'multiplier' => env('GTRENDS_RETRY_MULTIPLIER', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for paginated API responses.
    |
    */
    'pagination' => [
        // Number of items per page
        'per_page' => env('GTRENDS_PAGINATION_PER_PAGE', 20),
        
        // Maximum number of items to retrieve in total
        'max_items' => env('GTRENDS_PAGINATION_MAX_ITEMS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debugging information will be logged.
    |
    */
    'debug' => env('GTRENDS_DEBUG', false),
]; 