<?php

namespace Gtrends\Sdk\Tests\Integration;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Gtrends\Exceptions\ApiException;

class ApiWorkflowTest extends TestCase
{
    /** @test */
    public function it_performs_complete_trending_search_workflow()
    {
        // Create fixture data
        $trendingData = $this->loadFixture('trending_success');
        
        // Create mock responses
        $mockResponses = [
            new Response(200, ['Content-Type' => 'application/json'], $trendingData),
        ];
        
        // Create mock HTTP client
        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);
        
        // Create client
        $config = $this->createConfig();
        $client = new Client($config, $guzzleClient);
        
        // Execute workflow
        $result = $client->getTrending(['region' => 'US']);
        
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
        
        // Create mock HTTP client
        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);
        
        // Create client
        $config = $this->createConfig();
        $client = new Client($config, $guzzleClient);
        
        // Execute workflow
        $result = $client->getRelatedTopics(['keyword' => 'php', 'period' => '30d']);
        
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
        $errorData = $this->loadFixture('api_error');
        
        // Create mock responses
        $mockResponses = [
            new Response(400, ['Content-Type' => 'application/json'], $errorData),
        ];
        
        // Create mock HTTP client
        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);
        
        // Create client
        $config = $this->createConfig();
        $client = new Client($config, $guzzleClient);
        
        // Execute workflow and expect exception
        $this->expectException(ApiException::class);
        $client->getTrending(['region' => 'INVALID']);
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
        
        // Create mock HTTP client
        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);
        
        // Create client
        $config = $this->createConfig();
        $client = new Client($config, $guzzleClient);
        
        // Execute workflow
        $result = $client->getComparison(['topics' => ['php', 'javascript'], 'period' => '90d']);
        
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