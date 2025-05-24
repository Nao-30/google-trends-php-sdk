# Exception Hierarchy Implementation

This task involves creating a comprehensive exception hierarchy for the Google Trends PHP SDK.

## Task Description

Create the following exception classes:

1. **GtrendsException**: Base exception class for all SDK exceptions
2. **ApiException**: For API-related errors (HTTP errors, API errors)
3. **ConfigurationException**: For configuration-related errors
4. **NetworkException**: For network connectivity issues
5. **ValidationException**: For parameter validation errors

## Implementation Details

### GtrendsException

The base exception class should:
- Extend from PHP's native Exception
- Provide common functionality for all SDK exceptions
- Include context information methods

### ApiException

The API exception class should:
- Extend from GtrendsException
- Include HTTP status code information
- Include API error code information
- Store original response data

### ConfigurationException

The configuration exception class should:
- Extend from GtrendsException
- Include information about the invalid configuration
- Provide clear error messages for configuration issues

### NetworkException

The network exception class should:
- Extend from GtrendsException
- Include connection details
- Store underlying connection exception

### ValidationException

The validation exception class should:
- Extend from GtrendsException
- Include validation error details
- Store parameter name and expected values

## Files to Create

1. `src/Exceptions/GtrendsException.php`
2. `src/Exceptions/ApiException.php`
3. `src/Exceptions/ConfigurationException.php`
4. `src/Exceptions/NetworkException.php`
5. `src/Exceptions/ValidationException.php`

## Dependencies

None - these are foundational exception classes that other components will use.

## Acceptance Criteria

- All exception classes must extend from the base GtrendsException
- Exceptions should include helpful error messages
- Each exception type should handle its specific error scenario
- Exception classes should follow PSR-12 coding standards
- All exceptions should have comprehensive PHPDoc comments 