<?php

namespace Gtrends\Sdk\Http;

use Gtrends\Sdk\Contracts\ConfigurationInterface;
use Gtrends\Sdk\Exceptions\NetworkException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HttpClient
 *
 * Wrapper for GuzzleHttp client that handles retry logic, timeouts, and error handling.
 *
 * @package Gtrends\Sdk\Http
 */
class HttpClient
{
    /**
     * @var GuzzleClient The Guzzle HTTP client
     */
    protected GuzzleClient $client;

    /**
     * @var ConfigurationInterface The SDK configuration
     */
    protected ConfigurationInterface $config;

    /**
     * @var LoggerInterface The logger instance
     */
    protected LoggerInterface $logger;

    /**
     * HttpClient constructor.
     *
     * @param ConfigurationInterface $config The SDK configuration
     * @param LoggerInterface|null $logger The logger instance (optional)
     */
    public function __construct(ConfigurationInterface $config, ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->initializeClient();
    }

    /**
     * Initialize the Guzzle client with configuration options.
     */
    protected function initializeClient(): void
    {
        $stack = HandlerStack::create();

        // Add retry middleware if retries are enabled
        if ($this->config->getMaxRetries() > 0) {
            $stack->push($this->createRetryMiddleware());
        }

        // Add logging middleware if debug mode is enabled
        if ($this->config->isDebugMode()) {
            $stack->push($this->createLoggingMiddleware());
        }

        $this->client = new GuzzleClient([
            'handler' => $stack,
            'timeout' => $this->config->getTimeout(),
            'connect_timeout' => $this->config->getConnectTimeout(),
            'http_errors' => false, // We'll handle HTTP errors ourselves
            'verify' => $this->config->shouldVerifySsl(),
        ]);
    }

    /**
     * Create middleware for request retries.
     *
     * @return callable Retry middleware
     */
    protected function createRetryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                $retries,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?GuzzleException $exception = null
            ) {
                // Don't retry if we've reached the max retries
                if ($retries >= $this->config->getMaxRetries()) {
                    return false;
                }

                // Retry on server errors (5xx)
                if ($response && $response->getStatusCode() >= 500) {
                    $this->logRetry($retries, $request, $response, $exception);
                    return true;
                }

                // Retry on connection errors
                if ($exception instanceof GuzzleException) {
                    $this->logRetry($retries, $request, $response, $exception);
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff with jitter
                $delay = (int) pow(2, $retries) * 1000;
                $jitter = random_int(0, 500);
                return $delay + $jitter;
            }
        );
    }

    /**
     * Create middleware for logging requests and responses.
     *
     * @return callable Logging middleware
     */
    protected function createLoggingMiddleware(): callable
    {
        return Middleware::tap(
            function (RequestInterface $request, array $options) {
                $this->logger->debug('API Request', [
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'body' => (string) $request->getBody(),
                ]);
            },
            function (RequestInterface $request, array $options, ResponseInterface $response) {
                $this->logger->debug('API Response', [
                    'status_code' => $response->getStatusCode(),
                    'reason_phrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => (string) $response->getBody(),
                ]);
            }
        );
    }

    /**
     * Log a retry attempt.
     *
     * @param int $retries Current retry count
     * @param RequestInterface $request The request
     * @param ResponseInterface|null $response The response (if available)
     * @param \Throwable|null $exception The exception (if available)
     */
    protected function logRetry(
        int $retries,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?\Throwable $exception = null
    ): void {
        $this->logger->info('Retrying request', [
            'retry_count' => $retries,
            'uri' => (string) $request->getUri(),
            'method' => $request->getMethod(),
            'status_code' => $response ? $response->getStatusCode() : null,
            'error' => $exception ? $exception->getMessage() : null,
        ]);
    }

    /**
     * Send a request and return the response.
     *
     * @param RequestInterface $request The request to send
     * @return ResponseInterface The response
     *
     * @throws NetworkException When a network error occurs
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // If real HTTP requests are disabled, throw a NetworkException to simulate a network error
        if (!$this->config->shouldMakeRealRequests()) {
            throw new NetworkException(
                'HTTP requests are disabled in test mode',
                400,
                null,
                ['request_uri' => (string) $request->getUri()],
                (string) $request->getUri(),
                $request->getMethod()
            );
        }

        try {
            return $this->client->send($request, [
                'timeout' => $this->config->getTimeout(),
                'connect_timeout' => $this->config->getConnectTimeout(),
            ]);
        } catch (GuzzleException $e) {
            throw new NetworkException(
                'Network error occurred: ' . $e->getMessage(),
                0, // Error code should be an integer
                $e, // Previous exception
                ['exception' => get_class($e)], // Context as array
                (string) $request->getUri(), // URL
                $request->getMethod() // Method
            );
        }
    }

    /**
     * Send a request asynchronously.
     *
     * @param RequestInterface $request The request to send
     * @return \GuzzleHttp\Promise\PromiseInterface The promise
     */
    public function sendRequestAsync(RequestInterface $request): \GuzzleHttp\Promise\PromiseInterface
    {
        return $this->client->sendAsync($request, [
            'timeout' => $this->config->getTimeout(),
            'connect_timeout' => $this->config->getConnectTimeout(),
        ]);
    }

    /**
     * Get the underlying Guzzle client.
     *
     * @return GuzzleClient The Guzzle client
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * Get the configuration instance.
     *
     * @return ConfigurationInterface The configuration
     */
    public function getConfig(): ConfigurationInterface
    {
        return $this->config;
    }

    /**
     * Set the configuration instance.
     *
     * @param ConfigurationInterface $config The configuration
     * @return self
     */
    public function setConfig(ConfigurationInterface $config): self
    {
        $this->config = $config;
        $this->initializeClient(); // Reinitialize client with new config
        return $this;
    }

    /**
     * Get the logger instance.
     *
     * @return LoggerInterface The logger
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set the logger instance.
     *
     * @param LoggerInterface $logger The logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
