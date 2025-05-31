<?php

/** @noinspection SpellCheckingInspection */

namespace Gtrends\Sdk\Tests\Unit\Http;

use Gtrends\Sdk\Exceptions\ValidationException;
use Gtrends\Sdk\Http\RequestBuilder;
use Gtrends\Sdk\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class RequestBuilderTest extends TestCase
{
    /** @test
     * @throws ValidationException
     */
    public function itBuildsGetRequestWithQueryParams(): void
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

    /** @test
     * @throws ValidationException
     */
    public function itBuildsPostRequestWithJsonBody(): void
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

        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($bodyData, $body);
    }

    /** @test
     * @throws ValidationException
     */
    public function itAddsDefaultHeaders(): void
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);

        $request = $builder->createGetRequest('endpoint');

        $this->assertTrue($request->hasHeader('User-Agent'));
        $this->assertTrue($request->hasHeader('Accept'));
    }

    /** @test
     * @throws ValidationException
     */
    public function itAddsCustomHeaders(): void
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
    public function itValidatesRequiredParameters(): void
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

    /** @test
     * @throws ValidationException
     */
    public function itConstructsFullUriFromBaseAndPath(): void
    {
        $config = $this->createConfig(['base_uri' => 'http://localhost:3000/api/']);
        $builder = new RequestBuilder($config);

        $request = $builder->createGetRequest('trending');

        $uri = $request->getUri();
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('/api/trending', $uri->getPath());
    }

    /** @test
     * @throws ValidationException
     */
    public function itHandlesUrlEncodingProperly(): void
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
