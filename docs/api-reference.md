# Google Trends PHP SDK - API Reference

This document provides a detailed reference for all the API endpoints available in the Google Trends PHP SDK.

## Client Methods

### Trending Searches

```php
public function getTrending(string $geo = 'US', array $options = []): array
```

Retrieves real-time trending searches for a specific geographic location.

**Parameters:**
- `$geo` (string): The geographic location code (e.g., 'US', 'GB', 'FR')
- `$options` (array): Additional options for the request
  - `limit` (int): Maximum number of results to return
  - `category` (string): Filter by category

**Returns:**
- `array`: List of trending searches with scores and metadata

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$trending = $client->getTrending('US', ['limit' => 20]);
```

### Related Topics

```php
public function getRelatedTopics(string $keyword, array $options = []): array
```

Retrieves topics related to a specific keyword.

**Parameters:**
- `$keyword` (string): The search term to find related topics for
- `$options` (array): Additional options for the request
  - `geo` (string): Geographic location filter
  - `timeframe` (string): Time range for data (e.g., 'past-24h', 'past-7d', 'past-30d', 'past-90d', 'past-12m', 'past-5y')
  - `category` (string): Filter by category

**Returns:**
- `array`: List of related topics with relevance scores

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$relatedTopics = $client->getRelatedTopics('artificial intelligence', [
    'geo' => 'US',
    'timeframe' => 'past-30d'
]);
```

### Related Queries

```php
public function getRelatedQueries(string $keyword, array $options = []): array
```

Retrieves search queries related to a specific keyword.

**Parameters:**
- `$keyword` (string): The search term to find related queries for
- `$options` (array): Additional options for the request
  - `geo` (string): Geographic location filter
  - `timeframe` (string): Time range for data
  - `category` (string): Filter by category

**Returns:**
- `array`: List of related queries with relevance scores

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$relatedQueries = $client->getRelatedQueries('machine learning', [
    'geo' => 'US',
    'timeframe' => 'past-90d'
]);
```

### Comparison

```php
public function getComparison(array $keywords, array $options = []): array
```

Compares the interest over time for multiple keywords.

**Parameters:**
- `$keywords` (array): Array of keywords to compare (max 5)
- `$options` (array): Additional options for the request
  - `geo` (string): Geographic location filter
  - `timeframe` (string): Time range for data
  - `category` (string): Filter by category

**Returns:**
- `array`: Comparison data with interest over time for each keyword

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$comparison = $client->getComparison(['php', 'python', 'javascript'], [
    'geo' => 'US',
    'timeframe' => 'past-12m'
]);
```

### Suggestions

```php
public function getSuggestions(string $keyword, array $options = []): array
```

Retrieves content creation suggestions for a specific keyword.

**Parameters:**
- `$keyword` (string): The search term to get suggestions for
- `$options` (array): Additional options for the request
  - `type` (string): Type of suggestions (e.g., 'blog', 'video', 'social')
  - `limit` (int): Maximum number of results to return

**Returns:**
- `array`: List of content suggestions with relevance scores

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$suggestions = $client->getSuggestions('php development', [
    'type' => 'blog',
    'limit' => 10
]);
```

### Opportunities

```php
public function getOpportunities(string $keyword, array $options = []): array
```

Identifies writing opportunities for a specific keyword.

**Parameters:**
- `$keyword` (string): The search term to find opportunities for
- `$options` (array): Additional options for the request
  - `geo` (string): Geographic location filter
  - `type` (string): Type of opportunities

**Returns:**
- `array`: List of identified opportunities with scores and metadata

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$opportunities = $client->getOpportunities('web development', [
    'geo' => 'US',
    'type' => 'emerging'
]);
```

### Growth

```php
public function getGrowth(string $keyword, array $options = []): array
```

Retrieves growth patterns for a specific keyword over time.

**Parameters:**
- `$keyword` (string): The search term to analyze growth for
- `$options` (array): Additional options for the request
  - `geo` (string): Geographic location filter
  - `timeframe` (string): Time range for data
  - `category` (string): Filter by category

**Returns:**
- `array`: Growth data with time series and trend analysis

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$growth = $client->getGrowth('blockchain', [
    'geo' => 'US',
    'timeframe' => 'past-5y'
]);
```

### Geo

```php
public function getGeo(string $keyword, array $options = []): array
```

Analyzes geographic interest distribution for a specific keyword.

**Parameters:**
- `$keyword` (string): The search term to analyze geographic interest for
- `$options` (array): Additional options for the request
  - `resolution` (string): Geographic resolution (e.g., 'country', 'region', 'city')
  - `timeframe` (string): Time range for data

**Returns:**
- `array`: Geographic interest data with location-based scores

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs
- `ValidationException`: When invalid parameters are provided

**Example:**
```php
$geo = $client->getGeo('electric vehicles', [
    'resolution' => 'country',
    'timeframe' => 'past-12m'
]);
```

### Health

```php
public function getHealth(): array
```

Checks the health status of the Google Trends API service.

**Returns:**
- `array`: Service health information including status and metrics

**Throws:**
- `ApiException`: When the API returns an error response
- `NetworkException`: When a network error occurs

**Example:**
```php
$health = $client->getHealth();
```

## Exception Handling

The SDK uses a structured exception hierarchy:

- `GtrendsException`: Base exception class for all SDK exceptions
- `ApiException`: Thrown when the API returns an error response
- `ConfigurationException`: Thrown when there's a configuration error
- `NetworkException`: Thrown when a network error occurs
- `ValidationException`: Thrown when invalid parameters are provided

Example of handling exceptions:

```php
try {
    $trending = $client->getTrending('US');
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage();
} catch (NetworkException $e) {
    echo "Network Error: " . $e->getMessage();
} catch (GtrendsException $e) {
    echo "SDK Error: " . $e->getMessage();
}
``` 