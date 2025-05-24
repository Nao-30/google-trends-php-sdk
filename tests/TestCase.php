<?php

namespace Gtrends\Sdk\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Gtrends\Sdk\Configuration\Config;

/**
 * Base TestCase for all tests
 */
class TestCase extends BaseTestCase
{
    /**
     * History container for Guzzle request history
     *
     * @var array
     */
    protected $historyContainer = [];

    /**
     * Create a mock handler with the given responses
     *
     * @param array $responses Array of GuzzleHttp\Psr7\Response objects
     * @return \GuzzleHttp\Client
     */
    protected function createMockClient(array $responses = []): GuzzleClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        
        // Add history middleware
        $history = Middleware::history($this->historyContainer);
        $handlerStack->push($history);
        
        return new GuzzleClient(['handler' => $handlerStack]);
    }

    /**
     * Load a fixture from the fixtures directory
     *
     * @param string $name Fixture name without extension
     * @return string|null JSON content or null if fixture not found
     */
    protected function loadFixture(string $name): ?string
    {
        $path = __DIR__ . '/fixtures/' . $name . '.json';
        
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        
        return null;
    }

    /**
     * Create a mock response with the given status code and body
     *
     * @param int $statusCode HTTP status code
     * @param string|null $body Response body
     * @param array $headers Response headers
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function createMockResponse(int $statusCode = 200, ?string $body = null, array $headers = []): Response
    {
        return new Response($statusCode, $headers, $body);
    }

    /**
     * Create a default config instance for testing
     *
     * @param array $overrides Config overrides
     * @return \Gtrends\Sdk\Configuration\Config
     */
    protected function createConfig(array $overrides = []): Config
    {
        $defaults = [
            'api_base_uri' => 'https://example.com/api/v1',
            'api_timeout' => 10,
            'retry_attempts' => 3,
            'retry_delay' => 1,
        ];
        
        return new Config(array_merge($defaults, $overrides));
    }
} 