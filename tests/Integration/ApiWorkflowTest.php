<?php

namespace Gtrends\Sdk\Tests\Integration;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Http\ResponseHandler;
use Gtrends\Sdk\Http\HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Gtrends\Sdk\Exceptions\ApiException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiWorkflowTest extends TestCase
{
    /**
     * Create a mock HttpClient that returns predetermined responses
     *
     * @param array $responses Array of Response objects
     * @return HttpClient|MockObject
     */
    protected function createMockHttpClient(array $responses): MockObject
    {
        $mockHttpClient = $this->createMock(HttpClient::class);

        // Configure the mock to return predetermined responses
        $mockHttpClient->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use (&$responses) {
                if (empty($responses)) {
                    $this->fail('No more mock responses available for request: ' . $request->getUri());
                }
                return array_shift($responses);
            });

        return $mockHttpClient;
    }

    /**
     * Set up a client with mock responses
     *
     * @param array $mockResponses Array of Response objects
     * @return Client
     */
    protected function setupClientWithMocks(array $mockResponses): Client
    {
        // Create test config
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);

        $requestBuilder = new RequestBuilder($config);
        $responseHandler = new ResponseHandler($config);

        // Create mock HttpClient
        $mockHttpClient = $this->createMockHttpClient($mockResponses);

        // Create the SDK client
        $client = new Client($config, $requestBuilder, $responseHandler);

        // Replace the HttpClient with our mock
        $clientReflection = new \ReflectionClass(Client::class);
        $httpClientProperty = $clientReflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockHttpClient);

        return $client;
    }

    /** @test */
    public function it_performs_complete_trending_search_workflow()
    {
        // Create fixture data
        $trendingData = $this->loadFixture('trending_success');

        // Create mock responses
        $mockResponses = [
            new Response(200, ['Content-Type' => 'application/json'], $trendingData),
        ];

        // Setup client with mocks
        $client = $this->setupClientWithMocks($mockResponses);

        // Execute workflow
        $result = $client->trending('US');

        // Verify results
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('trends', $result['data']);
        $this->assertCount(3, $result['data']['trends']);
    }

    /** @test */
    public function it_performs_complete_related_topics_workflow()
    {
        // Create mock data
        $mockData = [
            'status' => 'success',
            'data' => [
                'topics' => [
                    ['title' => 'Topic 1', 'value' => 100],
                    ['title' => 'Topic 2', 'value' => 80],
                    ['title' => 'Topic 3', 'value' => 60],
                ],
                'meta' => [
                    'keyword' => 'php',
                    'period' => '30d'
                ]
            ]
        ];

        // Create mock responses
        $mockResponses = [
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockData)),
        ];

        // Setup client with mocks
        $client = $this->setupClientWithMocks($mockResponses);

        // Execute workflow
        $result = $client->relatedTopics('php');

        // Verify results
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('topics', $result['data']);
        $this->assertCount(3, $result['data']['topics']);
        $this->assertEquals('php', $result['data']['meta']['keyword']);
    }

    /** @test */
    public function it_handles_error_responses_appropriately()
    {
        // Create error data
        $errorData = json_encode([
            'status' => 'error',
            'error' => [
                'code' => 'invalid_request',
                'message' => 'API request failed',
                'details' => [
                    'field' => 'region',
                    'reason' => 'Invalid region code. Must be a valid ISO 3166-1 alpha-2 code.'
                ]
            ]
        ]);

        // Create mock responses
        $mockResponses = [
            new Response(400, ['Content-Type' => 'application/json'], $errorData),
        ];

        // Setup client with mocks
        $client = $this->setupClientWithMocks($mockResponses);

        // Execute workflow and expect exception
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');
        $client->trending('INVALID');
    }

    /** @test */
    public function it_performs_complete_comparison_workflow()
    {
        // Create mock data
        $mockData = [
            'status' => 'success',
            'data' => [
                'comparison' => [
                    ['term' => 'php', 'points' => [10, 20, 30, 40, 50]],
                    ['term' => 'javascript', 'points' => [50, 40, 30, 20, 10]],
                ],
                'meta' => [
                    'period' => '90d',
                    'dates' => ['2023-01-01', '2023-02-01', '2023-03-01', '2023-04-01', '2023-05-01']
                ]
            ]
        ];

        // Create mock responses
        $mockResponses = [
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockData)),
        ];

        // Setup client with mocks
        $client = $this->setupClientWithMocks($mockResponses);

        // Execute workflow
        $result = $client->compare(['php', 'javascript']);

        // Verify results
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('comparison', $result['data']);
        $this->assertCount(2, $result['data']['comparison']);
        $this->assertEquals('php', $result['data']['comparison'][0]['term']);
        $this->assertEquals('javascript', $result['data']['comparison'][1]['term']);
    }
}
