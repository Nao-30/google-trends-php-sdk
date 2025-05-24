<?php

namespace GtrendsSdk\Http;

use GtrendsSdk\Contracts\ConfigurationInterface;
use GtrendsSdk\Contracts\ResponseHandlerInterface;
use GtrendsSdk\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseHandler
 * 
 * Implements the ResponseHandlerInterface for handling HTTP responses from the Google Trends API.
 * 
 * @package GtrendsSdk\Http
 */
class ResponseHandler implements ResponseHandlerInterface
{
    /**
     * @var ConfigurationInterface The SDK configuration
     */
    protected ConfigurationInterface $config;

    /**
     * ResponseHandler constructor.
     * 
     * @param ConfigurationInterface $config The SDK configuration
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function processResponse(ResponseInterface $response): array
    {
        if (!$this->isSuccessful($response)) {
            $errorDetails = $this->getErrorDetails($response);
            throw new ApiException(
                $errorDetails['message'] ?? 'API request failed',
                $errorDetails
            );
        }
        
        return $this->extractJson($response);
    }

    /**
     * {@inheritdoc}
     */
    public function extractJson(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        
        if (empty($body)) {
            return [];
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Failed to decode JSON response: ' . json_last_error_msg(),
                ['raw_body' => $body]
            );
        }
        
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorDetails(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        
        try {
            $body = $this->extractJson($response);
            
            return [
                'status_code' => $statusCode,
                'reason' => $reasonPhrase,
                'message' => $body['error'] ?? $body['message'] ?? $reasonPhrase,
                'errors' => $body['errors'] ?? [],
                'code' => $body['code'] ?? $statusCode,
                'details' => $body['details'] ?? [],
                'debug_id' => $body['debug_id'] ?? null,
                'raw_response' => $body,
            ];
        } catch (\Throwable $e) {
            // If we can't extract JSON, return basic error details
            return [
                'status_code' => $statusCode,
                'reason' => $reasonPhrase,
                'message' => $reasonPhrase,
                'raw_response' => (string) $response->getBody(),
                'parse_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transformResponseData(array $data, string $endpoint): array
    {
        // Basic transformation for different endpoints
        // More specific transformations can be added based on the endpoint
        
        // Remove unnecessary metadata if present
        if (isset($data['meta'])) {
            $metadata = $data['meta'];
            unset($data['meta']);
            $data['_metadata'] = $metadata;
        }
        
        // Standardize data format
        if (isset($data['data']) && is_array($data['data'])) {
            return [
                'items' => $data['data'],
                'endpoint' => $endpoint,
                'metadata' => $data['_metadata'] ?? [],
            ];
        }
        
        // If data doesn't match expected format, return as is
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function validateResponseData(array $data, array $requiredFields): bool
    {
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new ApiException(
                'API response missing required fields',
                ['missing_fields' => $missingFields]
            );
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo(ResponseInterface $response): array
    {
        return [
            'status_code' => $response->getStatusCode(),
            'reason_phrase' => $response->getReasonPhrase(),
            'protocol_version' => $response->getProtocolVersion(),
            'headers' => $response->getHeaders(),
            'body_size' => $response->getBody()->getSize(),
            'body_preview' => $this->getBodyPreview($response),
            'timestamp' => time(),
        ];
    }
    
    /**
     * Get a preview of the response body for debugging.
     *
     * @param ResponseInterface $response PSR-7 Response object
     * @param int $maxLength Maximum length of the preview
     * @return string Body preview
     */
    protected function getBodyPreview(ResponseInterface $response, int $maxLength = 500): string
    {
        $body = (string) $response->getBody();
        
        if (strlen($body) <= $maxLength) {
            return $body;
        }
        
        return substr($body, 0, $maxLength) . '...';
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