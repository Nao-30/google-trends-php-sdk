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
 * GrowthResource - Handles API operations for growth pattern tracking.
 *
 * This class encapsulates operations related to analyzing growth patterns
 * of search terms over time using the Google Trends API.
 *
 * @package Gtrends\Sdk\Resources
 */
class GrowthResource
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
     * Create a new GrowthResource instance.
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
     * Get growth pattern tracking data.
     *
     * @param string $query Search term to analyze growth for
     * @param string $timeframe Time range for data (e.g., 'today 12-m', 'today 5-y')
     * @return array Growth pattern data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getGrowthPatterns(string $query, string $timeframe = 'today 12-m'): array
    {
        // Validate query parameter
        if (empty($query)) {
            throw new ValidationException(
                'Query cannot be empty',
                0, null, ['parameter' => 'query', 'value' => $query]
            );
        }

        // Validate timeframe format
        $validTimeframes = ['today 3-m', 'today 12-m', 'today 5-y', 'all'];
        if (!in_array($timeframe, $validTimeframes)) {
            throw new ValidationException(
                'Timeframe must be one of: ' . implode(', ', $validTimeframes),
                0, null, ['parameter' => 'timeframe', 'value' => $timeframe]
            );
        }

        $params = [
            'q' => $query,
            'time' => $timeframe,
        ];

        $this->logger->debug('Getting growth patterns', [
            'query' => $query,
            'timeframe' => $timeframe,
        ]);

        $request = $this->requestBuilder->createGetRequest('growth', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatGrowthResponse($data);
    }

    /**
     * Format the growth response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatGrowthResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['growth']) && is_array($data['growth'])) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            'growth' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'query' => $data['query'] ?? null,
            'timeframe' => $data['timeframe'] ?? null,
        ];

        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['growth'] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['growth'] = $data['data'];
        } elseif (isset($data['timeline']) && is_array($data['timeline'])) {
            $formatted['growth'] = $data['timeline'];
        } elseif (isset($data['interest_over_time']) && is_array($data['interest_over_time'])) {
            $formatted['growth'] = $data['interest_over_time'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['growth'] = $data;
        }

        // Add growth metrics if available
        if (isset($data['growth_rate'])) {
            $formatted['growth_rate'] = $data['growth_rate'];
        }

        if (isset($data['trend_direction'])) {
            $formatted['trend_direction'] = $data['trend_direction'];
        }

        return $formatted;
    }
}
