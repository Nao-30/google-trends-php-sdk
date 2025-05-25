<?php

namespace Gtrends\Sdk\Tests\Laravel;

use Orchestra\Testbench\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Laravel\GtrendsServiceProvider;

class ServiceProviderTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            GtrendsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('gtrends.api_base_uri', 'https://test-api.example.com');
        $app['config']->set('gtrends.api_timeout', 15);
    }

    /** @test */
    public function it_registers_client_as_singleton()
    {
        $client1 = $this->app->make(Client::class);
        $client2 = $this->app->make(Client::class);

        $this->assertInstanceOf(Client::class, $client1);
        $this->assertSame($client1, $client2);
    }

    /** @test */
    public function it_loads_config_from_laravel_config()
    {
        $client = $this->app->make(Client::class);

        // Get config from client
        $config = $this->getClientConfig($client);

        // Verify config values
        $this->assertEquals('https://test-api.example.com', $config->get('api_base_uri'));
        $this->assertEquals(15, $config->get('api_timeout'));
    }

    /** @test */
    public function it_publishes_config_file()
    {
        $this->artisan('vendor:publish', [
            '--provider' => GtrendsServiceProvider::class,
            '--tag' => 'config'
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('gtrends.php'));
    }

    /**
     * Helper method to get config from client using reflection
     *
     * @param Client $client
     * @return \Gtrends\Sdk\Configuration\Config
     */
    private function getClientConfig(Client $client)
    {
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);

        return $property->getValue($client);
    }
}
