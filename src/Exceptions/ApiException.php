<?php

namespace Gtrends\Sdk\Exceptions;

/**
 * Exception thrown for API-related errors.
 *
 * This exception is used when there are errors related to the API,
 * including HTTP errors and API-specific error responses.
 */
class ApiException extends GtrendsException
{
    /**
     * HTTP status code from the API response.
     *
     * @var int|null
     */
    protected ?int $statusCode = null;

    /**
     * API error code if available.
     *
     * @var string|null
     */
    protected ?string $apiErrorCode = null;

    /**
     * Original response data received from the API.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $responseData = null;

    /**
     * Create a new API exception instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     * @param array<string, mixed> $context Additional context information
     * @param int|null $statusCode HTTP status code
     * @param string|null $apiErrorCode API-specific error code
     * @param array<string, mixed>|null $responseData Original response data
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = [],
        ?int $statusCode = null,
        ?string $apiErrorCode = null,
        ?array $responseData = null
    ) {
        parent::__construct($message, $code, $previous, $context);

        $this->statusCode = $statusCode;
        $this->apiErrorCode = $apiErrorCode;
        $this->responseData = $responseData;

        // Add API information to context
        if ($statusCode !== null) {
            $this->addContext('status_code', $statusCode);
        }

        if ($apiErrorCode !== null) {
            $this->addContext('api_error_code', $apiErrorCode);
        }

        if ($responseData !== null) {
            $this->addContext('response_data', $responseData);
        }
    }

    /**
     * Get the HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get the API error code.
     *
     * @return string|null
     */
    public function getApiErrorCode(): ?string
    {
        return $this->apiErrorCode;
    }

    /**
     * Get the original response data.
     *
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
