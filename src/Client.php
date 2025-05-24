<?php

namespace GtrendsSdk;

use GtrendsSdk\Configuration\Config;
use GtrendsSdk\Contracts\ClientInterface;
use GtrendsSdk\Contracts\ConfigurationInterface;
use GtrendsSdk\Contracts\RequestBuilderInterface;
use GtrendsSdk\Contracts\ResponseHandlerInterface;
use GtrendsSdk\Exceptions\ApiException;
use GtrendsSdk\Exceptions\NetworkException;
use GtrendsSdk\Exceptions\ValidationException;
use GtrendsSdk\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Main client class for the Google Trends PHP SDK.
 *
 * This class serves as the primary entry point for interacting with the
 * Google Trends API endpoints. It manages configuration, HTTP communication,
 * and provides methods for each API endpoint.
 *
 * @package GtrendsSdk
 */
class Client implements ClientInterface
{
    /**
     * SDK version number
     *
     * @var string
     */
    public const SDK_VERSION = '1.0.0';

    /**
     * @var ConfigurationInterface The SDK configuration
     */
    protected ConfigurationInterface $config;

    /**
     * @var RequestBuilderInterface The request builder
     */
    protected RequestBuilderInterface $requestBuilder;

    /**
     * @var ResponseHandlerInterface The response handler
     */
    protected ResponseHandlerInterface $responseHandler;

    /**
     * @var HttpClient The HTTP client
     */
    protected HttpClient $httpClient;

    /**
     * @var LoggerInterface The logger instance
     */
    protected LoggerInterface $logger;

    /**
     * Create a new Client instance.
     *
     * @param ConfigurationInterface|array|null $config Configuration instance or array
     * @param RequestBuilderInterface|null $requestBuilder Request builder instance
     * @param ResponseHandlerInterface|null $responseHandler Response handler instance
     * @param LoggerInterface|null $logger Logger instance
     */
    public function __construct(
        $config = null,
        ?RequestBuilderInterface $requestBuilder = null,
        ?ResponseHandlerInterface $responseHandler = null,
        ?LoggerInterface $logger = null
    ) {
        // Initialize configuration
        if ($config instanceof ConfigurationInterface) {
            $this->config = $config;
        } elseif (is_array($config)) {
            $this->config = new Config($config);
        } else {
            $this->config = new Config();
        }

        $this->logger = $logger ?? new NullLogger();
        
        // Initialize HTTP client
        $this->httpClient = new HttpClient($this->config, $this->logger);
        
        // Initialize request builder and response handler with provided instances or create new ones
        $this->requestBuilder = $requestBuilder;
        if (!$this->requestBuilder) {
            // Assuming we have a RequestBuilder class in the Http namespace
            $this->requestBuilder = new Http\RequestBuilder($this->config);
        }
        
        $this->responseHandler = $responseHandler;
        if (!$this->responseHandler) {
            // Assuming we have a ResponseHandler class in the Http namespace
            $this->responseHandler = new Http\ResponseHandler($this->config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function trending(?string $region = null, int $limit = 10, bool $includeNews = false): array
    {
        $this->validateLimit($limit, 1, 100);
        
        $params = [
            'limit' => $limit,
            'include_news' => $includeNews,
        ];
        
        if ($region !== null) {
            $params['region'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('trending', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function relatedTopics(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        $params = [
            'q' => $topic,
            'time' => $timeframe,
            'cat' => $category,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('related-topics', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function relatedQueries(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        $params = [
            'q' => $topic,
            'time' => $timeframe,
            'cat' => $category,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('related-queries', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function compare(array $topics, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array
    {
        if (empty($topics)) {
            throw new ValidationException('At least one topic is required for comparison');
        }
        
        if (count($topics) > 5) {
            throw new ValidationException('Maximum of 5 topics can be compared');
        }
        
        $params = [
            'q' => implode(',', $topics),
            'time' => $timeframe,
            'cat' => $category,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('comparison', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function suggestions(string $query, ?string $region = null, string $contentType = 'all'): array
    {
        $params = [
            'q' => $query,
            'type' => $contentType,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('suggestions', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function opportunities(string $niche, ?string $region = null, int $count = 10): array
    {
        $this->validateLimit($count, 1, 100);
        
        $params = [
            'niche' => $niche,
            'limit' => $count,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('opportunities', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function growth(string $query, string $timeframe = 'today 12-m'): array
    {
        $params = [
            'q' => $query,
            'time' => $timeframe,
        ];
        
        $request = $this->requestBuilder->createGetRequest('growth', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function geo(string $query, ?string $region = null, string $resolution = 'COUNTRY', string $timeframe = 'today 12-m', string $category = '0', int $count = 20): array
    {
        $this->validateLimit($count, 1, 100);
        
        $params = [
            'q' => $query,
            'resolution' => $resolution,
            'time' => $timeframe,
            'cat' => $category,
            'limit' => $count,
        ];
        
        if ($region !== null) {
            $params['geo'] = $region;
        }
        
        $request = $this->requestBuilder->createGetRequest('geo', $params);
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function health(): array
    {
        $request = $this->requestBuilder->createGetRequest('health');
        $response = $this->httpClient->sendRequest($request);
        
        return $this->responseHandler->processResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): ConfigurationInterface
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(string $key, $value): self
    {
        $this->config->set($key, $value);
        
        // Update config in dependencies
        $this->requestBuilder->setConfig($this->config);
        $this->responseHandler->setConfig($this->config);
        $this->httpClient->setConfig($this->config);
        
        return $this;
    }

    /**
     * Validate a limit parameter.
     *
     * @param int $limit The limit value to validate
     * @param int $min The minimum allowed value
     * @param int $max The maximum allowed value
     * @return bool True if valid
     * 
     * @throws ValidationException When the limit is outside the allowed range
     */
    protected function validateLimit(int $limit, int $min, int $max): bool
    {
        if ($limit < $min || $limit > $max) {
            throw new ValidationException(
                sprintf('Limit must be between %d and %d, %d given', $min, $max, $limit)
            );
        }
        
        return true;
    }
} 