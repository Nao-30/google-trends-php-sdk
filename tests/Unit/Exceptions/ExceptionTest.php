<?php

namespace Gtrends\Sdk\Tests\Unit\Exceptions;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Exceptions\GtrendsException;
use Gtrends\Sdk\Exceptions\ApiException;
use Gtrends\Sdk\Exceptions\ConfigurationException;
use Gtrends\Sdk\Exceptions\NetworkException;
use Gtrends\Sdk\Exceptions\ValidationException;

class ExceptionTest extends TestCase
{
    /** @test */
    public function base_exception_can_be_instantiated_with_message()
    {
        $exception = new GtrendsException('Test exception message');

        $this->assertInstanceOf(GtrendsException::class, $exception);
        $this->assertEquals('Test exception message', $exception->getMessage());
    }

    /** @test */
    public function base_exception_supports_context_data()
    {
        $context = ['key' => 'value', 'nested' => ['data' => true]];
        $exception = new GtrendsException('Test exception message', 0, null, $context);

        $this->assertEquals($context, $exception->getContext());
    }

    /** @test */
    public function api_exception_stores_http_status_code()
    {
        $exception = new ApiException('API error', 404);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertInstanceOf(GtrendsException::class, $exception);
        $this->assertEquals(404, $exception->getCode());
    }

    /** @test */
    public function api_exception_includes_response_data_in_context()
    {
        $responseData = ['status' => 'error', 'message' => 'Not found'];
        $exception = new ApiException('API error', 404, null, ['response_data' => $responseData]);

        $context = $exception->getContext();
        $this->assertArrayHasKey('response_data', $context);
        $this->assertEquals($responseData, $context['response_data']);
    }

    /** @test */
    public function configuration_exception_can_be_instantiated()
    {
        $exception = new ConfigurationException('Missing required config');

        $this->assertInstanceOf(ConfigurationException::class, $exception);
        $this->assertInstanceOf(GtrendsException::class, $exception);
    }

    /** @test */
    public function network_exception_can_be_instantiated()
    {
        $exception = new NetworkException('Connection timeout');

        $this->assertInstanceOf(NetworkException::class, $exception);
        $this->assertInstanceOf(GtrendsException::class, $exception);
    }

    /** @test */
    public function validation_exception_can_be_instantiated()
    {
        $exception = new ValidationException('Invalid parameter');

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertInstanceOf(GtrendsException::class, $exception);
    }

    /** @test */
    public function validation_exception_stores_field_information()
    {
        $exception = new ValidationException(
            'Invalid parameter',
            0,
            null,
            [
                'field' => 'region',
                'reason' => 'Must be a valid ISO 3166-1 alpha-2 code'
            ]
        );

        $context = $exception->getContext();
        $this->assertArrayHasKey('field', $context);
        $this->assertEquals('region', $context['field']);
        $this->assertArrayHasKey('reason', $context);
    }
}
