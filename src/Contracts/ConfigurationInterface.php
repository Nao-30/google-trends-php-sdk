<?php

namespace GtrendsSdk\Contracts;

/**
 * Interface ConfigurationInterface
 *
 * Defines the contract for configuration management in the Google Trends PHP SDK.
 * This interface outlines methods for getting, setting, and validating configuration values.
 *
 * @package GtrendsSdk\Contracts
 */
interface ConfigurationInterface
{
    /**
     * Get a configuration value by key.
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if the key doesn't exist
     * @return mixed The configuration value
     */
    public function get(string $key, $default = null);
    
    /**
     * Set a configuration value.
     *
     * @param string $key The configuration key
     * @param mixed $value The configuration value
     * @return self
     * 
     * @throws \GtrendsSdk\Exceptions\ConfigurationException When the key or value is invalid
     */
    public function set(string $key, $value): self;
    
    /**
     * Check if a configuration key exists.
     *
     * @param string $key The configuration key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool;
    
    /**
     * Get all configuration values.
     *
     * @return array All configuration values
     */
    public function all(): array;
    
    /**
     * Load configuration from an array.
     *
     * @param array $config Configuration array
     * @return self
     * 
     * @throws \GtrendsSdk\Exceptions\ConfigurationException When the configuration is invalid
     */
    public function load(array $config): self;
    
    /**
     * Load configuration from environment variables.
     *
     * @param string $prefix Environment variable prefix (e.g., 'GTRENDS_')
     * @return self
     */
    public function loadFromEnvironment(string $prefix = 'GTRENDS_'): self;
    
    /**
     * Validate the configuration.
     *
     * @return bool True if the configuration is valid, false otherwise
     * 
     * @throws \GtrendsSdk\Exceptions\ConfigurationException When the configuration is invalid
     */
    public function validate(): bool;
    
    /**
     * Get the base URI for API requests.
     *
     * @return string The base URI
     */
    public function getBaseUri(): string;
    
    /**
     * Get the HTTP client options.
     *
     * @return array HTTP client options
     */
    public function getHttpOptions(): array;
    
    /**
     * Get the default request timeout.
     *
     * @return int Timeout in seconds
     */
    public function getTimeout(): int;
    
    /**
     * Get the default request headers.
     *
     * @return array Headers as key-value pairs
     */
    public function getHeaders(): array;
    
    /**
     * Get the debug mode status.
     *
     * @return bool True if debug mode is enabled, false otherwise
     */
    public function isDebugEnabled(): bool;
} 