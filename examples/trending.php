<?php
/**
 * Google Trends PHP SDK - Trending Searches Example
 * 
 * This example demonstrates how to retrieve trending searches using the Google Trends PHP SDK.
 */

// Require the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize the client
$client = new Gtrends\Client([
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 30,
    'debug' => true
]);

try {
    // Get trending searches for the US
    echo "Getting trending searches for US...\n";
    $trending = $client->getTrending('US');
    
    // Display the results
    echo "Top trending searches:\n";
    echo "====================\n";
    
    foreach ($trending as $index => $trend) {
        echo ($index + 1) . ". " . $trend['title'] . "\n";
        echo "   Search volume: " . $trend['search_volume'] . "\n";
        echo "   Category: " . $trend['category'] . "\n";
        
        if (!empty($trend['articles'])) {
            echo "   Related articles:\n";
            foreach ($trend['articles'] as $article) {
                echo "   - " . $article['title'] . " (" . $article['source'] . ")\n";
            }
        }
        
        echo "\n";
    }
    
    // Get trending searches for the UK with a limit of 5
    echo "Getting trending searches for UK (limited to 5)...\n";
    $trendingUK = $client->getTrending('GB', ['limit' => 5]);
    
    // Display the results
    echo "Top trending searches in UK:\n";
    echo "===========================\n";
    
    foreach ($trendingUK as $index => $trend) {
        echo ($index + 1) . ". " . $trend['title'] . "\n";
    }
    
    // Get trending searches for a specific category
    echo "\nGetting trending searches in Technology category...\n";
    $trendingTech = $client->getTrending('US', ['category' => 'technology']);
    
    // Display the results
    echo "Top trending searches in Technology:\n";
    echo "==================================\n";
    
    foreach ($trendingTech as $index => $trend) {
        echo ($index + 1) . ". " . $trend['title'] . "\n";
    }

} catch (Gtrends\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getCode() . "\n";
    
    if ($e->hasResponse()) {
        echo "Response Body: " . $e->getResponseBody() . "\n";
    }
} catch (Gtrends\Exceptions\NetworkException $e) {
    echo "Network Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\GtrendsException $e) {
    echo "SDK Error: " . $e->getMessage() . "\n";
} 