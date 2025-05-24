# Release Plan for Google Trends PHP SDK

This document outlines the release strategy and version management for the Google Trends PHP SDK.

## Semantic Versioning

The Google Trends PHP SDK follows [Semantic Versioning 2.0.0](https://semver.org/). Version numbers are structured as MAJOR.MINOR.PATCH:

- **MAJOR** version increases for incompatible API changes
- **MINOR** version increases for backward-compatible new functionality
- **PATCH** version increases for backward-compatible bug fixes

## Release Process

### Pre-release Checklist

Before creating a new release:

1. Ensure all tests pass: `composer test`
2. Check code style compliance: `composer check-style`
3. Update CHANGELOG.md with all changes since the last release
4. Update documentation if necessary
5. Verify that all GitHub issues for the milestone are resolved

### Creating a Release

1. Update version number in relevant files:
   - composer.json
   - src/Client.php (SDK_VERSION constant)
   - CHANGELOG.md

2. Commit the version changes:
   ```bash
   git add .
   git commit -m "Prepare release vX.Y.Z"
   ```

3. Create a git tag:
   ```bash
   git tag -a vX.Y.Z -m "Release vX.Y.Z"
   ```

4. Push the changes and tag:
   ```bash
   git push origin main
   git push origin vX.Y.Z
   ```

5. Create a GitHub release:
   - Navigate to GitHub Releases
   - Create a new release using the tag
   - Include release notes from CHANGELOG.md

### Packagist Submission

The Google Trends PHP SDK is distributed through [Packagist](https://packagist.org/), the PHP package repository.

#### Initial Submission

For the initial submission to Packagist:

1. Ensure the composer.json file is properly configured:
   - Proper package name (gtrends/php-sdk)
   - Description
   - License
   - Author information
   - Required dependencies
   - PSR-4 autoloading

2. Register the package on Packagist:
   - Visit https://packagist.org/packages/submit
   - Submit the GitHub repository URL
   - Verify package information

#### Maintaining Packagist Updates

To ensure Packagist is automatically updated:

1. Configure GitHub Webhooks:
   - In the GitHub repository settings, add a webhook for Packagist
   - URL: https://packagist.org/api/github
   - Content type: application/json
   - Secret: (Obtain from Packagist account)
   - Events: Push and Create (for tags)

2. Alternative: Configure Packagist to auto-update:
   - In Packagist, enable GitHub integration for the package
   - This allows Packagist to automatically check for updates

## Release Schedule

The Google Trends PHP SDK follows a regular release schedule:

- **Major releases**: Approximately once per year
- **Minor releases**: Every 2-3 months for new features
- **Patch releases**: As needed for bug fixes (typically within 2 weeks of critical bug reports)

## Support Policy

- **Active support**: The latest major version receives active development and patches
- **Security fixes**: The previous major version receives security fixes for 6 months
- **End-of-life**: Older versions receive no updates and should be upgraded

## First Release

The initial release (v1.0.0) will include:

- Core SDK functionality with all API endpoints
- Laravel integration
- Comprehensive documentation
- Complete test suite

## Future Releases

Plans for future releases are documented in the roadmap (docs/roadmap.md) and may include:

- Additional framework integrations
- Advanced caching strategies
- Performance optimizations
- Additional API endpoints as they become available 