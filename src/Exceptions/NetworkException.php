<?php

namespace Gtrends\Sdk\Exceptions;

/**
 * Exception thrown for network connectivity issues.
 *
 * This exception is used when there are problems with network connectivity,
 * such as connection timeouts, DNS resolution failures, etc.
 */
class NetworkException extends GtrendsException
{
    /**
     * The URL that was being accessed when the error occurred.
     *
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * The HTTP method being used when the error occurred.
     *
     * @var string|null
     */
    protected ?string $method = null;

    /**
     * The underlying connection exception, if available.
     *
     * @var \Throwable|null
     */
    protected ?\Throwable $connectionException = null;

    /**
     * Create a new network exception instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     * @param array<string, mixed> $context Additional context information
     * @param string|null $url The URL that was being accessed
     * @param string|null $method The HTTP method being used
     * @param \Throwable|null $connectionException The underlying connection exception
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = [],
        ?string $url = null,
        ?string $method = null,
        ?\Throwable $connectionException = null
    ) {
        parent::__construct($message, $code, $previous, $context);

        $this->url = $url;
        $this->method = $method;
        $this->connectionException = $connectionException;

        // Add network information to context
        if ($url !== null) {
            $this->addContext('url', $url);
        }

        if ($method !== null) {
            $this->addContext('method', $method);
        }

        if ($connectionException !== null) {
            $this->addContext('connection_exception', [
                'class' => get_class($connectionException),
                'message' => $connectionException->getMessage(),
                'code' => $connectionException->getCode(),
            ]);
        }
    }

    /**
     * Get the URL that was being accessed when the error occurred.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get the HTTP method being used when the error occurred.
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Get the underlying connection exception.
     *
     * @return \Throwable|null
     */
    public function getConnectionException(): ?\Throwable
    {
        return $this->connectionException;
    }
}
