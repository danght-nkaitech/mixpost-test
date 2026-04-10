#!/bin/bash
#
# Update the Laravel app that requires mixpost-pro-team.
# Sets inovector/mixpost-pro-team to dev-<current_branch>.
# Reads LARAVEL_APP_PATH from .env.
#
# Usage: ./scripts-dev/update-laravel-app.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_DIR/.env"

# Read LARAVEL_APP_PATH from .env
LARAVEL_APP_PATH=""
if [ -f "$ENV_FILE" ]; then
    LARAVEL_APP_PATH=$(grep -E '^LARAVEL_APP_PATH=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '[:space:]')
fi

if [ -z "$LARAVEL_APP_PATH" ]; then
    echo "LARAVEL_APP_PATH is not set in .env file, skipping Laravel app update."
    exit 0
fi

if [ ! -d "$LARAVEL_APP_PATH" ]; then
    echo "Warning: LARAVEL_APP_PATH directory does not exist: $LARAVEL_APP_PATH"
    exit 1
fi

# Get current branch of the enterprise package
PACKAGE_BRANCH=$(cd "$PROJECT_DIR" && git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "")
PACKAGE_DEV_VERSION="dev-$PACKAGE_BRANCH"

echo "Updating Laravel app composer.json..."
if [ -n "$PACKAGE_BRANCH" ]; then
    echo "  inovector/mixpost-pro-team -> $PACKAGE_DEV_VERSION"
fi

php -r '
$composerFile = $argv[1];
$packageVersion = $argv[2];

$composer = json_decode(file_get_contents($composerFile), true);

if ($composer === null) {
    echo "Error: Failed to parse Laravel app composer.json\n";
    exit(1);
}

if (!empty($packageVersion)) {
    $composer["require"]["inovector/mixpost-pro-team"] = $packageVersion;
}

file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "Laravel app composer.json updated successfully.\n";
' "$LARAVEL_APP_PATH/composer.json" "$PACKAGE_DEV_VERSION"

echo "Removing composer.lock and vendor/..."
rm -f "$LARAVEL_APP_PATH/composer.lock"
rm -rf "$LARAVEL_APP_PATH/vendor"

echo "Running composer install in Laravel app..."
composer install --working-dir="$LARAVEL_APP_PATH"

echo "Clearing Laravel app cache..."
php "$LARAVEL_APP_PATH/artisan" optimize:clear
