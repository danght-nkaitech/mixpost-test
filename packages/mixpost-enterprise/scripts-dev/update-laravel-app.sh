#!/bin/bash
#
# Update the Laravel app that requires mixpost-enterprise.
# Sets inovector/mixpost-enterprise to dev-<current_branch> and manages repositories.
# Reads LARAVEL_APP_PATH from .env.
#
# Usage: ./scripts/update-laravel-app.sh [--pro-path=<path> | --prod-repos]
#   --pro-path=<path>  Add path repositories for pro-team and enterprise (dev mode)
#   --prod-repos       Use only the composer repository from inovector (prod mode)

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_DIR/.env"

# Parse arguments
REPOS_MODE=""
PRO_PATH=""
for arg in "$@"; do
    case $arg in
        --pro-path=*)
            REPOS_MODE="dev"
            PRO_PATH="${arg#*=}"
            ;;
        --prod-repos)
            REPOS_MODE="prod"
            ;;
    esac
done

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
ENTERPRISE_BRANCH=$(cd "$PROJECT_DIR" && git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "")
ENTERPRISE_DEV_VERSION="dev-$ENTERPRISE_BRANCH"

echo "Updating Laravel app composer.json..."
if [ -n "$ENTERPRISE_BRANCH" ]; then
    echo "  inovector/mixpost-enterprise -> $ENTERPRISE_DEV_VERSION"
fi
if [ "$REPOS_MODE" = "dev" ]; then
    echo "  repositories -> path ($PRO_PATH, $PROJECT_DIR)"
elif [ "$REPOS_MODE" = "prod" ]; then
    echo "  repositories -> composer (packages.inovector.com)"
fi

php -r '
$composerFile = $argv[1];
$enterpriseVersion = $argv[2];
$reposMode = $argv[3];
$proPath = $argv[4];
$enterprisePath = $argv[5];

$composer = json_decode(file_get_contents($composerFile), true);

if ($composer === null) {
    echo "Error: Failed to parse Laravel app composer.json\n";
    exit(1);
}

if (!empty($enterpriseVersion)) {
    $composer["require"]["inovector/mixpost-enterprise"] = $enterpriseVersion;
}

if ($reposMode === "dev") {
    $composer["repositories"] = [
        ["type" => "path", "url" => $proPath],
        ["type" => "path", "url" => $enterprisePath],
    ];
} elseif ($reposMode === "prod") {
    $composer["repositories"] = [
        ["type" => "path", "url" => $enterprisePath],
        ["type" => "composer", "url" => "https://packages.inovector.com"],
    ];
}

file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "Laravel app composer.json updated successfully.\n";
' "$LARAVEL_APP_PATH/composer.json" "$ENTERPRISE_DEV_VERSION" "$REPOS_MODE" "$PRO_PATH" "$PROJECT_DIR"

echo "Removing composer.lock and vendor/..."
rm -f "$LARAVEL_APP_PATH/composer.lock"
rm -rf "$LARAVEL_APP_PATH/vendor"

echo "Running composer install in Laravel app..."
composer install --working-dir="$LARAVEL_APP_PATH"

echo "Clearing Laravel app cache..."
php "$LARAVEL_APP_PATH/artisan" optimize:clear
