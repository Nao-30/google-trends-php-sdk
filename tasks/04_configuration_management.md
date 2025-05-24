# Configuration Management Implementation

This task involves creating the configuration management system for the Google Trends PHP SDK.

## Task Description

Implement the following configuration components:

1. **Config**: Main configuration class
2. **Environment variable support**: Loading configuration from environment variables
3. **Default configurations**: Setting sensible defaults

## Implementation Details

### Config Class

The Config class should:
- Implement the ConfigurationInterface
- Store and retrieve configuration values
- Validate configuration values
- Merge configuration from different sources
- Support runtime configuration updates

### Environment Variable Support

The configuration system should:
- Load settings from environment variables
- Follow Laravel-style naming conventions (GTRENDS_*)
- Support type conversion (strings to booleans, integers, etc.)
- Provide clear error messages for invalid environment values

### Default Configuration Values

The default configuration should include:
- Base URI for the API
- Default timeout values
- Default headers
- Retry settings
- Pagination defaults

## Files to Create

1. `src/Configuration/Config.php`
2. `config/gtrends.php` (for Laravel integration)

## Dependencies

- Requires ConfigurationInterface
- Requires ConfigurationException for error handling

## Acceptance Criteria

- Configuration should be loadable from arrays, environment variables, and files
- Should merge configuration from multiple sources with proper precedence
- Must validate configuration values and report errors clearly
- Should provide sensible defaults for all required settings
- Must follow PSR-12 coding standards
- Should have comprehensive PHPDoc comments 