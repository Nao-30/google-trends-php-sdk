<?php

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Http\ResponseHandler;
use GuzzleHttp\Psr7\Response;
use Gtrends\Exceptions\ApiException;

class ResponseHandlerTest extends TestCase
{
    /** @test */
    public function it_extracts_json_data_from_successful_response()
    {
        $fixtureData = $this->loadFixture('trending_success');
        $response = new Response(200, ['Content-Type' => 'application/json'], $fixtureData);
        
        $handler = new ResponseHandler();
        $result = $handler->handle($response);
        
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
        
        $handler = new ResponseHandler();
        
        $this->expectException(ApiException::class);
        $handler->handle($response);
    }
    
    /** @test */
    public function it_throws_api_exception_for_http_error_responses()
    {
        $response = new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');
        
        $handler = new ResponseHandler();
        
        $this->expectException(ApiException::class);
        $handler->handle($response);
    }
    
    /** @test */
    public function it_throws_api_exception_for_invalid_json()
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{invalid json}');
        
        $handler = new ResponseHandler();
        
        $this->expectException(ApiException::class);
        $handler->handle($response);
    }
    
    /** @test */
    public function it_handles_empty_responses()
    {
        $response = new Response(204);
        
        $handler = new ResponseHandler();
        $result = $handler->handle($response);
        
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
        
        $handler = new ResponseHandler();
        $result = $handler->handle($response);
        
        $this->assertEquals('test', $result['data']['nested']['deeply']['value']);
    }
    
    /** @test */
    public function it_includes_detailed_error_information_in_exceptions()
    {
        $fixtureData = $this->loadFixture('api_error');
        $response = new Response(400, ['Content-Type' => 'application/json'], $fixtureData);
        
        $handler = new ResponseHandler();
        
        try {
            $handler->handle($response);
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            $this->assertEquals(400, $e->getCode());
            $this->assertStringContainsString('Invalid request parameters', $e->getMessage());
            
            // Check that context information was included
            $context = $e->getContext();
            $this->assertIsArray($context);
            $this->assertArrayHasKey('response_body', $context);
            $this->assertArrayHasKey('http_status', $context);
        }
    }
} 