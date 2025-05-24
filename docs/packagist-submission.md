# Packagist Submission Guidelines

This document outlines the steps for submitting the Google Trends PHP SDK to Packagist, the PHP package repository.

## Prerequisites

Before submitting to Packagist, ensure the following:

1. **Public GitHub Repository**: The package must be in a public GitHub repository.
2. **Valid composer.json**: The composer.json file must be valid and include all required fields:
   - name
   - description
   - type
   - license
   - authors
   - require
   - autoload

3. **Proper Versioning**: The package should have at least one release tag following semantic versioning (e.g., v1.0.0).
4. **Complete Documentation**: README.md and documentation should be complete and helpful.
5. **Passing Tests**: All tests should pass before submission.

## Submission Process

### 1. Create a Packagist Account

If you don't already have one:

1. Go to [Packagist.org](https://packagist.org)
2. Click "Register" in the top-right corner
3. Register using your GitHub account or create a new account

### 2. Submit the Package

1. Log in to your Packagist account
2. Click "Submit" in the top navigation
3. Enter the GitHub repository URL: `https://github.com/your-username/gtrends-php-sdk`
4. Click "Check" to validate the package information
5. Review the package information for accuracy
6. Click "Submit" to complete the submission

### 3. Set Up Webhooks

To ensure Packagist automatically updates when you push new releases:

#### Option 1: GitHub Service Integration (Recommended)

1. Go to your package page on Packagist
2. Click "Settings"
3. In the "GitHub Service" section, click "Update package now"
4. This will automatically configure the GitHub webhook

#### Option 2: Manual Webhook Setup

1. Go to your GitHub repository
2. Click "Settings" > "Webhooks" > "Add webhook"
3. Set Payload URL to: `https://packagist.org/api/github?username=your-packagist-username`
4. Set Content type to: `application/json`
5. Enable "Push events" and "Create events"
6. Click "Add webhook"

## Package Updates

After the initial submission:

1. **New Versions**: Create a new tag in your repository to release a new version
   ```bash
   git tag -a v1.0.1 -m "Release v1.0.1"
   git push origin v1.0.1
   ```

2. **Webhook Verification**: Verify that webhooks are working by checking that your package is updated on Packagist after pushing a new tag

3. **Package Statistics**: Monitor your package's download statistics and GitHub stars on Packagist

## Troubleshooting

If you encounter issues with your Packagist submission:

1. **Validation Errors**: Ensure your composer.json is valid using `composer validate`
2. **Webhook Issues**: Check the webhook delivery logs in GitHub
3. **Manual Updates**: You can manually update your package on Packagist by clicking "Update" on your package page
4. **Support**: For Packagist-specific issues, you can contact support via GitHub at [composer/packagist](https://github.com/composer/packagist)

## Maintaining Your Package

Once your package is on Packagist:

1. Keep your README.md up to date
2. Respond to issues on GitHub
3. Regularly release updates and bug fixes
4. Follow semantic versioning for all releases
5. Monitor your package for security vulnerabilities 