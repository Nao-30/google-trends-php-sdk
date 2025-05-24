# Main Client Class Implementation

This task involves creating the main Client class, which serves as the primary entry point for the Google Trends PHP SDK.

## Task Description

Implement the main Client class with the following capabilities:

1. **API Endpoint Methods**: Methods for each API endpoint
2. **Configuration Management**: Handling SDK configuration
3. **HTTP Communication**: Managing HTTP requests and responses

## Implementation Details

### Client Class Structure

The Client class should:
- Implement the ClientInterface
- Accept dependencies through constructor injection
- Manage the lifecycle of internal components
- Provide a fluent interface where appropriate

### API Endpoint Methods

Implement methods for each API endpoint:
- `trending()`: For trending searches
- `relatedTopics()`: For related topics
- `relatedQueries()`: For related queries
- `comparison()`: For comparing multiple topics
- `suggestions()`: For content creation suggestions
- `opportunities()`: For writing opportunity identification
- `growth()`: For growth pattern tracking
- `geo()`: For geographic interest analysis
- `health()`: For service health monitoring

### Parameter Handling

The client should:
- Validate required parameters
- Provide sensible defaults for optional parameters
- Convert parameter types as needed
- Format parameters according to API requirements

## Files to Create

1. `src/Client.php`

## Dependencies

- Requires RequestBuilder for constructing API requests
- Requires ResponseHandler for processing API responses
- Requires Config for configuration management
- Requires exception classes for error handling

## Acceptance Criteria

- Client must implement all API endpoint methods
- Should properly validate input parameters
- Must handle API errors gracefully
- Should support configuration through constructor and setter methods
- Must follow PSR-12 coding standards
- Should have comprehensive PHPDoc comments with examples 