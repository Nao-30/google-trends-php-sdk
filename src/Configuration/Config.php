<?php

namespace Gtrends\Sdk\Configuration;

use Gtrends\Sdk\Client;
use Gtrends\Sdk\Contracts\ConfigurationInterface;
use Gtrends\Sdk\Exceptions\ConfigurationException;

/**
 * Configuration management class for the Google Trends PHP SDK.
 *
 * This class handles loading, validating, and retrieving configuration values
 * from various sources including arrays, environment variables, and files.
 *
 * @package Gtrends\Sdk\Configuration
 */
class Config implements ConfigurationInterface
{
    /**
     * The configuration values.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Default configuration values.
     *
     * @var array<string, mixed>
     */
    protected array $defaults = [
        'base_uri' => 'http://localhost:3000/api/',
        'timeout' => 30,
        'headers' => [
            'User-Agent' => 'Gtrends/Sdk/' . Client::SDK_VERSION,
            'Accept' => 'application/json',
        ],
        'retry' => [
            'max_attempts' => 3,
            'delay' => 1000,
            'multiplier' => 2,
        ],
        'pagination' => [
            'per_page' => 20,
            'max_items' => 100,
        ],
        'debug' => false,
    ];

    /**
     * Required configuration keys.
     *
     * @var array<string>
     */
    protected array $required = [
        'base_uri',
        'timeout',
    ];

    /**
     * Configuration validation rules.
     *
     * @var array<string, array>
     */
    protected array $validationRules = [
        'base_uri' => ['type' => 'string', 'required' => true],
        'timeout' => ['type' => 'integer', 'min' => 1, 'max' => 120, 'required' => true],
        'headers' => ['type' => 'array', 'required' => false],
        'retry.max_attempts' => ['type' => 'integer', 'min' => 0, 'max' => 10, 'required' => false],
        'retry.delay' => ['type' => 'integer', 'min' => 100, 'max' => 10000, 'required' => false],
        'retry.multiplier' => ['type' => 'float', 'min' => 1.0, 'max' => 5.0, 'required' => false],
        'pagination.per_page' => ['type' => 'integer', 'min' => 5, 'max' => 100, 'required' => false],
        'pagination.max_items' => ['type' => 'integer', 'min' => 5, 'max' => 1000, 'required' => false],
        'debug' => ['type' => 'boolean', 'required' => false],
    ];

    /**
     * @var bool Whether to make real HTTP requests.
     */
    protected bool $makeRealRequests = true;

    /**
     * Create a new Config instance.
     *
     * @param array<string, mixed> $config Initial configuration
     */
    public function __construct(array $config = [])
    {
        // Initialize with defaults
        $this->config = $this->defaults;

        // Load provided configuration
        if (!empty($config)) {
            $this->load($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): self
    {
        // Validate the key and value against validation rules
        if (array_key_exists($key, $this->validationRules)) {
            $this->validateValue($key, $value);
        }

        $keys = explode('.', $key);
        $config = &$this->config;

        // Navigate to the correct nesting level
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
                break;
            }

            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }

            $config = &$config[$segment];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return false;
            }
            $config = $config[$segment];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config): self
    {
        $this->config = array_replace_recursive($this->config, $config);
        
        // Validate the configuration
        $this->validate();
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromEnvironment(string $prefix = 'GTRENDS_'): self
    {
        $envConfig = [];
        
        foreach ($_ENV as $key => $value) {
            // Check if this environment variable has our prefix
            if (strpos($key, $prefix) === 0) {
                // Remove the prefix
                $configKey = strtolower(str_replace($prefix, '', $key));
                
                // Convert to snake_case and handle nested keys
                $configKey = str_replace('__', '.', $configKey);
                
                // Convert value types
                $envConfig[$configKey] = $this->parseEnvironmentValue($value);
            }
        }
        
        // Load the configuration values from environment
        if (!empty($envConfig)) {
            $this->load($envConfig);
        }
        
        return $this;
    }

    /**
     * Parse an environment variable value and convert it to the appropriate type.
     *
     * @param string $value The environment variable value
     * @return mixed The converted value
     */
    protected function parseEnvironmentValue(string $value)
    {
        // Convert "true" and "false" strings to boolean
        if (strtolower($value) === 'true') {
            return true;
        }
        
        if (strtolower($value) === 'false') {
            return false;
        }
        
        // Convert numeric strings to integers or floats
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }
        
        // Handle JSON data
        if (strpos($value, '{') === 0 || strpos($value, '[') === 0) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // Default to string
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // Check for required keys
        foreach ($this->required as $key) {
            if (!$this->has($key)) {
                throw new ConfigurationException(
                    "Missing required configuration key: {$key}",
                    0,
                    null,
                    [],
                    $key,
                    null,
                    'Required configuration value'
                );
            }
        }
        
        // Validate all values according to rules
        foreach ($this->validationRules as $key => $rules) {
            if (isset($rules['required']) && $rules['required'] && !$this->has($key)) {
                throw new ConfigurationException(
                    "Missing required configuration key: {$key}",
                    0,
                    null,
                    [],
                    $key,
                    null,
                    'Required configuration value'
                );
            }
            
            if ($this->has($key)) {
                $this->validateValue($key, $this->get($key));
            }
        }
        
        return true;
    }

    /**
     * Validate a configuration value against its rules.
     *
     * @param string $key The configuration key
     * @param mixed $value The value to validate
     * @return bool True if valid
     * 
     * @throws ConfigurationException When validation fails
     */
    protected function validateValue(string $key, $value): bool
    {
        // If no rules exist for this key, consider it valid
        if (!isset($this->validationRules[$key])) {
            return true;
        }
        
        $rules = $this->validationRules[$key];
        
        // Type validation
        if (isset($rules['type'])) {
            $valid = false;
            
            switch ($rules['type']) {
                case 'string':
                    $valid = is_string($value);
                    break;
                    
                case 'integer':
                    $valid = is_int($value);
                    break;
                    
                case 'float':
                    $valid = is_float($value) || is_int($value);
                    break;
                    
                case 'boolean':
                    $valid = is_bool($value);
                    break;
                    
                case 'array':
                    $valid = is_array($value);
                    break;
            }
            
            if (!$valid) {
                throw new ConfigurationException(
                    "Invalid type for configuration key {$key}: expected {$rules['type']}",
                    0,
                    null,
                    [],
                    $key,
                    $value,
                    $rules['type']
                );
            }
        }
        
        // Min/max validation for numeric types
        if (is_numeric($value)) {
            if (isset($rules['min']) && $value < $rules['min']) {
                throw new ConfigurationException(
                    "Value for {$key} is too small: minimum is {$rules['min']}",
                    0,
                    null,
                    [],
                    $key,
                    $value,
                    "Value >= {$rules['min']}"
                );
            }
            
            if (isset($rules['max']) && $value > $rules['max']) {
                throw new ConfigurationException(
                    "Value for {$key} is too large: maximum is {$rules['max']}",
                    0,
                    null,
                    [],
                    $key,
                    $value,
                    "Value <= {$rules['max']}"
                );
            }
        }
        
        return true;
    }

    /**
     * Get the API base URL.
     *
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        return $this->get('base_uri', $this->defaults['base_uri']);
    }

    /**
     * Get the maximum number of retry attempts.
     *
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->get('retry.max_attempts', $this->defaults['retry']['max_attempts']);
    }
    
    /**
     * Get the retry delay in milliseconds.
     *
     * @return int
     */
    public function getRetryDelay(): int
    {
        return $this->get('retry.delay', $this->defaults['retry']['delay']);
    }
    
    /**
     * Get the retry delay multiplier.
     *
     * @return float
     */
    public function getRetryMultiplier(): float
    {
        return $this->get('retry.multiplier', $this->defaults['retry']['multiplier']);
    }
    
    /**
     * Alias for getBaseUri to maintain compatibility.
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->getApiBaseUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpOptions(): array
    {
        return [
            'timeout' => $this->getTimeout(),
            'headers' => $this->getHeaders(),
            'http_errors' => false, // We'll handle errors ourselves
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(): int
    {
        return (int) $this->get('timeout');
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->get('headers', []);
    }

    /**
     * {@inheritdoc}
     */
    public function isDebugEnabled(): bool
    {
        return (bool) $this->get('debug', false);
    }

    /**
     * Get the API key.
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->get('api_key');
    }
    
    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->isDebugEnabled();
    }

    /**
     * Get the connection timeout in seconds.
     *
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->get('timeout', $this->defaults['timeout']);
    }

    /**
     * Check if SSL verification should be enabled.
     *
     * @return bool
     */
    public function shouldVerifySsl(): bool
    {
        return $this->get('verify_ssl', true);
    }

    /**
     * Set whether to make real HTTP requests or not.
     *
     * @param bool $enable Whether to enable real HTTP requests
     * @return self
     */
    public function setMakeRealRequests(bool $enable): self
    {
        $this->makeRealRequests = $enable;
        return $this;
    }

    /**
     * Check if real HTTP requests are enabled.
     *
     * @return bool True if real HTTP requests are enabled
     */
    public function shouldMakeRealRequests(): bool
    {
        return $this->makeRealRequests;
    }
} 