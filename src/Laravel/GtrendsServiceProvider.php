<?php

namespace Gtrends\Sdk\Laravel;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Illuminate\Support\ServiceProvider;

class GtrendsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config/gtrends.php', 'gtrends'
        );

        // Register Client as singleton
        $this->app->singleton(Client::class, function ($app) {
            $config = new Config($app['config']->get('gtrends'));
            return new Client($config);
        });

        // Register facade
        $this->app->alias(Client::class, 'gtrends');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/gtrends.php' => config_path('gtrends.php'),
        ], 'gtrends-config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class, 'gtrends'];
    }
} 