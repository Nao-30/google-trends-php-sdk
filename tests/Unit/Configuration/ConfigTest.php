<?php

namespace Gtrends\Sdk\Tests\Unit\Configuration;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Exceptions\ConfigurationException;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_loads_config_from_array()
    {
        $config = new Config([
            'api_base_uri' => 'https://test-api.example.com',
            'api_timeout' => 15,
        ]);

        $this->assertEquals('https://test-api.example.com', $config->get('api_base_uri'));
        $this->assertEquals(15, $config->get('api_timeout'));
    }

    /** @test */
    public function it_returns_default_values_for_missing_config()
    {
        $config = new Config([]);

        // Assuming these are the defaults set in the Config class
        $this->assertNotNull($config->get('api_base_uri'));
        $this->assertNotNull($config->get('api_timeout'));
    }

    /** @test */
    public function it_loads_config_from_environment_variables()
    {
        // Set environment variables
        putenv('GTRENDS_API_BASE_URI=https://env-api.example.com');
        putenv('GTRENDS_API_TIMEOUT=20');

        $config = new Config();

        $this->assertEquals('https://env-api.example.com', $config->get('api_base_uri'));
        $this->assertEquals(20, $config->get('api_timeout'));

        // Clean up environment
        putenv('GTRENDS_API_BASE_URI');
        putenv('GTRENDS_API_TIMEOUT');
    }

    /** @test */
    public function it_validates_required_config_options()
    {
        $this->expectException(ConfigurationException::class);

        // Force validation error by setting api_base_uri to null
        // We're assuming the Config class validates this as required
        new Config([
            'api_base_uri' => null,
        ]);
    }

    /** @test */
    public function it_supports_dot_notation_for_nested_config()
    {
        $config = new Config([
            'options' => [
                'retry' => [
                    'attempts' => 5,
                    'delay' => 2
                ]
            ]
        ]);

        $this->assertEquals(5, $config->get('options.retry.attempts'));
        $this->assertEquals(2, $config->get('options.retry.delay'));
    }

    /** @test */
    public function it_returns_all_config_as_array()
    {
        $configData = [
            'api_base_uri' => 'https://test-api.example.com',
            'api_timeout' => 15,
        ];

        $config = new Config($configData);
        $allConfig = $config->all();

        $this->assertIsArray($allConfig);
        $this->assertArrayHasKey('api_base_uri', $allConfig);
        $this->assertArrayHasKey('api_timeout', $allConfig);
    }

    /** @test */
    public function it_can_set_config_values_after_initialization()
    {
        $config = new Config([
            'api_base_uri' => 'https://test-api.example.com',
        ]);

        $config->set('api_timeout', 25);
        $this->assertEquals(25, $config->get('api_timeout'));

        $config->set('options.retry.attempts', 4);
        $this->assertEquals(4, $config->get('options.retry.attempts'));
    }
} 