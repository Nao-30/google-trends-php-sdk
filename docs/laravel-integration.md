# Google Trends PHP SDK - Laravel Integration

This document provides instructions for integrating the Google Trends PHP SDK with Laravel applications.

## Installation

First, install the package via Composer:

```bash
composer require gtrends/gtrends-php-sdk
```

## Service Provider Registration

The SDK includes a Laravel service provider that integrates with your application automatically.

### Laravel 5.5+ (Package Auto-Discovery)

If you're using Laravel 5.5 or higher, the package will be automatically discovered and registered.

### Laravel 5.4 and below

For Laravel 5.4 and below, add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // Other service providers...
    Gtrends\Laravel\GtrendsServiceProvider::class,
],
```

## Facade Registration

If you'd like to use the facade, add it to the `aliases` array in `config/app.php`:

```php
'aliases' => [
    // Other facades...
    'Gtrends' => Gtrends\Laravel\Facades\Gtrends::class,
],
```

## Configuration

### Publishing the Configuration

To publish the configuration file, run:

```bash
php artisan vendor:publish --provider="Gtrends\Laravel\GtrendsServiceProvider"
```

This will create a `config/gtrends.php` file in your application.

### Configuration Options

The configuration file includes the following options:

```php
<?php
// config/gtrends.php
return [
    // API Connection
    'base_uri' => env('GTRENDS_BASE_URI', 'https://trends-api-url.com'),
    'timeout' => env('GTRENDS_TIMEOUT', 30),
    
    // Retry Settings
    'retry_attempts' => env('GTRENDS_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('GTRENDS_RETRY_DELAY', 1000),
    
    // HTTP Options
    'user_agent' => env('GTRENDS_USER_AGENT', 'Gtrends-PHP-SDK/1.0'),
    'debug' => env('GTRENDS_DEBUG', false),
    'http_errors' => env('GTRENDS_HTTP_ERRORS', true),
    'verify' => env('GTRENDS_VERIFY_SSL', true),
    'proxy' => env('GTRENDS_PROXY', null),
    
    // Cache Configuration
    'cache' => [
        'enabled' => env('GTRENDS_CACHE_ENABLED', false),
        'ttl' => env('GTRENDS_CACHE_TTL', 3600),
        'store' => null, // Uses the default cache store
    ],
    
    // Default Parameters
    'defaults' => [
        'geo' => env('GTRENDS_DEFAULT_GEO', 'US'),
        'timeframe' => env('GTRENDS_DEFAULT_TIMEFRAME', 'past-30d'),
    ],
];
```

### Environment Variables

Add the following variables to your `.env` file to configure the SDK:

```
GTRENDS_BASE_URI=https://trends-api-url.com
GTRENDS_TIMEOUT=30
GTRENDS_RETRY_ATTEMPTS=3
GTRENDS_CACHE_ENABLED=true
GTRENDS_CACHE_TTL=3600
```

## Basic Usage

### Using the Facade

```php
<?php

namespace App\Http\Controllers;

use Gtrends\Laravel\Facades\Gtrends;
use Illuminate\Http\Request;

class TrendsController extends Controller
{
    public function index(Request $request)
    {
        $geo = $request->input('geo', 'US');
        $trending = Gtrends::getTrending($geo);
        
        return view('trends.index', ['trending' => $trending]);
    }
    
    public function related(Request $request)
    {
        $keyword = $request->input('keyword');
        $relatedTopics = Gtrends::getRelatedTopics($keyword, [
            'geo' => $request->input('geo', 'US'),
            'timeframe' => $request->input('timeframe', 'past-30d')
        ]);
        
        return view('trends.related', [
            'keyword' => $keyword,
            'relatedTopics' => $relatedTopics
        ]);
    }
}
```

### Using Dependency Injection

```php
<?php

namespace App\Http\Controllers;

use Gtrends\Contracts\ClientInterface;
use Illuminate\Http\Request;

class TrendsController extends Controller
{
    protected $trendsClient;
    
    public function __construct(ClientInterface $trendsClient)
    {
        $this->trendsClient = $trendsClient;
    }
    
    public function index(Request $request)
    {
        $geo = $request->input('geo', 'US');
        $trending = $this->trendsClient->getTrending($geo);
        
        return view('trends.index', ['trending' => $trending]);
    }
}
```

## Advanced Usage

### Custom Configuration at Runtime

```php
$trending = Gtrends::withConfig(['timeout' => 60])->getTrending('US');
```

### Using with Middleware

You can create middleware to handle API errors:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Gtrends\Exceptions\ApiException;
use Gtrends\Exceptions\NetworkException;

class HandleTrendsApiErrors
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (NetworkException $e) {
            return response()->json([
                'error' => 'Network error when connecting to Trends API',
                'message' => $e->getMessage()
            ], 503);
        } catch (ApiException $e) {
            return response()->json([
                'error' => 'Google Trends API error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }
}
```

### Caching Responses

The SDK integrates with Laravel's cache system:

```php
// config/gtrends.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'store' => 'redis', // Use a specific cache store
],
```

### Customizing the HTTP Client

You can customize the underlying HTTP client in a service provider:

```php
<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\ServiceProvider;
use Gtrends\Contracts\ClientInterface;

class TrendsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(ClientInterface::class, function ($client, $app) {
            $stack = HandlerStack::create();
            $stack->push(Middleware::retry(/* custom retry logic */));
            
            $httpClient = new Client([
                'handler' => $stack,
                // Other custom configuration
            ]);
            
            return $client->withHttpClient($httpClient);
        });
    }
}
```

## Laravel-Specific Features

### Artisan Commands

The SDK provides useful Artisan commands:

```bash
# Check Google Trends API health
php artisan gtrends:health

# Get trending searches
php artisan gtrends:trending {geo?}

# Clear Gtrends cache
php artisan gtrends:clear-cache
```

### Scheduled Commands

You can schedule API calls in your `app/Console/Kernel.php` file:

```php
protected function schedule(Schedule $schedule)
{
    // Check API health every hour
    $schedule->command('gtrends:health')->hourly();
    
    // Cache trending searches every day
    $schedule->command('gtrends:trending')->daily();
}
```

### Events

The SDK dispatches Laravel events:

```php
<?php

namespace App\Providers;

use Gtrends\Laravel\Events\ApiRequestFailed;
use Gtrends\Laravel\Events\TrendingSearchesRetrieved;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ApiRequestFailed::class => [
            'App\Listeners\LogFailedApiRequest',
        ],
        TrendingSearchesRetrieved::class => [
            'App\Listeners\ProcessTrendingSearches',
        ],
    ];
}
```

## Troubleshooting

### Logging

The SDK integrates with Laravel's logging system. To enable detailed logging:

```php
// config/gtrends.php
'debug' => env('GTRENDS_DEBUG', true),
```

Logs will be written to your Laravel log file.

### Testing

When testing Laravel components that use the SDK, you can mock the facade:

```php
<?php

namespace Tests\Feature;

use Gtrends\Laravel\Facades\Gtrends;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendsControllerTest extends TestCase
{
    public function testIndex()
    {
        Gtrends::shouldReceive('getTrending')
            ->once()
            ->with('US')
            ->andReturn(['trends' => ['example']]);
            
        $response = $this->get('/trends');
        
        $response->assertStatus(200);
        $response->assertViewHas('trending', ['trends' => ['example']]);
    }
}
``` 