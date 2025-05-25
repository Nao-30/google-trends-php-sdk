<?php

namespace Gtrends\Sdk\Tests\Laravel;

use Orchestra\Testbench\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Laravel\GtrendsServiceProvider;
use Gtrends\Sdk\Laravel\Facades\Gtrends;

class FacadeTest extends TestCase
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
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app)
    {
        return [
            'Gtrends' => Gtrends::class,
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
    }

    /** @test */
    public function it_resolves_to_the_correct_underlying_instance()
    {
        $this->assertInstanceOf(Client::class, app(Client::class));
        $this->assertInstanceOf(Client::class, Gtrends::getFacadeRoot());
    }

    /** @test */
    public function it_proxies_method_calls_to_client()
    {
        // Mock the client
        $mock = $this->mock(Client::class);
        $mock->shouldReceive('getTrending')
            ->once()
            ->with(['region' => 'US'])
            ->andReturn(['status' => 'success', 'data' => []]);

        $this->app->instance(Client::class, $mock);

        // Call facade method
        $result = Gtrends::getTrending(['region' => 'US']);

        // Verify result
        $this->assertEquals(['status' => 'success', 'data' => []], $result);
    }

    /** @test */
    public function it_passes_all_api_method_calls_to_client()
    {
        // Mock the client
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('getRelatedTopics')
            ->once()
            ->with(['keyword' => 'php'])
            ->andReturn(['status' => 'success']);

        $mock->shouldReceive('getRelatedQueries')
            ->once()
            ->with(['keyword' => 'php'])
            ->andReturn(['status' => 'success']);

        $mock->shouldReceive('getComparison')
            ->once()
            ->with(['topics' => ['php', 'javascript']])
            ->andReturn(['status' => 'success']);

        $this->app->instance(Client::class, $mock);

        // Call facade methods
        $result1 = Gtrends::getRelatedTopics(['keyword' => 'php']);
        $result2 = Gtrends::getRelatedQueries(['keyword' => 'php']);
        $result3 = Gtrends::getComparison(['topics' => ['php', 'javascript']]);

        // Verify results
        $this->assertEquals(['status' => 'success'], $result1);
        $this->assertEquals(['status' => 'success'], $result2);
        $this->assertEquals(['status' => 'success'], $result3);
    }
}
