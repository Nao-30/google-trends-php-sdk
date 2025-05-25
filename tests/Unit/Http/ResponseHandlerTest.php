<?php

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Http\ResponseHandler;
use GuzzleHttp\Psr7\Response;
use Gtrends\Sdk\Exceptions\ApiException;

class ResponseHandlerTest extends TestCase
{
    /** @test */
    public function it_extracts_json_data_from_successful_response()
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
    public function it_throws_api_exception_for_error_responses()
    {
        $fixtureData = $this->loadFixture('api_error');
        $response = new Response(400, ['Content-Type' => 'application/json'], $fixtureData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function it_throws_api_exception_for_http_error_responses()
    {
        $response = new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function it_throws_api_exception_for_invalid_json()
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{invalid json}');

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);

        $this->expectException(ApiException::class);
        $handler->processResponse($response);
    }

    /** @test */
    public function it_handles_empty_responses()
    {
        $response = new Response(204);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);
        $result = $handler->processResponse($response);

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_processes_response_with_nested_data()
    {
        $jsonData = json_encode([
            'status' => 'success',
            'data' => [
                'nested' => [
                    'deeply' => [
                        'value' => 'test'
                    ]
                ]
            ]
        ]);

        $response = new Response(200, ['Content-Type' => 'application/json'], $jsonData);

        $config = $this->createConfig();
        $handler = new ResponseHandler($config);
        $result = $handler->processResponse($response);

        $this->assertEquals('test', $result['data']['nested']['deeply']['value']);
    }

    /** @test */
    public function it_includes_detailed_error_information_in_exceptions()
    {
        $fixtureData = json_encode([
            'error' => 'Invalid request parameters',
            'code' => 400,
            'details' => ['missing' => 'keyword']
        ]);
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
