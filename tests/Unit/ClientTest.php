<?php

namespace Gtrends\Sdk\Tests\Unit;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Http\ResponseHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Gtrends\Exceptions\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;

class ClientTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestBuilder|MockObject
     */
    private $requestBuilder;

    /**
     * @var ResponseHandler|MockObject
     */
    private $responseHandler;

    /**
     * @var GuzzleClient|MockObject
     */
    private $httpClient;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createConfig();
        $this->requestBuilder = $this->createMock(RequestBuilder::class);
        $this->responseHandler = $this->createMock(ResponseHandler::class);
        $this->httpClient = $this->createMock(GuzzleClient::class);
    }

    /** @test */
    public function it_can_be_instantiated_with_config_only()
    {
        $client = new Client($this->config);
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_can_be_instantiated_with_all_dependencies()
    {
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_gets_trending_searches()
    {
        $fixtureData = $this->loadFixture('trending_success');
        $response = new Response(200, ['Content-Type' => 'application/json'], $fixtureData);
        
        $this->requestBuilder->expects($this->once())
            ->method('buildRequest')
            ->with('GET', '/trending', ['region' => 'US'])
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/api/v1/trending'));
        
        $this->httpClient->expects($this->once())
            ->method('send')
            ->willReturn($response);
        
        $this->responseHandler->expects($this->once())
            ->method('handle')
            ->with($response)
            ->willReturn(json_decode($fixtureData, true));
        
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        
        $result = $client->getTrending(['region' => 'US']);
        
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_validates_parameters_before_request()
    {
        $this->requestBuilder->expects($this->once())
            ->method('buildRequest')
            ->willThrowException(new ValidationException('Missing required parameter: region'));
        
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        
        $this->expectException(ValidationException::class);
        $client->getTrending([]);
    }

    /** @test */
    public function it_gets_related_topics()
    {
        $mockResponse = ['status' => 'success', 'data' => ['topics' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));
        
        $this->requestBuilder->expects($this->once())
            ->method('buildRequest')
            ->with('GET', '/related/topics', ['keyword' => 'php'])
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/api/v1/related/topics'));
        
        $this->httpClient->expects($this->once())
            ->method('send')
            ->willReturn($response);
        
        $this->responseHandler->expects($this->once())
            ->method('handle')
            ->willReturn($mockResponse);
        
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        
        $result = $client->getRelatedTopics(['keyword' => 'php']);
        
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_gets_related_queries()
    {
        $mockResponse = ['status' => 'success', 'data' => ['queries' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));
        
        $this->requestBuilder->expects($this->once())
            ->method('buildRequest')
            ->with('GET', '/related/queries', ['keyword' => 'php'])
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/api/v1/related/queries'));
        
        $this->httpClient->expects($this->once())
            ->method('send')
            ->willReturn($response);
        
        $this->responseHandler->expects($this->once())
            ->method('handle')
            ->willReturn($mockResponse);
        
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        
        $result = $client->getRelatedQueries(['keyword' => 'php']);
        
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_gets_comparison_data()
    {
        $mockResponse = ['status' => 'success', 'data' => ['comparison' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));
        
        $this->requestBuilder->expects($this->once())
            ->method('buildRequest')
            ->with('POST', '/comparison', [], ['topics' => ['php', 'javascript']])
            ->willReturn(new \GuzzleHttp\Psr7\Request('POST', 'https://example.com/api/v1/comparison'));
        
        $this->httpClient->expects($this->once())
            ->method('send')
            ->willReturn($response);
        
        $this->responseHandler->expects($this->once())
            ->method('handle')
            ->willReturn($mockResponse);
        
        $client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestBuilder,
            $this->responseHandler
        );
        
        $result = $client->getComparison(['topics' => ['php', 'javascript']]);
        
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }
} 