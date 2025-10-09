#!/bin/bash

# AdminTheme Plugin Test Runner
# This script runs PHPUnit tests for the AdminTheme plugin

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
WILLOW_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"

echo "AdminTheme Plugin Test Runner"
echo "=============================="
echo "Plugin Directory: $SCRIPT_DIR"
echo "Willow Root: $WILLOW_ROOT"
echo ""

# Change to the plugin directory
cd "$SCRIPT_DIR"

# Check if PHPUnit is available
if ! command -v phpunit &> /dev/null; then
    echo "PHPUnit not found. Trying to use vendor/bin/phpunit from Willow root..."
    if [ -f "$WILLOW_ROOT/vendor/bin/phpunit" ]; then
        PHPUNIT="$WILLOW_ROOT/vendor/bin/phpunit"
    else
        echo "Error: PHPUnit not found. Please install PHPUnit or run 'composer install' in the Willow root directory."
        exit 1
    fi
else
    PHPUNIT="phpunit"
fi

# Run the tests
echo "Running AdminTheme plugin tests..."
echo "Command: $PHPUNIT --configuration phpunit.xml.dist"
echo ""

$PHPUNIT --configuration phpunit.xml.dist "$@"

echo ""
echo "Tests completed!"
