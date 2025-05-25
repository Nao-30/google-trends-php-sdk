<?php

namespace Gtrends\Sdk\Contracts;

/**
 * Interface ClientInterface
 *
 * Defines the contract for the main client in the Google Trends PHP SDK.
 * This interface outlines all available methods for interacting with the API endpoints.
 *
 * @package Gtrends\Sdk\Contracts
 */
interface ClientInterface
{
    /**
     * Get trending searches from Google Trends.
     *
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param int $limit Maximum number of results to return (1-100)
     * @param bool $includeNews Whether to include related news articles
     * @return array Trending search data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function trending(?string $region = null, int $limit = 10, bool $includeNews = false): array;

    /**
     * Get related topics for a search term.
     *
     * @param string $topic Search query to find related topics for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Related topics data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function relatedTopics(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array;

    /**
     * Get related queries for a search term.
     *
     * @param string $topic Search query to find related queries for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Related queries data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function relatedQueries(string $topic, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array;

    /**
     * Compare multiple search terms to see relative interest.
     *
     * @param array $topics List of topics to compare (1-5 items)
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $timeframe Time range for data (e.g., 'today 3-m', 'today 12-m')
     * @param string $category Category ID to filter results
     * @return array Comparison data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     * @throws \Gtrends\Sdk\Exceptions\ValidationException When invalid parameters are provided
     */
    public function compare(array $topics, ?string $region = null, string $timeframe = 'today 3-m', string $category = '0'): array;

    /**
     * Get content creation suggestions.
     *
     * @param string $query Search term to get suggestions for
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $contentType Type of content suggestions to return
     * @return array Suggestions data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function suggestions(string $query, ?string $region = null, string $contentType = 'all'): array;

    /**
     * Get writing opportunity identification.
     *
     * @param string $niche Niche to find opportunities in
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param int $count Number of opportunities to return
     * @return array Opportunities data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function opportunities(string $niche, ?string $region = null, int $count = 10): array;

    /**
     * Get growth pattern tracking data.
     *
     * @param string $query Search term to analyze growth for
     * @param string $timeframe Time range for data (e.g., 'today 12-m', 'today 5-y')
     * @return array Growth pattern data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function growth(string $query, string $timeframe = 'today 12-m'): array;

    /**
     * Get geographic interest analysis.
     *
     * @param string $query Search term to analyze geographic interest
     * @param string|null $region Two-letter country code (e.g., US, GB, AE)
     * @param string $resolution Geographic resolution level (COUNTRY, REGION, CITY)
     * @param string $timeframe Time range for data (e.g., 'today 12-m')
     * @param string $category Category ID to filter results
     * @param int $count Number of regions to display
     * @return array Geographic interest data
     *
     * @throws \Gtrends\Sdk\Exceptions\ApiException When the API returns an error
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function geo(string $query, ?string $region = null, string $resolution = 'COUNTRY', string $timeframe = 'today 12-m', string $category = '0', int $count = 20): array;

    /**
     * Check the health of the Google Trends API.
     *
     * @return array Health check data
     *
     * @throws \Gtrends\Sdk\Exceptions\NetworkException When a network error occurs
     */
    public function health(): array;

    /**
     * Get the current configuration of the client.
     *
     * @return ConfigurationInterface
     */
    public function getConfig(): ConfigurationInterface;

    /**
     * Set a configuration value.
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return self
     *
     * @throws \Gtrends\Sdk\Exceptions\ConfigurationException When the configuration key is invalid
     */
    public function setConfig(string $key, $value): self;
}
