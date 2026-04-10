#!/bin/bash
#
# Revert mixpost-pro-team back to the production release.
# Removes path repositories and restores the version to ^4.0 in composer.json.
#
# Usage: ./scripts/use-prod-pro.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
COMPOSER_FILE="$PROJECT_DIR/composer.json"

echo "Reverting composer.json to original state..."

# Use PHP to revert composer.json
php -r '
$composerFile = $argv[1];

$composer = json_decode(file_get_contents($composerFile), true);

if ($composer === null) {
    echo "Error: Failed to parse composer.json\n";
    exit(1);
}

// Revert version to ^4.8
$composer["require"]["inovector/mixpost-pro-team"] = "^4.8";

// Replace repositories with only the composer repository
$composer["repositories"] = [
    [
        "type" => "composer",
        "url" => "https://packages.inovector.com",
    ],
];

file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "composer.json reverted successfully.\n";
' "$COMPOSER_FILE"

# Clean and reinstall dependencies
echo "Removing composer.lock and vendor/..."
rm -f "$PROJECT_DIR/composer.lock"
rm -rf "$PROJECT_DIR/vendor"

echo "Running composer install..."
composer install --working-dir="$PROJECT_DIR"

# Update the Laravel app
echo ""
"$SCRIPT_DIR/update-laravel-app.sh" --prod-repos
