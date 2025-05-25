<?php

namespace Gtrends\Sdk\Tests\Laravel;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Laravel\GtrendsServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ServiceProviderTest extends TestCase
{
    /** @test */
    public function itRegistersClientAsSingleton(): void
    {
        $client1 = $this->app->make(Client::class);
        $client2 = $this->app->make(Client::class);

        $this->assertInstanceOf(Client::class, $client1);
        $this->assertSame($client1, $client2);
    }

    /** @test */
    public function itLoadsConfigFromLaravelConfig(): void
    {
        $client = $this->app->make(Client::class);

        // Get config from client
        $config = $this->getClientConfig($client);

        // Verify config values
        $this->assertEquals('https://test-api.example.com', $config->get('api_base_uri'));
        $this->assertEquals(15, $config->get('api_timeout'));
    }

    /** @test */
    public function itPublishesConfigFile(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => GtrendsServiceProvider::class,
            '--tag' => 'config',
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('gtrends.php'));
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array: array
    {
        return [
            GtrendsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('gtrends.api_base_uri', 'https://test-api.example.com');
        $app['config']->set('gtrends.api_timeout', 15);
    }

    /**
     * Helper method to get config from client using reflection.
     *
     * @return Config
     */
    private function getClientConfig(Client $client): Config: Config
    {
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);

        return $property->getValue($client);
    }
}
