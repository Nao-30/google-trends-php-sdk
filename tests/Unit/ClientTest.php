<?php

namespace Gtrends\Sdk\Tests\Unit;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Exceptions\ValidationException;
use Gtrends\Sdk\Http\HttpClient;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Http\ResponseHandler;
use Gtrends\Sdk\Tests\TestCase;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class ClientTest extends TestCase
{
    private Config $config;

    /**
     * @var MockObject|RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var MockObject|ResponseHandler
     */
    private $responseHandler;

    /**
     * @var HttpClient|MockObject
     */
    private $httpClient;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $this->requestBuilder = $this->createMock(RequestBuilder::class);
        $this->responseHandler = $this->createMock(ResponseHandler::class);
        $this->httpClient = $this->createMock(HttpClient::class);
    }

    /** @test */
    public function itCanBeInstantiatedWithAllDependencies(): void
    {
        $client = new Client(
            $this->config,
            $this->requestBuilder,
            $this->responseHandler
        );
        true;
    }

    /** @test */
    public function itGetsTrendingSearches(): void
    {
        $fixtureData = $this->loadFixture('trending_success');
        $responseArray = json_decode($fixtureData, true, 512, JSON_THROW_ON_ERROR);
        $response = new Response(200, ['Content-Type' => 'application/json'], $fixtureData);

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('trending'),
                $this->callback(function ($params) {
                    return isset($params['region']) && 'US' === $params['region'];
                })
            )
            ->willReturn(new Request('GET', 'http://localhost:3000/api/trending'))
        ;

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->with($this->equalTo($response))
            ->willReturn($responseArray)
        ;

        $client = $this->createClientWithMocks();

        $result = $client->trending('US');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function itValidatesParametersBeforeRequest(): void
    {
        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->willThrowException(new ValidationException('Missing required parameter: region'))
        ;

        $client = $this->createClientWithMocks();

        $this->expectException(ValidationException::class);
        $client->trending();
    }

    /** @test */
    public function itGetsRelatedTopics(): void
    {
        $mockResponse = ['status' => 'success', 'data' => ['topics' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse, JSON_THROW_ON_ERROR));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('related-topics'),
                $this->callback(function ($params) {
                    return isset($params['q']) && 'php' === $params['q'];
                })
            )
            ->willReturn(new Request('GET', 'http://localhost:3000/api/related-topics'))
        ;

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse)
        ;

        $client = $this->createClientWithMocks();

        $result = $client->relatedTopics('php');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function itGetsRelatedQueries(): void
    {
        $mockResponse = ['status' => 'success', 'data' => ['queries' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse, JSON_THROW_ON_ERROR));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('related-queries'),
                $this->callback(function ($params) {
                    return isset($params['q']) && 'php' === $params['q'];
                })
            )
            ->willReturn(new Request('GET', 'http://localhost:3000/api/related-queries'))
        ;

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse)
        ;

        $client = $this->createClientWithMocks();

        $result = $client->relatedQueries('php');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function itGetsComparisonData(): void
    {
        $mockResponse = ['status' => 'success', 'data' => ['comparison' => []]];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse, JSON_THROW_ON_ERROR));

        $this->requestBuilder->expects($this->once())
            ->method('createGetRequest')
            ->with(
                $this->equalTo('comparison'),
                $this->callback(function ($params) {
                    return isset($params['q']) && 'php,javascript' === $params['q'];
                })
            )
            ->willReturn(new Request('GET', 'http://localhost:3000/api/comparison'))
        ;

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $this->responseHandler->expects($this->once())
            ->method('processResponse')
            ->willReturn($mockResponse)
        ;

        $client = $this->createClientWithMocks();

        $result = $client->compare(['php', 'javascript']);

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
    }

    /**
     * Create a client with mock dependencies.
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
}
