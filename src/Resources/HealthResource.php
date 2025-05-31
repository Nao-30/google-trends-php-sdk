<?php

namespace Gtrends\Sdk\Resources;

use Gtrends\Sdk\Contracts\ConfigurationInterface;
use Gtrends\Sdk\Contracts\RequestBuilderInterface;
use Gtrends\Sdk\Contracts\ResponseHandlerInterface;
use Gtrends\Sdk\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * HealthResource - Handles API operations for service health monitoring.
 *
 * This class encapsulates operations related to checking the health status
 * of the Google Trends API service.
 *
 * @package Gtrends\Sdk\Resources
 */
class HealthResource
{
    /**
     * @var HttpClient The HTTP client
     */
    protected HttpClient $httpClient;

    /**
     * @var RequestBuilderInterface The request builder
     */
    protected RequestBuilderInterface $requestBuilder;

    /**
     * @var ResponseHandlerInterface The response handler
     */
    protected ResponseHandlerInterface $responseHandler;

    /**
     * @var LoggerInterface The logger instance
     */
    protected LoggerInterface $logger;

    /**
     * @var ConfigurationInterface The configuration
     */
    protected ConfigurationInterface $config;

    /**
     * Create a new HealthResource instance.
     *
     * @param HttpClient $httpClient The HTTP client
     * @param RequestBuilderInterface $requestBuilder The request builder
     * @param ResponseHandlerInterface $responseHandler The response handler
     * @param ConfigurationInterface $config The configuration
     * @param LoggerInterface|null $logger The logger instance
     */
    public function __construct(
        HttpClient $httpClient,
        RequestBuilderInterface $requestBuilder,
        ResponseHandlerInterface $responseHandler,
        ConfigurationInterface $config,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestBuilder = $requestBuilder;
        $this->responseHandler = $responseHandler;
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Check the health of the Google Trends API.
     *
     * @return array Health check data
     *
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function checkHealth(): array
    {
        $this->logger->debug('Checking API health');

        $request = $this->requestBuilder->createGetRequest('health');
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatHealthResponse($data);
    }

    /**
     * Format the health response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatHealthResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['status']) && (isset($data['message']) || isset($data['details']))) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            'status' => 'unknown',
            'message' => '',
            'timestamp' => $data['timestamp'] ?? time(),
            'details' => [],
        ];

        // Extract status information
        if (isset($data['status'])) {
            $formatted['status'] = $data['status'];
        } elseif (isset($data['health_status'])) {
            $formatted['status'] = $data['health_status'];
        } elseif (isset($data['is_healthy']) && is_bool($data['is_healthy'])) {
            $formatted['status'] = $data['is_healthy'] ? 'healthy' : 'unhealthy';
        }

        // Extract message information
        if (isset($data['message'])) {
            $formatted['message'] = $data['message'];
        } elseif (isset($data['status_message'])) {
            $formatted['message'] = $data['status_message'];
        }

        // Extract detailed information
        if (isset($data['details']) && is_array($data['details'])) {
            $formatted['details'] = $data['details'];
        } elseif (isset($data['metrics']) && is_array($data['metrics'])) {
            $formatted['details'] = $data['metrics'];
        } elseif (isset($data['components']) && is_array($data['components'])) {
            $formatted['details'] = $data['components'];
        }

        // Add additional information if available
        if (isset($data['version'])) {
            $formatted['version'] = $data['version'];
        }

        return $formatted;
    }
}
