<?php

namespace GtrendsSdk\Resources;

use GtrendsSdk\Contracts\ConfigurationInterface;
use GtrendsSdk\Contracts\RequestBuilderInterface;
use GtrendsSdk\Contracts\ResponseHandlerInterface;
use GtrendsSdk\Exceptions\ValidationException;
use GtrendsSdk\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * TrendingResource - Handles API operations for real-time trending searches.
 *
 * This class encapsulates all operations related to the trending searches
 * endpoint of the Google Trends API.
 *
 * @package GtrendsSdk\Resources
 */
class TrendingResource
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
     * Create a new TrendingResource instance.
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
     * Get trending searches from Google Trends.
     *
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param int $limit Maximum number of results to return (1-100)
     * @param bool $includeNews Whether to include related news articles
     * @return array Trending search data
     * 
     * @throws ValidationException When the parameters are invalid
     * @throws \GtrendsSdk\Exceptions\ApiException When the API returns an error
     * @throws \GtrendsSdk\Exceptions\NetworkException When a network error occurs
     */
    public function getTrending(?string $region = null, int $limit = 10, bool $includeNews = false): array
    {
        // Validate limit parameter
        if ($limit < 1 || $limit > 100) {
            throw new ValidationException(
                'Limit must be between 1 and 100',
                ['parameter' => 'limit', 'value' => $limit]
            );
        }
        
        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                ['parameter' => 'region', 'value' => $region]
            );
        }
        
        $params = [
            'limit' => $limit,
            'include_news' => $includeNews,
        ];
        
        if ($region !== null) {
            $params['region'] = $region;
        }
        
        $this->logger->debug('Getting trending searches', [
            'region' => $region,
            'limit' => $limit,
            'include_news' => $includeNews
        ]);
        
        $request = $this->requestBuilder->createGetRequest('trending', $params);
        $response = $this->httpClient->sendRequest($request);
        
        $data = $this->responseHandler->processResponse($response);
        
        return $this->formatTrendingResponse($data);
    }
    
    /**
     * Format the trending response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatTrendingResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['trends']) && is_array($data['trends'])) {
            return $data;
        }
        
        // Construct a standardized response format
        $formatted = [
            'trends' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'region' => $data['region'] ?? null,
        ];
        
        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['trends'] = $data['items'];
        } elseif (isset($data['trending_searches']) && is_array($data['trending_searches'])) {
            $formatted['trends'] = $data['trending_searches'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['trends'] = $data['data'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['trends'] = $data;
        }
        
        return $formatted;
    }
} 