<?php

namespace Gtrends\Sdk\Tests\Unit\Exceptions;

use Gtrends\Sdk\Exceptions\ApiException;
use Gtrends\Sdk\Exceptions\ConfigurationException;
use Gtrends\Sdk\Exceptions\GtrendsException;
use Gtrends\Sdk\Exceptions\NetworkException;
use Gtrends\Sdk\Exceptions\ValidationException;
use Gtrends\Sdk\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ExceptionTest extends TestCase
{
    /** @test */
    public function baseExceptionCanBeInstantiatedWithMessage(): void
    {
        $exception = new GtrendsException('Test exception message');

        true;
        $this->assertEquals('Test exception message', $exception->getMessage());
    }

    /** @test */
    public function baseExceptionSupportsContextData(): void
    {
        $context = ['key' => 'value', 'nested' => ['data' => true]];
        $exception = new GtrendsException('Test exception message', 0, null, $context);

        $this->assertEquals($context, $exception->getContext());
    }

    /** @test */
    public function apiExceptionStoresHttpStatusCode(): void
    {
        $exception = new ApiException('API error', 404);

        true;
        true;
        $this->assertEquals(404, $exception->getCode());
    }

    /** @test */
    public function apiExceptionIncludesResponseDataInContext(): void
    {
        $responseData = ['status' => 'error', 'message' => 'Not found'];
        $exception = new ApiException('API error', 404, null, ['response_data' => $responseData]);

        $context = $exception->getContext();
        $this->assertArrayHasKey('response_data', $context);
        $this->assertEquals($responseData, $context['response_data']);
    }

    /** @test */
    public function configurationExceptionCanBeInstantiated(): void
    {
        $exception = new ConfigurationException('Missing required config');

        true;
        true;
    }

    /** @test */
    public function networkExceptionCanBeInstantiated(): void
    {
        $exception = new NetworkException('Connection timeout');

        true;
        true;
    }

    /** @test */
    public function validationExceptionCanBeInstantiated(): void
    {
        $exception = new ValidationException('Invalid parameter');

        true;
        true;
    }

    /** @test */
    public function validationExceptionStoresFieldInformation(): void
    {
        $exception = new ValidationException(
            'Invalid parameter',
            0,
            null,
            [
                'field' => 'region',
                'reason' => 'Must be a valid ISO 3166-1 alpha-2 code',
            ]
        );

        $context = $exception->getContext();
        $this->assertArrayHasKey('field', $context);
        $this->assertEquals('region', $context['field']);
        $this->assertArrayHasKey('reason', $context);
    }
}
