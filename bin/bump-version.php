#!/usr/bin/env php
<?php
/**
 * Version Bumping Utility for Gtrends PHP SDK
 * 
 * This script updates version numbers in various files:
 * - composer.json
 * - src/Client.php (SDK_VERSION constant)
 */

if (PHP_SAPI !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

// Check for arguments
if ($argc < 2) {
    echo "Usage: php bin/bump-version.php <new-version>\n";
    echo "Example: php bin/bump-version.php 1.0.0\n";
    exit(1);
}

$newVersion = $argv[1];

// Validate version format
if (!preg_match('/^\d+\.\d+\.\d+$/', $newVersion)) {
    echo "Error: Version must be in the format X.Y.Z\n";
    exit(1);
}

// Get the root directory of the project
$rootDir = dirname(__DIR__);

// Update composer.json
$composerJsonPath = $rootDir . '/composer.json';
if (!file_exists($composerJsonPath)) {
    echo "Error: composer.json not found at {$composerJsonPath}\n";
    exit(1);
}

$composerJson = json_decode(file_get_contents($composerJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error parsing composer.json: " . json_last_error_msg() . "\n";
    exit(1);
}

$oldVersion = $composerJson['version'] ?? 'not set';
$composerJson['version'] = $newVersion;

file_put_contents(
    $composerJsonPath,
    json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

echo "Updated composer.json version from {$oldVersion} to {$newVersion}\n";

// Update SDK_VERSION constant in Client.php
$clientPhpPath = $rootDir . '/src/Client.php';
if (!file_exists($clientPhpPath)) {
    echo "Error: Client.php not found at {$clientPhpPath}\n";
    exit(1);
}

$clientPhpContent = file_get_contents($clientPhpPath);
$pattern = '/const SDK_VERSION = \'(.*?)\';/';

if (preg_match($pattern, $clientPhpContent, $matches)) {
    $oldSdkVersion = $matches[1];
    $clientPhpContent = preg_replace(
        $pattern,
        "const SDK_VERSION = '{$newVersion}';",
        $clientPhpContent
    );
    
    file_put_contents($clientPhpPath, $clientPhpContent);
    echo "Updated SDK_VERSION in Client.php from {$oldSdkVersion} to {$newVersion}\n";
} else {
    echo "Warning: Could not find SDK_VERSION constant in Client.php\n";
}

echo "Version bump to {$newVersion} completed successfully!\n";
echo "Don't forget to create a git tag: git tag -a v{$newVersion} -m 'Version {$newVersion}'\n";
exit(0); 