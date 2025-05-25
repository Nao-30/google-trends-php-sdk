<?php

namespace Gtrends\Sdk\Http;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Contracts\ConfigurationInterface;
use Gtrends\Sdk\Contracts\RequestBuilderInterface;
use Gtrends\Sdk\Exceptions\ValidationException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * Class RequestBuilder
 * Implements the RequestBuilderInterface for building HTTP requests to the Google Trends API.
 * @package Gtrends\Sdk\Http
 */
class RequestBuilder implements RequestBuilderInterface
{
    /**
     * @var ConfigurationInterface The SDK configuration
     */
    protected ConfigurationInterface $config;

    /**
     * @var array Default request headers
     */
    protected array $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'User-Agent' => 'gtrends-php-sdk/' . Client::SDK_VERSION,
    ];

    /**
     * RequestBuilder constructor.
     * @param ConfigurationInterface $config The SDK configuration
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function createGetRequest(string $endpoint, array $queryParams = [], array $headers = []): RequestInterface
    {
        $url = $this->buildUrl($endpoint);

        if (!empty($queryParams)) {
            $url = $this->addQueryParams($url, $queryParams);
        }

        $mergedHeaders = $this->mergeHeaders($headers);

        return new Request('GET', $url, $mergedHeaders);
    }

    /**
     * {@inheritdoc}
     */
    public function createPostRequest(string $endpoint, array $data = [], array $headers = []): RequestInterface
    {
        $url = $this->buildUrl($endpoint);
        $mergedHeaders = $this->mergeHeaders($headers);
        $body = json_encode($data, JSON_UNESCAPED_SLASHES);

        if ($body === false) {
            throw new ValidationException('Failed to encode request data as JSON');
        }

        return new Request('POST', $url, $mergedHeaders, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function buildUrl(string $endpoint): string
    {
        $baseUrl = rtrim($this->config->getApiBaseUrl(), '/');
        $endpoint = ltrim($endpoint, '/');

        return "{$baseUrl}/{$endpoint}";
    }

    /**
     * {@inheritdoc}
     */
    public function addQueryParams(string $url, array $params): string
    {
        $query = http_build_query($params);

        if (empty($query)) {
            return $url;
        }

        $separator = (strpos($url, '?') !== false) ? '&' : '?';

        return "{$url}{$separator}{$query}";
    }

    /**
     * {@inheritdoc}
     */
    public function mergeHeaders(array $headers): array
    {
        // Add API key to headers if configured
        $mergedHeaders = $this->defaultHeaders;

        if ($this->config->getApiKey()) {
            $mergedHeaders['X-API-Key'] = $this->config->getApiKey();
        }

        // Merge custom headers
        foreach ($headers as $name => $value) {
            $mergedHeaders[$name] = $value;
        }

        return $mergedHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function validateParams(array $params, array $rules): bool
    {
        $errors = [];

        foreach ($rules as $param => $rule) {
            // Check required parameters
            if (strpos($rule, 'required') !== false && !isset($params[$param])) {
                $errors[] = "The parameter '{$param}' is required";
                continue;
            }

            // Skip validation for optional params that aren't present
            if (!isset($params[$param])) {
                continue;
            }

            // Validate parameter type
            if (strpos($rule, 'string') !== false && !is_string($params[$param])) {
                $errors[] = "The parameter '{$param}' must be a string";
            } elseif (strpos($rule, 'integer') !== false && !is_int($params[$param])) {
                $errors[] = "The parameter '{$param}' must be an integer";
            } elseif (strpos($rule, 'array') !== false && !is_array($params[$param])) {
                $errors[] = "The parameter '{$param}' must be an array";
            } elseif (strpos($rule, 'boolean') !== false && !is_bool($params[$param])) {
                $errors[] = "The parameter '{$param}' must be a boolean";
            }

            // Validate enum values
            if (strpos($rule, 'in:') !== false) {
                preg_match('/in:([^|]+)/', $rule, $matches);
                if (isset($matches[1])) {
                    $allowedValues = explode(',', $matches[1]);
                    if (!in_array($params[$param], $allowedValues)) {
                        $errors[] = "The parameter '{$param}' must be one of: " . implode(', ', $allowedValues);
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException(
                'Validation failed for request parameters',
                400,
                null,
                ['errors' => $errors]
            );
        }

        return true;
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
    public function setConfig(ConfigurationInterface $config): self
    {
        $this->config = $config;
        return $this;
    }
}
