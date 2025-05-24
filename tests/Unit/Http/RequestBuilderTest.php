<?php

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Exceptions\ValidationException;

class RequestBuilderTest extends TestCase
{
    /** @test */
    public function it_builds_get_request_with_query_params()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $request = $builder->buildRequest(
            'GET',
            '/trending',
            ['region' => 'US', 'date' => '2023-05-30']
        );
        
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/trending', $request->getUri()->getPath());
        $this->assertEquals('region=US&date=2023-05-30', $request->getUri()->getQuery());
    }
    
    /** @test */
    public function it_builds_post_request_with_json_body()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $bodyData = ['topics' => ['php', 'javascript'], 'period' => '30d'];
        
        $request = $builder->buildRequest(
            'POST',
            '/comparison',
            [],
            $bodyData
        );
        
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/comparison', $request->getUri()->getPath());
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        
        $body = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals($bodyData, $body);
    }
    
    /** @test */
    public function it_adds_default_headers()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $request = $builder->buildRequest('GET', '/endpoint');
        
        $this->assertTrue($request->hasHeader('User-Agent'));
        $this->assertTrue($request->hasHeader('Accept'));
    }
    
    /** @test */
    public function it_adds_custom_headers()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $request = $builder->buildRequest(
            'GET',
            '/endpoint',
            [],
            null,
            ['X-Custom-Header' => 'CustomValue']
        );
        
        $this->assertEquals('CustomValue', $request->getHeaderLine('X-Custom-Header'));
    }
    
    /** @test */
    public function it_validates_required_parameters()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $this->expectException(ValidationException::class);
        
        // Assuming the builder requires 'region' parameter for this endpoint
        $builder->buildRequest(
            'GET',
            '/trending',
            [],
            null,
            [],
            ['region']
        );
    }
    
    /** @test */
    public function it_constructs_full_uri_from_base_and_path()
    {
        $config = $this->createConfig(['api_base_uri' => 'https://api.example.com/v1']);
        $builder = new RequestBuilder($config);
        
        $request = $builder->buildRequest('GET', '/trending');
        
        $uri = $request->getUri();
        $this->assertEquals('https://api.example.com', $uri->getHost());
        $this->assertEquals('/v1/trending', $uri->getPath());
    }
    
    /** @test */
    public function it_handles_url_encoding_properly()
    {
        $config = $this->createConfig();
        $builder = new RequestBuilder($config);
        
        $request = $builder->buildRequest(
            'GET',
            '/search',
            ['q' => 'test query with spaces', 'filter' => 'type=news']
        );
        
        $this->assertStringContainsString('q=test+query+with+spaces', $request->getUri()->getQuery());
        $this->assertStringContainsString('filter=type%3Dnews', $request->getUri()->getQuery());
    }
} 