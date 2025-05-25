#!/usr/bin/env php
<?php

/**
 * Release Checklist Validator for Gtrends PHP SDK
 *
 * This script validates that all release requirements are met:
 * - All tests pass
 * - Documentation is up-to-date
 * - Version numbers are consistent
 * - CHANGELOG is properly updated
 */

if (PHP_SAPI !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

// Optional version argument
$version = null;
if ($argc > 1) {
    $version = $argv[1];
    if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
        echo "Error: Version must be in the format X.Y.Z\n";
        exit(1);
    }
}

// Get the root directory of the project
$rootDir = dirname(__DIR__);

// Check if we're in a clean Git state
exec('git status --porcelain', $gitStatus, $returnCode);
if (!empty($gitStatus)) {
    echo "❌ Git working directory is not clean. Commit or stash changes before releasing.\n";
    exit(1);
}

echo "✅ Git working directory is clean.\n";

// Run tests
echo "Running tests...\n";
exec('vendor/bin/phpunit', $testOutput, $testReturnCode);
if ($testReturnCode !== 0) {
    echo "❌ Tests are failing. Please fix them before releasing.\n";
    exit(1);
}

echo "✅ All tests are passing.\n";

// Check PHP code style
echo "Checking code style...\n";
exec('vendor/bin/phpcs --standard=PSR12 src', $styleOutput, $styleReturnCode);
if ($styleReturnCode !== 0) {
    echo "❌ Code style issues detected. Please fix them before releasing.\n";
    foreach ($styleOutput as $line) {
        echo "   {$line}\n";
    }
    exit(1);
}

echo "✅ Code style is compliant with PSR-12.\n";

// Check static analysis
echo "Running static analysis...\n";
exec('vendor/bin/phpstan analyse src tests --level=5', $staticOutput, $staticReturnCode);
if ($staticReturnCode !== 0) {
    echo "❌ Static analysis issues detected. Please fix them before releasing.\n";
    foreach ($staticOutput as $line) {
        echo "   {$line}\n";
    }
    exit(1);
}

echo "✅ Static analysis passed.\n";

// Check for version consistency if version provided
if ($version !== null) {
    // Check composer.json
    $composerJsonPath = $rootDir . '/composer.json';
    $composerJson = json_decode(file_get_contents($composerJsonPath), true);
    $composerVersion = $composerJson['version'] ?? null;

    if ($composerVersion !== $version) {
        echo "❌ Version mismatch in composer.json: {$composerVersion} (expected {$version}).\n";
        echo "   Run 'php bin/bump-version.php {$version}' to update.\n";
        exit(1);
    }

    echo "✅ composer.json version is {$version}.\n";

    // Check Client.php SDK_VERSION
    $clientPhpPath = $rootDir . '/src/Client.php';
    $clientPhpContent = file_get_contents($clientPhpPath);
    $pattern = '/const SDK_VERSION = \'(.*?)\';/';

    if (preg_match($pattern, $clientPhpContent, $matches)) {
        $sdkVersion = $matches[1];
        if ($sdkVersion !== $version) {
            echo "❌ Version mismatch in Client.php SDK_VERSION: {$sdkVersion} (expected {$version}).\n";
            echo "   Run 'php bin/bump-version.php {$version}' to update.\n";
            exit(1);
        }

        echo "✅ Client.php SDK_VERSION is {$version}.\n";
    } else {
        echo "❌ Could not find SDK_VERSION constant in Client.php.\n";
        exit(1);
    }

    // Check CHANGELOG.md
    $changelogPath = $rootDir . '/CHANGELOG.md';
    $changelogContent = file_exists($changelogPath) ? file_get_contents($changelogPath) : '';

    if (!preg_match('/## \[' . preg_quote($version) . '\]/', $changelogContent)) {
        echo "❌ CHANGELOG.md does not contain an entry for version {$version}.\n";
        echo "   Run 'php bin/generate-changelog.php <previous-version> v{$version} --update' to update.\n";
        exit(1);
    }

    echo "✅ CHANGELOG.md contains an entry for version {$version}.\n";
}

// Check documentation exists and is up-to-date
$docsDir = $rootDir . '/docs';
if (!is_dir($docsDir)) {
    echo "❌ Documentation directory 'docs' does not exist.\n";
    exit(1);
}

$requiredDocs = [
    'api-reference.md',
    'configuration.md',
    'laravel-integration.md',
    'release-strategy.md'
];

foreach ($requiredDocs as $doc) {
    $docPath = $docsDir . '/' . $doc;
    if (!file_exists($docPath)) {
        echo "❌ Required documentation file '{$doc}' is missing.\n";
        exit(1);
    }
}

echo "✅ All required documentation files exist.\n";

// Check for README.md
$readmePath = $rootDir . '/README.md';
if (!file_exists($readmePath)) {
    echo "❌ README.md file is missing.\n";
    exit(1);
}

echo "✅ README.md exists.\n";

// Check for LICENSE
$licensePath = $rootDir . '/LICENSE';
if (!file_exists($licensePath)) {
    echo "❌ LICENSE file is missing.\n";
    exit(1);
}

echo "✅ LICENSE file exists.\n";

// Final success message
echo "\n🎉 All release checks passed! The project is ready for release.\n";

if ($version !== null) {
    echo "\nTo create a new release:\n";
    echo "1. Run: git tag -a v{$version} -m 'Version {$version}'\n";
    echo "2. Run: git push origin v{$version}\n";
    echo "3. Create a GitHub release at: https://github.com/yourusername/gtrends-cli-sdk/releases/new?tag=v{$version}\n";
}

exit(0);
