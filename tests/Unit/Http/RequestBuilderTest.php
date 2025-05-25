<?php

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Exceptions\ValidationException;

class RequestBuilderTest extends TestCase
{
    /** @test */
    public function it_builds_get_request_with_query_params()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->createGetRequest(
            'trending',
            ['region' => 'US', 'date' => '2023-05-30']
        );
        
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/api/trending', $request->getUri()->getPath());
        $this->assertEquals('region=US&date=2023-05-30', $request->getUri()->getQuery());
    }
    
    /** @test */
    public function it_builds_post_request_with_json_body()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $bodyData = ['topics' => ['php', 'javascript'], 'period' => '30d'];
        
        $request = $builder->createPostRequest(
            'comparison',
            $bodyData
        );
        
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/comparison', $request->getUri()->getPath());
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals($bodyData, $body);
    }
    
    /** @test */
    public function it_adds_default_headers()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->createGetRequest('endpoint');
        
        $this->assertTrue($request->hasHeader('User-Agent'));
        $this->assertTrue($request->hasHeader('Accept'));
    }
    
    /** @test */
    public function it_adds_custom_headers()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->createGetRequest(
            'endpoint',
            [],
            ['X-Custom-Header' => 'CustomValue']
        );
        
        $this->assertEquals('CustomValue', $request->getHeaderLine('X-Custom-Header'));
    }
    
    /** @test */
    public function it_validates_required_parameters()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $this->expectException(ValidationException::class);
        
        // Validating parameters directly
        $builder->validateParams(
            [],
            ['region' => 'required']
        );
    }
    
    /** @test */
    public function it_constructs_full_uri_from_base_and_path()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->createGetRequest('trending');
        
        $uri = $request->getUri();
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('/api/trending', $uri->getPath());
    }
    
    /** @test */
    public function it_handles_url_encoding_properly()
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->createGetRequest(
            'search',
            ['q' => 'test query with spaces', 'filter' => 'type=news']
        );
        
        $this->assertStringContainsString('q=test+query+with+spaces', $request->getUri()->getQuery());
        $this->assertStringContainsString('filter=type%3Dnews', $request->getUri()->getQuery());
    }
} 