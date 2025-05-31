# Task 12: Release Automation with GitHub Actions

## Task Description

This task involves creating a comprehensive GitHub Actions workflow for automating the release process of the gtrends-php-sdk. The automation will cover testing, building, tagging, releasing, and publishing to Packagist, ensuring a consistent and error-free release process for version 1.0.0 and future versions.

## Implementation Details

### 1. GitHub Actions Workflow for CI/CD

Create a complete CI/CD pipeline using GitHub Actions with the following stages:

#### 1.1. Testing Stage
- Set up PHP environment with multiple PHP versions (8.0, 8.1, 8.2)
- Install Composer dependencies
- Run PHP linting
- Execute PHPUnit tests
- Perform static analysis with PHPStan
- Check code style with PHP_CodeSniffer
- Generate code coverage report
- Ensure code coverage meets minimum threshold

#### 1.2. Build Stage
- Validate composer.json
- Create optimized Composer autoloader
- Generate API documentation
- Package the release artifacts
- Create CHANGELOG entries based on commits

#### 1.3. Release Stage (Only on Tagged Commits)
- Verify tag follows semantic versioning
- Create GitHub release with release notes
- Attach packaged artifacts to release
- Update CHANGELOG.md in repository

#### 1.4. Publish Stage (Only on Successful Release)
- Publish to Packagist
- Update documentation website (if applicable)
- Send notifications (Slack, email, etc.)

### 2. Release Scripts and Utilities

#### 2.1. Version Bumping Script
- Create a script to bump version numbers in:
  - composer.json
  - src/Client.php (SDK_VERSION constant)
  - Other relevant files

#### 2.2. Changelog Generator
- Create a tool to generate CHANGELOG entries from commit messages
- Format according to Keep a Changelog standard

#### 2.3. Release Checklist Validator
- Create a validator to ensure all release requirements are met:
  - All tests pass
  - Documentation is up-to-date
  - Version numbers are consistent
  - CHANGELOG is properly updated

### 3. Security Checks

#### 3.1. Dependency Vulnerability Scanning
- Integrate Composer security checks
- Add GitHub's Dependabot for dependency monitoring

#### 3.2. SAST (Static Application Security Testing)
- Implement PHP security scanning
- Check for common vulnerabilities
- Validate secure coding practices

## Files to Create/Modify

1. `.github/workflows/ci.yml` - Main CI workflow
2. `.github/workflows/release.yml` - Release workflow
3. `.github/workflows/security.yml` - Security scanning workflow
4. `bin/bump-version.php` - Version bumping utility
5. `bin/generate-changelog.php` - Changelog generator
6. `bin/validate-release.php` - Release checklist validator
7. `.github/dependabot.yml` - Dependabot configuration

## Dependencies

This task builds upon:
- [x] Core implementation (Tasks 1-6)
- [x] Testing infrastructure (Task 8)
- [x] Documentation and distribution (Task 9)
- [x] Maintenance planning (Task 10)
- [x] Open source preparation (Task 11)

The release automation task utilizes the standardized namespaces and passing tests from the recent namespace standardization work (completed on November 21, 2023) as documented in task_logs.md.

## Acceptance Criteria

1. **Complete CI Workflow**
   - [x] CI workflow runs on all pull requests and commits to develop/main
   - [x] Tests execute successfully on multiple PHP versions
   - [x] Code quality checks (linting, static analysis) pass
   - [x] Code coverage reports are generated

2. **Release Automation**
   - [x] Creating a new tag automatically triggers release process
   - [x] GitHub release is created with proper release notes
   - [x] Package is automatically published to Packagist
   - [x] Release artifacts are generated and attached to release

3. **Security**
   - [x] Security scanning runs on a regular schedule
   - [x] Dependency vulnerabilities are automatically detected
   - [x] Security reports are generated

4. **Documentation**
   - [x] Release process is fully documented
   - [x] Workflow files include detailed comments explaining each step
   - [x] README.md is updated with build/security badges

5. **Error Handling and Reporting**
   - [x] Workflow failures send notifications
   - [x] Detailed error reporting is provided
   - [x] Manual intervention points are clearly identified

## Implementation Guidance

The implementation should follow best practices for GitHub Actions, ensuring idempotent, reusable, and well-commented workflows. Security should be a primary concern, particularly in handling tokens and secrets needed for publishing.

Review the existing release plan in docs/release-strategy.md to ensure alignment with the established versioning and release procedures. The GitHub Actions workflows should automate as much of this plan as possible while maintaining appropriate approval gates.

Utilize GitHub environments to separate staging from production releases, and implement approval requirements for production releases. 