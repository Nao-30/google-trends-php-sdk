<?php

namespace Gtrends\Sdk\Exceptions;

/**
 * Exception thrown for configuration-related errors.
 *
 * This exception is used when there are issues with the SDK configuration,
 * such as missing required settings, invalid configuration values, etc.
 */
class ConfigurationException extends GtrendsException
{
    /**
     * The configuration key that caused the error.
     *
     * @var string|null
     */
    protected ?string $configKey = null;

    /**
     * The invalid value that caused the error.
     *
     * @var mixed
     */
    protected mixed $invalidValue = null;

    /**
     * The expected type or value description.
     *
     * @var string|null
     */
    protected ?string $expectedValue = null;

    /**
     * Create a new configuration exception instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     * @param array<string, mixed> $context Additional context information
     * @param string|null $configKey The configuration key that caused the error
     * @param mixed $invalidValue The invalid value
     * @param string|null $expectedValue Description of the expected value or type
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = [],
        ?string $configKey = null,
        mixed $invalidValue = null,
        ?string $expectedValue = null
    ) {
        parent::__construct($message, $code, $previous, $context);

        $this->configKey = $configKey;
        $this->invalidValue = $invalidValue;
        $this->expectedValue = $expectedValue;

        // Add configuration information to context
        if ($configKey !== null) {
            $this->addContext('config_key', $configKey);
        }

        if ($invalidValue !== null) {
            // Use var_export for a string representation of any value
            $this->addContext('invalid_value', var_export($invalidValue, true));
        }

        if ($expectedValue !== null) {
            $this->addContext('expected_value', $expectedValue);
        }
    }

    /**
     * Get the configuration key that caused the error.
     *
     * @return string|null
     */
    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    /**
     * Get the invalid value that caused the error.
     *
     * @return mixed
     */
    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    /**
     * Get the expected value or type description.
     *
     * @return string|null
     */
    public function getExpectedValue(): ?string
    {
        return $this->expectedValue;
    }
}
