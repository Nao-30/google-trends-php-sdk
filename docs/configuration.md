# Google Trends PHP SDK - Configuration

This document details the configuration options available for the Google Trends PHP SDK.

## Basic Configuration

When instantiating the SDK client, you can pass a configuration array with various options:

```php
$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 30,
    'retry_attempts' => 3,
    'debug' => false
]);
```

## Available Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `base_uri` | string | - | **Required**. The base URI of the Google Trends API |
| `timeout` | int | 30 | Request timeout in seconds |
| `retry_attempts` | int | 3 | Number of retry attempts for failed requests |
| `retry_delay` | int | 1000 | Delay between retry attempts in milliseconds |
| `user_agent` | string | 'Gtrends-PHP-SDK/1.0' | User agent string sent with requests |
| `debug` | bool | false | Enable debug mode for detailed logging |
| `http_errors` | bool | true | Throw exceptions for HTTP errors |
| `verify` | bool | true | Verify SSL certificates |
| `proxy` | string | null | HTTP proxy URL |
| `cache_enabled` | bool | false | Enable response caching |
| `cache_ttl` | int | 3600 | Cache time-to-live in seconds |

## Environment Variables

The SDK supports configuration through environment variables. The following variables can be used:

| Environment Variable | Configuration Option |
|----------------------|----------------------|
| `GTRENDS_BASE_URI` | base_uri |
| `GTRENDS_TIMEOUT` | timeout |
| `GTRENDS_RETRY_ATTEMPTS` | retry_attempts |
| `GTRENDS_RETRY_DELAY` | retry_delay |
| `GTRENDS_USER_AGENT` | user_agent |
| `GTRENDS_DEBUG` | debug |
| `GTRENDS_HTTP_ERRORS` | http_errors |
| `GTRENDS_VERIFY_SSL` | verify |
| `GTRENDS_PROXY` | proxy |
| `GTRENDS_CACHE_ENABLED` | cache_enabled |
| `GTRENDS_CACHE_TTL` | cache_ttl |

Example using environment variables:

```php
// .env file
GTRENDS_BASE_URI=https://trends-api-url.com
GTRENDS_TIMEOUT=60
GTRENDS_DEBUG=true

// PHP code
$client = new Gtrends\Sdk\Client(); // Will use environment variables
```

## Using Config Files

You can also use a configuration file to load settings:

```php
$client = new Gtrends\Sdk\Client();
$client->loadConfigFile('/path/to/config.php');
```

Example configuration file:

```php
<?php
// config.php
return [
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 60,
    'retry_attempts' => 5,
    'debug' => true
];
```

## Configuration Priority

The SDK uses the following priority order for configuration (highest to lowest):

1. Options passed directly to the constructor
2. Environment variables
3. Configuration file
4. Default values

## Laravel-Specific Configuration

When using the SDK with Laravel, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="Gtrends\Sdk\Laravel\GtrendsServiceProvider"
```

This will create a `config/gtrends.php` file that you can modify:

```php
<?php
// config/gtrends.php
return [
    'base_uri' => env('GTRENDS_BASE_URI', 'https://trends-api-url.com'),
    'timeout' => env('GTRENDS_TIMEOUT', 30),
    'retry_attempts' => env('GTRENDS_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('GTRENDS_RETRY_DELAY', 1000),
    'user_agent' => env('GTRENDS_USER_AGENT', 'Gtrends-PHP-SDK/1.0'),
    'debug' => env('GTRENDS_DEBUG', false),
    'http_errors' => env('GTRENDS_HTTP_ERRORS', true),
    'verify' => env('GTRENDS_VERIFY_SSL', true),
    'proxy' => env('GTRENDS_PROXY', null),
    'cache' => [
        'enabled' => env('GTRENDS_CACHE_ENABLED', false),
        'ttl' => env('GTRENDS_CACHE_TTL', 3600),
        'store' => null, // Uses the default cache store
    ],
];
```

## Runtime Configuration Changes

You can change configuration values at runtime:

```php
$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com'
]);

// Change configuration at runtime
$client->setConfig('timeout', 60);
$client->setConfig('retry_attempts', 5);

// Get current configuration value
$timeout = $client->getConfig('timeout'); // 60

// Get all configuration values
$config = $client->getConfig();
```

## Advanced Configuration

### Logging

You can configure a custom PSR-3 compatible logger:

```php
$logger = new MyCustomLogger();
$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'logger' => $logger
]);
```

### HTTP Client

You can provide your own Guzzle HTTP client with custom middleware:

```php
$stack = HandlerStack::create();
$stack->push(Middleware::retry(/* custom retry logic */));

$httpClient = new \GuzzleHttp\Client([
    'handler' => $stack
]);

$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'http_client' => $httpClient
]);
```

### Cache Storage

You can customize the cache storage implementation:

```php
$cache = new CustomCacheImplementation();

$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'cache_enabled' => true,
    'cache_storage' => $cache
]);
```

## Debugging

When debug mode is enabled, the SDK will log detailed information about requests and responses:

```php
$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'debug' => true
]);

// The following request will generate detailed logs
$trending = $client->getTrending('US');
```

You can examine these logs to troubleshoot issues with the SDK or the API. 