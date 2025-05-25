<?php

namespace Gtrends\Sdk\Tests\Unit;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Http\ResponseHandler;
use Gtrends\Sdk\Http\HttpClient;
use GuzzleHttp\Psr7\Response;
use Gtrends\Sdk\Exceptions\ValidationException;
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
     * @var HttpClient|MockObject
     */
    private $httpClient;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $this->requestBuilder = $this->createMock(RequestBuilder::class);
        $this->responseHandler = $this->createMock(ResponseHandler::class);
        $this->httpClient = $this->createMock(HttpClient::class);
    }

    /**
     * Create a client with mock dependencies
     *
     * @return Client
     */
    private function createClientWithMocks(): Client
    {
        $client = new Client(
            $this->config,
            $this->requestBuilder,
            $this->responseHandler
        );

        // Inject HTTP client mock
        $reflection = new \ReflectionClass(Client::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $this->httpClient);

        return $client;
    }

    /** @test */
    public function it_can_be_instantiated_with_all_dependencies()
    {
        $client = new Client(
            $this->config,
            $this->requestBuilder,
            $this->responseHandler
        );
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_gets_trending_searches()
    {
        $fixtureData = $this->loadFixture('trending_success');
        $responseArray = json_decode($fixtureData, true);
        $response = new Response(200, ['Content-Type' => 'application/json'], $fixtureData);

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('trending'),
                $this->callback(function ($params) {
                    return isset($params['region']) && $params['region'] === 'US';
                })
            )
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'http://localhost:3000/api/trending'));

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->with($this->equalTo($response))
            ->willReturn($responseArray);

        $client = $this->createClientWithMocks();

        $result = $client->trending('US');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_validates_parameters_before_request()
    {
        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->willThrowException(new ValidationException('Missing required parameter: region'));

        $client = $this->createClientWithMocks();

        $this->expectException(ValidationException::class);
        $client->trending();
    }

    /** @test */
    public function it_gets_related_topics()
    {
        $mockResponse = ['status' => 'success', 'data' => ['topics' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('related-topics'),
                $this->callback(function ($params) {
                    return isset($params['q']) && $params['q'] === 'php';
                })
            )
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'http://localhost:3000/api/related-topics'));

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse);

        $client = $this->createClientWithMocks();

        $result = $client->relatedTopics('php');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_gets_related_queries()
    {
        $mockResponse = ['status' => 'success', 'data' => ['queries' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('related-queries'),
                $this->callback(function ($params) {
                    return isset($params['q']) && $params['q'] === 'php';
                })
            )
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'http://localhost:3000/api/related-queries'));

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse);

        $client = $this->createClientWithMocks();

        $result = $client->relatedQueries('php');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_gets_comparison_data()
    {
        $mockResponse = ['status' => 'success', 'data' => ['comparison' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('comparison'),
                $this->callback(function ($params) {
                    return isset($params['q']) && $params['q'] === 'php,javascript';
                })
            )
            ->willReturn(new \GuzzleHttp\Psr7\Request('GET', 'http://localhost:3000/api/comparison'));

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse);

        $client = $this->createClientWithMocks();

        $result = $client->compare(['php', 'javascript']);

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }
}
