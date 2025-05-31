# Google Trends PHP SDK

A professional-grade PHP SDK for the Google Trends CLI API. This SDK provides a simple, intuitive interface to interact with the Google Trends API, allowing developers to access trending searches, related topics and queries, comparison data, and more.

## Features

- Seamless integration with Laravel applications
- Compatible with standalone PHP projects
- Support for all Google Trends API endpoints
- Comprehensive error handling
- Extensive documentation and examples

## Requirements

- PHP 8.0 or higher
- JSON extension
- cURL extension
- Composer

## Installation

```bash
composer require gtrends/gtrends-php-sdk
```

## Quick Start

### Standalone PHP

```php
<?php

require 'vendor/autoload.php';

$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 30
]);

// Get trending searches
$trending = $client->getTrending('US');
```

### Laravel Integration

Add the service provider to your `config/app.php` file:

```php
'providers' => [
    // Other service providers...
    Gtrends\Sdk\Laravel\GtrendsServiceProvider::class,
],
```

Publish the configuration:

```bash
php artisan vendor:publish --provider="Gtrends\Sdk\Laravel\GtrendsServiceProvider"
```

Use the facade in your code:

```php
use Gtrends\Sdk\Laravel\Facades\Gtrends;

// Get trending searches
$trending = Gtrends::getTrending('US');
```

## Documentation

For full documentation, examples, and API reference, please see the [docs directory](./docs).

## License

This package is open-sourced software licensed under the MIT license. 