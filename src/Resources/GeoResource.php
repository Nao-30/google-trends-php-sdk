<?php

namespace Gtrends\Sdk\Resources;

use Gtrends\Sdk\Contracts\ConfigurationInterface;
use Gtrends\Sdk\Contracts\RequestBuilderInterface;
use Gtrends\Sdk\Contracts\ResponseHandlerInterface;
use Gtrends\Sdk\Exceptions\ValidationException;
use Gtrends\Sdk\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * GeoResource - Handles API operations for geographic interest analysis.
 *
 * This class encapsulates operations related to analyzing geographic interest
 * patterns for search terms using the Google Trends API.
 *
 * @package Gtrends\Sdk\Resources
 */
class GeoResource
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
     * Create a new GeoResource instance.
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
     * Get geographic interest analysis.
     *
     * @param string $query Search term to analyze geographic interest
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $resolution Geographic resolution level (COUNTRY, REGION, CITY)
     * @param string $timeframe Time range for data (e.g., 'today 12-m')
     * @param string $category Category ID to filter results
     * @param int $count Number of regions to display
     * @return array Geographic interest data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getGeoInterest(
        string $query,
        ?string $region = null,
        string $resolution = 'COUNTRY',
        string $timeframe = 'today 12-m',
        string $category = '0',
        int $count = 20
    ): array {
        // Validate query parameter
        if (empty($query)) {
            throw new ValidationException(
                'Query cannot be empty',
                0, null, ['parameter' => 'query', 'value' => $query]
            );
        }

        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                0, null, ['parameter' => 'region', 'value' => $region]
            );
        }

        // Validate resolution parameter
        $validResolutions = ['COUNTRY', 'REGION', 'CITY', 'DMA'];
        if (!in_array(strtoupper($resolution), $validResolutions)) {
            throw new ValidationException(
                'Resolution must be one of: ' . implode(', ', $validResolutions),
                0, null, ['parameter' => 'resolution', 'value' => $resolution]
            );
        }

        // Validate count parameter
        if ($count < 1 || $count > 100) {
            throw new ValidationException(
                'Count must be between 1 and 100',
                0, null, ['parameter' => 'count', 'value' => $count]
            );
        }

        $params = [
            'q' => $query,
            'resolution' => strtoupper($resolution),
            'time' => $timeframe,
            'cat' => $category,
            'count' => $count,
        ];

        if ($region !== null) {
            $params['geo'] = $region;
        }

        $this->logger->debug('Getting geographic interest', [
            'query' => $query,
            'region' => $region,
            'resolution' => $resolution,
            'timeframe' => $timeframe,
            'category' => $category,
            'count' => $count,
        ]);

        $request = $this->requestBuilder->createGetRequest('geo', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatGeoResponse($data);
    }

    /**
     * Format the geographic interest response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatGeoResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['geo_interest']) && is_array($data['geo_interest'])) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            'geo_interest' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'query' => $data['query'] ?? null,
            'region' => $data['region'] ?? null,
            'resolution' => $data['resolution'] ?? null,
            'timeframe' => $data['timeframe'] ?? null,
        ];

        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['geo_interest'] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['geo_interest'] = $data['data'];
        } elseif (isset($data['interest_by_region']) && is_array($data['interest_by_region'])) {
            $formatted['geo_interest'] = $data['interest_by_region'];
        } elseif (isset($data['regions']) && is_array($data['regions'])) {
            $formatted['geo_interest'] = $data['regions'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['geo_interest'] = $data;
        }

        return $formatted;
    }
}
