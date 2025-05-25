<?php

namespace Gtrends\Sdk\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * Interface RequestBuilderInterface
 *
 * Defines the contract for building HTTP requests in the Google Trends PHP SDK.
 * This interface outlines methods for constructing and configuring HTTP requests.
 *
 * @package Gtrends\Sdk\Contracts
 */
interface RequestBuilderInterface
{
    /**
     * Create a new GET request.
     *
     * @param string $endpoint API endpoint path
     * @param array $queryParams Query parameters
     * @param array $headers HTTP headers
     * @return RequestInterface PSR-7 Request object
     * 
     * @throws \Gtrends\Sdk\Exceptions\ValidationException When the parameters are invalid
     */
    public function createGetRequest(string $endpoint, array $queryParams = [], array $headers = []): RequestInterface;
    
    /**
     * Create a new POST request.
     *
     * @param string $endpoint API endpoint path
     * @param array $data Request body data
     * @param array $headers HTTP headers
     * @return RequestInterface PSR-7 Request object
     * 
     * @throws \Gtrends\Sdk\Exceptions\ValidationException When the parameters are invalid
     */
    public function createPostRequest(string $endpoint, array $data = [], array $headers = []): RequestInterface;
    
    /**
     * Build the full URL for an API endpoint.
     *
     * @param string $endpoint API endpoint path
     * @return string Full URL
     */
    public function buildUrl(string $endpoint): string;
    
    /**
     * Add query parameters to a URL.
     *
     * @param string $url Base URL
     * @param array $params Query parameters
     * @return string URL with query parameters
     */
    public function addQueryParams(string $url, array $params): string;
    
    /**
     * Merge default headers with custom headers.
     *
     * @param array $headers Custom headers
     * @return array Merged headers
     */
    public function mergeHeaders(array $headers): array;
    
    /**
     * Validate request parameters.
     *
     * @param array $params Parameters to validate
     * @param array $rules Validation rules
     * @return bool True if valid, false otherwise
     * 
     * @throws \Gtrends\Sdk\Exceptions\ValidationException When the parameters are invalid
     */
    public function validateParams(array $params, array $rules): bool;
    
    /**
     * Get the configuration used by the request builder.
     *
     * @return ConfigurationInterface
     */
    public function getConfig(): ConfigurationInterface;
    
    /**
     * Set the configuration for the request builder.
     *
     * @param ConfigurationInterface $config
     * @return self
     */
    public function setConfig(ConfigurationInterface $config): self;
} 