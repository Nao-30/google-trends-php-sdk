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
 * SuggestionsResource - Handles API operations for content creation suggestions.
 *
 * This class encapsulates operations related to getting content suggestions
 * based on search trends using the Google Trends API.
 *
 * @package Gtrends\Sdk\Resources
 */
class SuggestionsResource
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
     * Create a new SuggestionsResource instance.
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
     * Get content creation suggestions.
     *
     * @param string $query Search term to get suggestions for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $contentType Type of content suggestions to return
     * @return array Suggestions data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getSuggestions(string $query, ?string $region = null, string $contentType = 'all'): array
    {
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

        // Validate content type
        $validContentTypes = ['all', 'article', 'video', 'image', 'social'];
        if (!in_array($contentType, $validContentTypes)) {
            throw new ValidationException(
                'Content type must be one of: ' . implode(', ', $validContentTypes),
                0, null, ['parameter' => 'contentType', 'value' => $contentType]
            );
        }

        $params = [
            'q' => $query,
            'type' => $contentType,
        ];

        if ($region !== null) {
            $params['geo'] = $region;
        }

        $this->logger->debug('Getting content suggestions', [
            'query' => $query,
            'region' => $region,
            'contentType' => $contentType,
        ]);

        $request = $this->requestBuilder->createGetRequest('suggestions', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatSuggestionsResponse($data);
    }

    /**
     * Format the suggestions response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatSuggestionsResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['suggestions']) && is_array($data['suggestions'])) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            'suggestions' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'query' => $data['query'] ?? null,
            'region' => $data['region'] ?? null,
            'content_type' => $data['content_type'] ?? 'all',
        ];

        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['suggestions'] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['suggestions'] = $data['data'];
        } elseif (isset($data['content_ideas']) && is_array($data['content_ideas'])) {
            $formatted['suggestions'] = $data['content_ideas'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['suggestions'] = $data;
        }

        return $formatted;
    }
}
