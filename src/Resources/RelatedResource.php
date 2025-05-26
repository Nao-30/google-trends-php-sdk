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
 * RelatedResource - Handles API operations for related topics and queries.
 *
 * This class encapsulates all operations related to finding related topics
 * and related queries based on a search term using the Google Trends API.
 *
 * @package Gtrends\Sdk\Resources
 */
class RelatedResource
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
     * Create a new RelatedResource instance.
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
     * Get related topics for a search term.
     *
     * @param string $topic Search query to find related topics for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Related topics data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getRelatedTopics(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        // Validate topic parameter
        if (empty($topic)) {
            throw new ValidationException(
                'Topic cannot be empty',
                0, null, ['parameter' => 'topic', 'value' => $topic]
            );
        }

        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                0, null, ['parameter' => 'region', 'value' => $region]
            );
        }

        $params = [
            'q' => $topic,
            'time' => $timeframe,
            'cat' => $category,
        ];

        if ($region !== null) {
            $params['geo'] = $region;
        }

        $this->logger->debug('Getting related topics', [
            'topic' => $topic,
            'region' => $region,
            'timeframe' => $timeframe,
            'category' => $category,
        ]);

        $request = $this->requestBuilder->createGetRequest('related-topics', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatRelatedResponse($data, 'topics');
    }

    /**
     * Get related queries for a search term.
     *
     * @param string $topic Search query to find related queries for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Related queries data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getRelatedQueries(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        // Validate topic parameter
        if (empty($topic)) {
            throw new ValidationException(
                'Topic cannot be empty',
                0, null, ['parameter' => 'topic', 'value' => $topic]
            );
        }

        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                0, null, ['parameter' => 'region', 'value' => $region]
            );
        }

        $params = [
            'q' => $topic,
            'time' => $timeframe,
            'cat' => $category,
        ];

        if ($region !== null) {
            $params['geo'] = $region;
        }

        $this->logger->debug('Getting related queries', [
            'topic' => $topic,
            'region' => $region,
            'timeframe' => $timeframe,
            'category' => $category,
        ]);

        $request = $this->requestBuilder->createGetRequest('related-queries', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatRelatedResponse($data, 'queries');
    }

    /**
     * Format the related topics/queries response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @param string $type Type of related data ('topics' or 'queries')
     * @return array Formatted response data
     */
    protected function formatRelatedResponse(array $data, string $type): array
    {
        $responseKey = $type === 'topics' ? 'related_topics' : 'related_queries';

        // If the response already has the expected format, return it as is
        if (isset($data[$responseKey]) && is_array($data[$responseKey])) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            $responseKey => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'query' => $data['query'] ?? null,
            'region' => $data['region'] ?? null,
        ];

        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted[$responseKey] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted[$responseKey] = $data['data'];
        } elseif (isset($data['rising']) && isset($data['top'])) {
            // Some APIs separate top and rising results
            $formatted[$responseKey] = [
                'rising' => $data['rising'],
                'top' => $data['top']
            ];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted[$responseKey] = $data;
        }

        return $formatted;
    }
}
