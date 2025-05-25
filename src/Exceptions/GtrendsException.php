<?php

namespace Gtrends\Sdk\Exceptions;

use Exception;

/**
 * Base exception class for all Google Trends SDK exceptions.
 * 
 * This class serves as the foundation for all SDK-specific exceptions,
 * providing common functionality and context information methods.
 */
class GtrendsException extends Exception
{
    /**
     * Additional context information for the exception.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Create a new exception instance with optional context data.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     * @param array<string, mixed> $context Additional context information
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the additional context information for this exception.
     *
     * @return array<string, mixed> The context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Add additional context information to this exception.
     *
     * @param string $key The context key
     * @param mixed $value The context value
     * @return self
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Add multiple context values to this exception.
     *
     * @param array<string, mixed> $context The context data to add
     * @return self
     */
    public function addContextData(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * Convert the exception to a string representation including context.
     *
     * @return string
     */
    public function __toString(): string
    {
        $baseString = parent::__toString();
        
        if (empty($this->context)) {
            return $baseString;
        }
        
        $contextString = json_encode($this->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $baseString . PHP_EOL . 'Context: ' . $contextString;
    }
} 