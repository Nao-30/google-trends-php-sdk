<?php
/**
 * Google Trends PHP SDK - Keyword Comparison Example
 * 
 * This example demonstrates how to compare interest over time for multiple keywords
 * using the Google Trends PHP SDK.
 */

// Require the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize the client
$client = new Gtrends\Sdk\Client([
    'base_uri' => 'https://trends-api-url.com',
    'timeout' => 30
]);

// Define keywords to compare
$keywords = ['php', 'python', 'javascript', 'ruby', 'golang'];

try {
    // Compare interest over time for programming languages
    echo "Comparing interest over time for programming languages: " . implode(', ', $keywords) . "\n\n";
    
    $comparison = $client->getComparison($keywords, [
        'geo' => 'US',
        'timeframe' => 'past-12m'
    ]);
    
    // Display overall interest scores
    echo "Overall interest scores (0-100):\n";
    echo "================================\n";
    
    foreach ($comparison['average_scores'] as $keyword => $score) {
        echo str_pad($keyword, 12) . ": " . str_pad($score, 3) . " ";
        // Create a simple bar chart
        echo str_repeat('█', round($score / 5)) . "\n";
    }
    echo "\n";
    
    // Display peak points
    echo "Peak interest points:\n";
    echo "====================\n";
    
    foreach ($comparison['peak_points'] as $keyword => $peak) {
        echo str_pad($keyword, 12) . ": " . $peak['date'] . " (score: " . $peak['value'] . ")\n";
    }
    echo "\n";
    
    // Display time series data (summarized for example purposes)
    echo "Interest over time (quarterly averages):\n";
    echo "======================================\n";
    
    // Group time series data by quarter for simplified display
    $quarters = [];
    foreach ($comparison['time_series'] as $date => $values) {
        $dateObj = new DateTime($date);
        $quarter = 'Q' . ceil($dateObj->format('n') / 3) . ' ' . $dateObj->format('Y');
        
        if (!isset($quarters[$quarter])) {
            $quarters[$quarter] = [];
            foreach ($keywords as $keyword) {
                $quarters[$quarter][$keyword] = [];
            }
        }
        
        foreach ($values as $keyword => $value) {
            $quarters[$quarter][$keyword][] = $value;
        }
    }
    
    // Calculate quarterly averages
    $quarterlyAverages = [];
    foreach ($quarters as $quarter => $keywordValues) {
        $quarterlyAverages[$quarter] = [];
        foreach ($keywordValues as $keyword => $values) {
            $quarterlyAverages[$quarter][$keyword] = array_sum($values) / count($values);
        }
    }
    
    // Print the table header
    echo str_pad('Period', 10) . " | ";
    foreach ($keywords as $keyword) {
        echo str_pad($keyword, 10) . " | ";
    }
    echo "\n" . str_repeat('-', 10 + (count($keywords) * 13)) . "\n";
    
    // Print the data rows
    foreach ($quarterlyAverages as $quarter => $values) {
        echo str_pad($quarter, 10) . " | ";
        foreach ($keywords as $keyword) {
            $value = round($values[$keyword]);
            echo str_pad($value, 10) . " | ";
        }
        echo "\n";
    }
    echo "\n";
    
    // Compare interest by region
    echo "Comparing interest by region:\n";
    echo "============================\n";
    
    $regionComparison = $client->getComparison($keywords, [
        'geo' => 'US',
        'timeframe' => 'past-12m',
        'resolution' => 'region'
    ]);
    
    // Display top regions for each keyword
    foreach ($keywords as $keyword) {
        echo "Top regions for " . $keyword . ":\n";
        
        // Sort regions by interest value for this keyword
        $regions = $regionComparison['regions'];
        usort($regions, function($a, $b) use ($keyword) {
            return $b[$keyword] <=> $a[$keyword];
        });
        
        // Display top 3 regions
        $topRegions = array_slice($regions, 0, 3);
        foreach ($topRegions as $region) {
            echo "- " . $region['name'] . ": " . $region[$keyword] . "\n";
        }
        echo "\n";
    }

} catch (Gtrends\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\NetworkException $e) {
    echo "Network Error: " . $e->getMessage() . "\n";
} catch (Gtrends\Exceptions\ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
    
    if ($e->hasInvalidParameters()) {
        echo "Invalid parameters: " . implode(", ", $e->getInvalidParameters()) . "\n";
    }
} catch (Gtrends\Exceptions\GtrendsException $e) {
    echo "SDK Error: " . $e->getMessage() . "\n";
} 