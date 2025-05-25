#!/usr/bin/env php
<?php
/**
 * Changelog Generator for Gtrends PHP SDK
 * 
 * This script generates a CHANGELOG.md entry from Git commit messages,
 * organized into categories according to the Keep a Changelog format.
 */

if (PHP_SAPI !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

// Check for arguments
if ($argc < 3) {
    echo "Usage: php bin/generate-changelog.php <from-tag> <to-tag> [--update]\n";
    echo "Example: php bin/generate-changelog.php v0.1.0 v1.0.0 --update\n";
    exit(1);
}

$fromTag = $argv[1];
$toTag = $argv[2];
$updateChangelog = in_array('--update', $argv);

// Validate tag arguments
if (!preg_match('/^v\d+\.\d+\.\d+$/', $fromTag) || !preg_match('/^v\d+\.\d+\.\d+$/', $toTag)) {
    echo "Error: Tags must be in the format vX.Y.Z\n";
    exit(1);
}

// Get commit logs
$command = "git log {$fromTag}..{$toTag} --pretty=format:'%h %s' --no-merges";
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Error executing git log command. Make sure both tags exist.\n";
    exit(1);
}

// Categories for organizing changes
$categories = [
    'Added' => [],
    'Changed' => [],
    'Deprecated' => [],
    'Removed' => [],
    'Fixed' => [],
    'Security' => [],
];

// Parse commit messages into categories
foreach ($output as $line) {
    $hash = substr($line, 0, 7);
    $message = substr($line, 8);
    
    if (preg_match('/^(feat|add|feature)/i', $message)) {
        $categories['Added'][] = "- {$message} ({$hash})";
    } elseif (preg_match('/^(fix|bug|hotfix)/i', $message)) {
        $categories['Fixed'][] = "- {$message} ({$hash})";
    } elseif (preg_match('/^(change|refactor|improve)/i', $message)) {
        $categories['Changed'][] = "- {$message} ({$hash})";
    } elseif (preg_match('/^(deprecate)/i', $message)) {
        $categories['Deprecated'][] = "- {$message} ({$hash})";
    } elseif (preg_match('/^(remove)/i', $message)) {
        $categories['Removed'][] = "- {$message} ({$hash})";
    } elseif (preg_match('/^(security)/i', $message)) {
        $categories['Security'][] = "- {$message} ({$hash})";
    } else {
        // Default to Added category if no match
        $categories['Changed'][] = "- {$message} ({$hash})";
    }
}

// Generate changelog content
$version = substr($toTag, 1); // Remove the 'v' prefix
$date = date('Y-m-d');
$changelog = "## [{$version}] - {$date}\n\n";

foreach ($categories as $category => $changes) {
    if (empty($changes)) {
        continue;
    }
    
    $changelog .= "### {$category}\n";
    $changelog .= implode("\n", $changes) . "\n\n";
}

if ($updateChangelog) {
    // Update CHANGELOG.md
    $changelogPath = dirname(__DIR__) . '/CHANGELOG.md';
    if (!file_exists($changelogPath)) {
        $header = "# Changelog\n\n";
        $header .= "All notable changes to this project will be documented in this file.\n\n";
        $header .= "The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),\n";
        $header .= "and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).\n\n";
        file_put_contents($changelogPath, $header);
    }
    
    $existingChangelog = file_get_contents($changelogPath);
    $pattern = '/# Changelog.*?\n\n/s';
    if (preg_match($pattern, $existingChangelog, $matches)) {
        $newChangelog = $matches[0] . $changelog . substr($existingChangelog, strlen($matches[0]));
        file_put_contents($changelogPath, $newChangelog);
        echo "Updated CHANGELOG.md with new entry for version {$version}.\n";
    } else {
        echo "Error: Could not find expected format in CHANGELOG.md\n";
        exit(1);
    }
} else {
    // Just print to stdout
    echo $changelog;
}

exit(0); 