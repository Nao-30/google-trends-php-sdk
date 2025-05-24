# API Endpoints Implementation

This task involves implementing resource classes for each of the Google Trends API endpoints.

## Task Description

Create resource classes for the following API endpoints:

1. **TrendingResource**: For real-time trending searches
2. **RelatedResource**: For related topics and queries
3. **ComparisonResource**: For comparing multiple topics
4. **SuggestionsResource**: For content creation suggestions
5. **OpportunitiesResource**: For writing opportunity identification
6. **GrowthResource**: For growth pattern tracking
7. **GeoResource**: For geographic interest analysis
8. **HealthResource**: For service health monitoring

## API Reference Documentation

When implementing these endpoints, refer to the API documentation files in the `reffere-helpers/gtrends` directory:

- `paths.json`: Contains Swagger documentation for each endpoint, including:
  - Endpoint URLs
  - Request methods (GET/POST)
  - Request parameters
  - Response structures
  - Error responses

- `schemas.json`: Contains JSON schema definitions for:
  - Request objects
  - Response objects
  - Error objects

These files should be consulted to ensure the SDK implementation matches the API specifications exactly.

## Implementation Details

### Common Resource Structure

Each resource class should:
- Contain methods specific to its endpoint
- Accept HTTP client as a dependency
- Handle parameter validation
- Format requests according to API requirements
- Transform API responses into consistent structures

### Endpoint-Specific Implementations

#### TrendingResource
- Implement `getTrending()` method with region and count parameters
- Support filtering by including article data

#### RelatedResource
- Implement `getRelatedTopics()` and `getRelatedQueries()` methods
- Support keyword-based filtering

#### ComparisonResource
- Implement `compare()` method for multiple keywords
- Support timeframe and regional parameters

#### SuggestionsResource
- Implement `getSuggestions()` method for content suggestions
- Support topic and content type parameters

#### OpportunitiesResource
- Implement `getOpportunities()` method for writing opportunities
- Support niche and region parameters

#### GrowthResource
- Implement `getGrowthPatterns()` method for trend analysis
- Support timeframe and keyword parameters

#### GeoResource
- Implement `getGeoInterest()` method for geographic analysis
- Support resolution and region parameters

#### HealthResource
- Implement `checkHealth()` method for API status

## Files to Create

1. `src/Resources/TrendingResource.php`
2. `src/Resources/RelatedResource.php`
3. `src/Resources/ComparisonResource.php`
4. `src/Resources/SuggestionsResource.php`
5. `src/Resources/OpportunitiesResource.php`
6. `src/Resources/GrowthResource.php`
7. `src/Resources/GeoResource.php`
8. `src/Resources/HealthResource.php`

## Dependencies

- Requires HTTP client implementation
- Requires exception handling for validation and API errors
- Uses configuration for API endpoint paths

## Acceptance Criteria

- Each resource class should implement its endpoint-specific methods
- Must validate input parameters before making API requests
- Should transform API responses into consistent formats
- Must handle API errors appropriately
- Should follow PSR-12 coding standards
- All classes should have comprehensive PHPDoc comments with examples 