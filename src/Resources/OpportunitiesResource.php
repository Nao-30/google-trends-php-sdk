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
 * OpportunitiesResource - Handles API operations for writing opportunity identification.
 *
 * This class encapsulates operations related to finding writing opportunities
 * based on trending searches and user interests using the Google Trends API.
 *
 * @package Gtrends\Sdk\Resources
 */
class OpportunitiesResource
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
     * Create a new OpportunitiesResource instance.
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
     * Get writing opportunity identification.
     *
     * @param string $niche Niche to find opportunities in
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param int $count Number of opportunities to return
     * @return array Opportunities data
     *
     * @throws ValidationException When the parameters are invalid
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function getOpportunities(string $niche, ?string $region = null, int $count = 10): array
    {
        // Validate niche parameter
        if (empty($niche)) {
            throw new ValidationException(
                'Niche cannot be empty',
                0,
                null,
                ['parameter' => 'niche', 'value' => $niche]
            );
        }

        // Validate region parameter if provided
        if ($region !== null && !preg_match('/^[A-Z]{2}$/', $region)) {
            throw new ValidationException(
                'Region must be a valid two-letter country code',
                0,
                null,
                ['parameter' => 'region', 'value' => $region]
            );
        }

        // Validate count parameter
        if ($count < 1 || $count > 50) {
            throw new ValidationException(
                'Count must be between 1 and 50',
                0,
                null,
                ['parameter' => 'count', 'value' => $count]
            );
        }

        $params = [
            'niche' => $niche,
            'count' => $count,
        ];

        if ($region !== null) {
            $params['geo'] = $region;
        }

        $this->logger->debug('Getting writing opportunities', [
            'niche' => $niche,
            'region' => $region,
            'count' => $count,
        ]);

        $request = $this->requestBuilder->createGetRequest('opportunities', $params);
        $response = $this->httpClient->sendRequest($request);

        $data = $this->responseHandler->processResponse($response);

        return $this->formatOpportunitiesResponse($data);
    }

    /**
     * Format the opportunities response data into a consistent structure.
     *
     * @param array $data Raw response data
     * @return array Formatted response data
     */
    protected function formatOpportunitiesResponse(array $data): array
    {
        // If the response already has the expected format, return it as is
        if (isset($data['opportunities']) && is_array($data['opportunities'])) {
            return $data;
        }

        // Construct a standardized response format
        $formatted = [
            'opportunities' => [],
            'timestamp' => $data['timestamp'] ?? time(),
            'niche' => $data['niche'] ?? null,
            'region' => $data['region'] ?? null,
            'count' => $data['count'] ?? null,
        ];

        // Handle different possible response structures
        if (isset($data['items']) && is_array($data['items'])) {
            $formatted['opportunities'] = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $formatted['opportunities'] = $data['data'];
        } elseif (isset($data['writing_opportunities']) && is_array($data['writing_opportunities'])) {
            $formatted['opportunities'] = $data['writing_opportunities'];
        } else {
            // If we can't determine the structure, use the raw data
            $formatted['opportunities'] = $data;
        }

        return $formatted;
    }
}
