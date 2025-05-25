<?php

namespace Gtrends\Sdk\Contracts;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseHandlerInterface
 *
 * Defines the contract for handling API responses in the Google Trends PHP SDK.
 * This interface outlines methods for processing, validating, and transforming API responses.
 *
 * @package Gtrends\Sdk\Contracts
 */
interface ResponseHandlerInterface
{
    /**
     * Process a raw HTTP response into a structured array.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @return array Processed response data
     * 
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     */
    public function processResponse(ResponseInterface $response): array;
    
    /**
     * Extract JSON data from a response.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @return array Decoded JSON data
     * 
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the response cannot be decoded
     */
    public function extractJson(ResponseInterface $response): array;
    
    /**
     * Check if a response is successful.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @return bool True if the response is successful, false otherwise
     */
    public function isSuccessful(ResponseInterface $response): bool;
    
    /**
     * Get error details from an error response.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @return array Error details
     */
    public function getErrorDetails(ResponseInterface $response): array;
    
    /**
     * Transform raw API response data into a consistent format.
     *
     * @param array $data Raw response data
     * @param string $endpoint API endpoint that was called
     * @return array Transformed data
     */
    public function transformResponseData(array $data, string $endpoint): array;
    
    /**
     * Validate that the response contains all required fields.
     *
     * @param array $data Response data
     * @param array $requiredFields List of required fields
     * @return bool True if valid, false otherwise
     * 
     * @throws \Gtrends\Sdk\Exceptions\ApiException When required fields are missing
     */
    public function validateResponseData(array $data, array $requiredFields): bool;
    
    /**
     * Get the debug information from a response.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @return array Debug information
     */
    public function getDebugInfo(ResponseInterface $response): array;
    
    /**
     * Get the configuration used by the response handler.
     *
     * @return ConfigurationInterface
     */
    public function getConfig(): ConfigurationInterface;
    
    /**
     * Set the configuration for the response handler.
     *
     * @param ConfigurationInterface $config
     * @return self
     */
    public function setConfig(ConfigurationInterface $config): self;
} 