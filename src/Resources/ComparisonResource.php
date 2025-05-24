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
 * ComparisonResource - Handles API operations for comparing multiple topics.
 *
 * This class encapsulates operations related to comparing multiple search terms
 * to see their relative interest over time using the Google Trends API.
 *
 * @package GtrendsSdk\Resources
 */
class ComparisonResource
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
     * Create a new ComparisonResource instance.
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
     * Compare multiple search terms to see relative interest.
     *
     * @param array $topics List of topics to compare (1-5 items)
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Comparison data
     * 
     * @throws ValidationException When the parameters are invalid
     * @throws \GtrendsSdk\Exceptions\ApiException When the API returns an error
     * @throws \GtrendsSdk\Exceptions\NetworkException When a network error occurs
     */
    public function compare(array $topics, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        // Validate topics parameter
        if (empty($topics)) {
            throw new ValidationException(
                'At least one topic is required for comparison',
                ['parameter' => 'topics', 'value' => $topics]
            );
        }
        
        if (count($topics) > 5) {
            throw new ValidationException(
                'Maximum of 5 topics can be compared',
                ['parameter' => 'topics', 'value' => $topics]
            );
        }
        
        // Check for empty topic strings
        foreach ($topics as $index => $topic) {
            if (empty($topic)) {
                throw new ValidationException(
                    'Topic cannot be empty',
                    ['parameter' => 'topics', 'index' => $index, 'value' => $topic]
                );
            }
        }
        
        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                ['parameter' => 'region', 'value' => $region]
            );
        }
        
        $params = [
            'q' => implode(',', $topics),
            'time' => $timeframe,
            'cat' => $category,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $this->logger->debug('Comparing topics', [
            'topics' => $topics,
            'region' => $region,
            'timeframe' => $timeframe,
            'category' => $category,
        ]);
        
        $request = $this->requestBuilder->createGetRequest('comparison', $params);
        $response = $this->httpClient->sendRequest($request);
        
        $data = $this->responseHandler->processResponse($response);
        
        return $this->formatComparisonResponse($data, $topics);
    }
    
    /**
     * Format the comparison response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @param array $topics Original list of topics being compared
     * @return array Formatted response data
     */
    protected function formatComparisonResponse(array $data, array $topics): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['comparison']) && is_array($data['comparison'])) {
            return $data;
        }
        
        // Construct a standardized response format
        $formatted = [
            'comparison' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'topics' => $topics,
            'region' => $data['region'] ?? null,
            'timeframe' => $data['timeframe'] ?? null,
        ];
        
        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['comparison'] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['comparison'] = $data['data'];
        } elseif (isset($data['interest_over_time']) && is_array($data['interest_over_time'])) {
            $formatted['comparison'] = $data['interest_over_time'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['comparison'] = $data;
        }
        
        return $formatted;
    }
} 