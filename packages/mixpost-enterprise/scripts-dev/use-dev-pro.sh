#!/bin/bash
#
# Switch mixpost-pro-team to a local dev version.
# Adds a path repository and sets the version to dev-<branch> in composer.json.
# Reads MIXPOST_PRO_PATH from .env. Optionally pass branch name as argument.
#
# Usage: ./scripts/use-dev-pro.sh [branch]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
COMPOSER_FILE="$PROJECT_DIR/composer.json"
ENV_FILE="$PROJECT_DIR/.env"

# Check .env file exists
if [ ! -f "$ENV_FILE" ]; then
    echo "Error: .env file not found at $ENV_FILE"
    exit 1
fi

# Read MIXPOST_PRO_PATH from .env
MIXPOST_PRO_PATH=$(grep -E '^MIXPOST_PRO_PATH=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '[:space:]')

if [ -z "$MIXPOST_PRO_PATH" ]; then
    echo "Error: MIXPOST_PRO_PATH is not set in .env file"
    echo "Add MIXPOST_PRO_PATH=/path/to/mixpost-pro-team to your .env file"
    exit 1
fi

# Resolve full path for git operations
if [[ "$MIXPOST_PRO_PATH" == /* ]]; then
    FULL_PRO_PATH="$MIXPOST_PRO_PATH"
else
    FULL_PRO_PATH="$PROJECT_DIR/$MIXPOST_PRO_PATH"
fi

if [ ! -d "$FULL_PRO_PATH" ]; then
    echo "Error: Directory does not exist: $FULL_PRO_PATH"
    exit 1
fi

# Get current branch from the pro repo
CURRENT_BRANCH=$(cd "$FULL_PRO_PATH" && git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "")

# Determine branch: use argument, prompt user, or use current branch
if [ -n "$1" ]; then
    BRANCH="$1"
elif [ -n "$CURRENT_BRANCH" ]; then
    echo "Current branch: $CURRENT_BRANCH"
    printf "Branch (press Enter to use current): "
    read USER_INPUT
    BRANCH="${USER_INPUT:-$CURRENT_BRANCH}"
else
    printf "Branch: "
    read BRANCH
fi

if [ -z "$BRANCH" ]; then
    echo "Error: No branch specified"
    exit 1
fi

DEV_VERSION="dev-$BRANCH"

echo ""
echo "Path: $MIXPOST_PRO_PATH"
echo "Version: $DEV_VERSION"
echo ""

# Use PHP to modify composer.json
php -r '
$composerFile = $argv[1];
$proPath = $argv[2];
$devVersion = $argv[3];

$composer = json_decode(file_get_contents($composerFile), true);

if ($composer === null) {
    echo "Error: Failed to parse composer.json\n";
    exit(1);
}

// Update version to dev
$composer["require"]["inovector/mixpost-pro-team"] = $devVersion;

// Replace repositories with only the path repository
$composer["repositories"] = [
    [
        "type" => "path",
        "url" => $proPath,
    ],
];

file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "composer.json updated successfully.\n";
' "$COMPOSER_FILE" "$MIXPOST_PRO_PATH" "$DEV_VERSION"

# Clean and reinstall dependencies
echo "Removing composer.lock and vendor/..."
rm -f "$PROJECT_DIR/composer.lock"
rm -rf "$PROJECT_DIR/vendor"

echo "Running composer install..."
composer install --working-dir="$PROJECT_DIR"

# Update the Laravel app
echo ""
"$SCRIPT_DIR/update-laravel-app.sh" --pro-path="$MIXPOST_PRO_PATH"