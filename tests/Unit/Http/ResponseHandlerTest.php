<?php

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Exceptions\ApiException;
use Gtrends\Sdk\Http\ResponseHandler;
use Gtrends\Sdk\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * @internal
 *
 * @coversNothing
 */
class ResponseHandlerTest extends TestCase
{
    /** @test */
    public function itExtractsJsonDataFromSuccessfulResponse(): void
    {
        $fixtureData = $this->loadFixture('trending_success');
        $response = new Response(200, ['Content-Type' => 'application/json'], $fixtureData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);
        $result = $handler->processResponse($response);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function itThrowsApiExceptionForErrorResponses(): void
    {
        $fixtureData = $this->loadFixture('api_error');
        $response = new Response(400, ['Content-Type' => 'application/json'], $fixtureData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function itThrowsApiExceptionForHttpErrorResponses(): void
    {
        $response = new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function itThrowsApiExceptionForInvalidJson(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{invalid json}');

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function itHandlesEmptyResponses(): void
    {
        $response = new Response(204);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);
        $result = $handler->processResponse($response);

        $this->assertEmpty($result);
    }

    /** @test */
    public function itProcessesResponseWithNestedData(): void
    {
        $jsonData = json_encode([
            'status' => 'success',
            'data' => [
                'nested' => [
                    'deeply' => [
                        'value' => 'test',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $response = new Response(200, ['Content-Type' => 'application/json'], $jsonData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);
        $result = $handler->processResponse($response);

        $this->assertEquals('test', $result['data']['nested']['deeply']['value']);
    }

    /** @test */
    public function itIncludesDetailedErrorInformationInExceptions(): void
    {
        $fixtureData = json_encode([
            'error' => 'Invalid request parameters',
            'code' => 400,
            'details' => ['missing' => 'keyword'],
        ], JSON_THROW_ON_ERROR);
        $response = new Response(400, ['Content-Type' => 'application/json'], $fixtureData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        try {
            $handler->processResponse($response);
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            $this->assertEquals(400, $e->getCode());
            $this->assertStringContainsString('Invalid request parameters', $e->getMessage());

            // Check that response data was included
            $this->assertEquals(400, $e->getStatusCode());
            $responseData = $e->getResponseData();
            $this->assertNotNull($responseData);
            $this->assertArrayHasKey('details', $responseData);
        }
    }
}
