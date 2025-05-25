<?php

namespace Gtrends\Sdk\Tests\Unit\Configuration;

use Gtrends\Sdk\Tests\TestCase;
use Gtrends\Sdk\Configuration\Config;
use Gtrends\Sdk\Exceptions\ConfigurationException;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_loads_config_from_array()
    {
        $config = new Config([
            'base_uri' => 'https://test-api.example.com',
            'timeout' => 15,
        ]);

        $this->assertEquals('https://test-api.example.com', $config->get('base_uri'));
        $this->assertEquals(15, $config->get('timeout'));
    }

    /** @test */
    public function it_returns_default_values_for_missing_config()
    {
        $config = new Config([]);

        // Check defaults from the Config class
        $this->assertEquals('http://localhost:3000/api/', $config->get('base_uri'));
        $this->assertEquals(30, $config->get('timeout'));
    }

    /** @test */
    public function it_loads_config_from_environment_variables()
    {
        // Set environment variables
        $_ENV['GTRENDS_BASE_URI'] = 'https://env-api.example.com';
        $_ENV['GTRENDS_TIMEOUT'] = '20';

        $config = new Config();
        $config->loadFromEnvironment();

        $this->assertEquals('https://env-api.example.com', $config->get('base_uri'));
        $this->assertEquals(20, $config->get('timeout'));

        // Clean up environment
        unset($_ENV['GTRENDS_BASE_URI']);
        unset($_ENV['GTRENDS_TIMEOUT']);
    }

    /** @test */
    public function it_validates_required_config_options()
    {
        $this->expectException(ConfigurationException::class);

        // Create an invalid config by removing required base_uri
        $config = new Config();
        $reflection = new \ReflectionClass(Config::class);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        
        $configData = $property->getValue($config);
        unset($configData['base_uri']);
        $property->setValue($config, $configData);
        
        // Now validate
        $config->validate();
    }

    /** @test */
    public function it_supports_dot_notation_for_nested_config()
    {
        $config = new Config([
            'retry' => [
                'max_attempts' => 5,
                'delay' => 2000
            ]
        ]);

        $this->assertEquals(5, $config->get('retry.max_attempts'));
        $this->assertEquals(2000, $config->get('retry.delay'));
    }

    /** @test */
    public function it_returns_all_config_as_array()
    {
        $configData = [
            'base_uri' => 'https://test-api.example.com',
            'timeout' => 15,
        ];

        $config = new Config($configData);
        $allConfig = $config->all();

        $this->assertIsArray($allConfig);
        $this->assertArrayHasKey('base_uri', $allConfig);
        $this->assertArrayHasKey('timeout', $allConfig);
    }

    /** @test */
    public function it_can_set_config_values_after_initialization()
    {
        $config = new Config([
            'base_uri' => 'https://test-api.example.com',
        ]);

        $config->set('timeout', 25);
        $this->assertEquals(25, $config->get('timeout'));

        $config->set('retry.max_attempts', 4);
        $this->assertEquals(4, $config->get('retry.max_attempts'));
    }
} 