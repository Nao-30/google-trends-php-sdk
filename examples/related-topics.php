<?php
/**
 * Google Trends PHP SDK - Related Topics Example
 * 
 * This example demonstrates how to retrieve related topics for a keyword
 * using the Google Trends PHP SDK.
 */

// Require the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize the client
$client = new Gtrends\Client([
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 30
]);

// Set the keyword to analyze
$keyword = 'artificial intelligence';

try {
    // Get related topics for the keyword
    echo "Getting related topics for '{$keyword}'...\n";
    $relatedTopics = $client->getRelatedTopics($keyword, [
        'geo' => 'US',
        'timeframe' => 'past-30d'
    ]);
    
    // Display the results
    echo "Related topics for '{$keyword}':\n";
    echo str_repeat('=', 30 + strlen($keyword)) . "\n";
    
    // Sort by relevance score (descending)
    usort($relatedTopics, function($a, $b) {
        return $b['relevance_score'] <=> $a['relevance_score'];
    });
    
    foreach ($relatedTopics as $index => $topic) {
        echo ($index + 1) . ". " . $topic['title'] . "\n";
        echo "   Relevance score: " . $topic['relevance_score'] . "\n";
        echo "   Topic type: " . $topic['type'] . "\n";
        
        if (!empty($topic['description'])) {
            echo "   Description: " . $topic['description'] . "\n";
        }
        
        echo "\n";
    }
    
    // Get related topics with different timeframe
    echo "Getting related topics for '{$keyword}' (past 12 months)...\n";
    $relatedTopicsYear = $client->getRelatedTopics($keyword, [
        'geo' => 'US',
        'timeframe' => 'past-12m'
    ]);
    
    // Display the results
    echo "Related topics for '{$keyword}' (past 12 months):\n";
    echo str_repeat('=', 45 + strlen($keyword)) . "\n";
    
    // Sort by relevance score (descending)
    usort($relatedTopicsYear, function($a, $b) {
        return $b['relevance_score'] <=> $a['relevance_score'];
    });
    
    // Display only top 5
    $topRelated = array_slice($relatedTopicsYear, 0, 5);
    
    foreach ($topRelated as $index => $topic) {
        echo ($index + 1) . ". " . $topic['title'] . "\n";
        echo "   Relevance score: " . $topic['relevance_score'] . "\n";
        echo "\n";
    }
    
    // Get rising related topics
    echo "Getting rising related topics for '{$keyword}'...\n";
    $risingTopics = $client->getRelatedTopics($keyword, [
        'geo' => 'US',
        'timeframe' => 'past-30d',
        'rising' => true
    ]);
    
    // Display the results
    echo "Rising related topics for '{$keyword}':\n";
    echo str_repeat('=', 32 + strlen($keyword)) . "\n";
    
    foreach ($risingTopics as $index => $topic) {
        echo ($index + 1) . ". " . $topic['title'] . "\n";
        echo "   Growth percentage: " . $topic['growth'] . "%\n";
        echo "\n";
    }

} catch (Gtrends\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\NetworkException $e) {
    echo "Network Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\GtrendsException $e) {
    echo "SDK Error: " . $e->getMessage() . "\n";
} 