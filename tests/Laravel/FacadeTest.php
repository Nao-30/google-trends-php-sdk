<?php

namespace Gtrends\Sdk\Tests\Laravel;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Laravel\Facades\Gtrends;
use Gtrends\Sdk\Laravel\GtrendsServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class FacadeTest extends TestCase
{
    /** @test */
    public function itResolvesToTheCorrectUnderlyingInstance(): void
    {
        $this->assertInstanceOf(Client::class, app(Client::class));
        $this->assertInstanceOf(Client::class, Gtrends::getFacadeRoot());
    }

    /** @test */
    public function itProxiesMethodCallsToClient(): void
    {
        // Mock the client
        $mock = $this->mock(Client::class);
        $mock->shouldReceive('trending')
            ->once()
            ->with('US')
            ->andReturn(['status' => 'success', 'data' => []])
        ;

        $this->app->instance(Client::class, $mock);

        // Call facade method
        $result = Gtrends::trending('US');

        // Verify result
        $this->assertEquals(['status' => 'success', 'data' => []], $result);
    }

    /** @test */
    public function itPassesAllApiMethodCallsToClient(): void
    {
        // Mock the client
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('relatedTopics')
            ->once()
            ->with('php')
            ->andReturn(['status' => 'success'])
        ;

        $mock->shouldReceive('relatedQueries')
            ->once()
            ->with('php')
            ->andReturn(['status' => 'success'])
        ;

        $mock->shouldReceive('compare')
            ->once()
            ->with(['php', 'javascript'])
            ->andReturn(['status' => 'success'])
        ;

        $this->app->instance(Client::class, $mock);

        // Call facade methods
        $result1 = Gtrends::relatedTopics('php');
        $result2 = Gtrends::relatedQueries('php');
        $result3 = Gtrends::compare(['php', 'javascript']);

        // Verify results
        $this->assertEquals(['status' => 'success'], $result1);
        $this->assertEquals(['status' => 'success'], $result2);
        $this->assertEquals(['status' => 'success'], $result3);
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            GtrendsServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param Application $app
     *
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Gtrends' => Gtrends::class,
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
    }
}
