#!/usr/bin/env bash

# Run PHPUnit tests with filtering options as per project rules
set -euo pipefail

# Resolve project root regardless of invocation path
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

# Load environment from ./config/.env if present
if [ -f "config/.env" ]; then
    set -a
    . "config/.env"
    set +a
fi

echo "==============================================="
echo "WillowCMS PHPUnit Test Runner"
echo "==============================================="
echo ""

# Parse command line arguments
TEST_SCOPE="${1:-all}"
FILTER="${2:-}"

# Use Docker Compose per project rule. Default to the provided compose file if present, fallback otherwise
COMPOSE_FILE_DEFAULT="/Users/mikey/Docs/git-repo-loc/docker-hub/adaptercms-beta/willow/docker-compose.yml"
if [ -f "$COMPOSE_FILE_DEFAULT" ]; then
    DOCKER_COMPOSE="docker compose -f $COMPOSE_FILE_DEFAULT"
else
    DOCKER_COMPOSE="docker compose"
fi

case "$TEST_SCOPE" in
    controller|controllers)
        echo "Running Controller tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit --filter Controller tests/TestCase/Controller/
        ;;
    model|models|table|tables)
        echo "Running Model/Table tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit --filter Table tests/TestCase/Model/Table/
        ;;
    entity|entities)
        echo "Running Entity tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit --filter Entity tests/TestCase/Model/Entity/
        ;;
    job|jobs)
        echo "Running Job tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit --filter Job tests/TestCase/Job/
        ;;
    behavior|behaviors)
        echo "Running Behavior tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit tests/TestCase/Model/Behavior/
        ;;
    middleware)
        echo "Running Middleware tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit tests/TestCase/Middleware/
        ;;
    smoke)
        echo "Running Smoke tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit tests/TestCase/Smoke/
        ;;
    product|products)
        echo "Running Product-related tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit --filter "Product" tests/TestCase/
        ;;
    all)
        echo "Running ALL tests..."
        $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit tests/TestCase/
        ;;
    *)
        # Check if it looks like PHPUnit arguments (starts with -- or a test path)
        if [[ "$TEST_SCOPE" =~ ^-- ]] || [[ "$TEST_SCOPE" =~ ^tests/ ]] || [[ "$#" -gt 1 ]]; then
            echo "Running PHPUnit with custom arguments: $*"
            $DOCKER_COMPOSE exec -T willowcms php vendor/bin/phpunit "$@"
        else
            echo "Invalid scope: $TEST_SCOPE"
            echo ""
            echo "Usage: $0 [scope] [filter] | [phpunit-args...]"
            echo ""
            echo "Available scopes:"
            echo "  controller  - Run controller tests"
            echo "  model       - Run model/table tests"
            echo "  entity      - Run entity tests"
            echo "  job         - Run job tests"
            echo "  behavior    - Run behavior tests"
            echo "  middleware  - Run middleware tests"
            echo "  smoke       - Run smoke tests"
            echo "  product     - Run product-related tests"
            echo "  all         - Run all tests (default)"
            echo ""
            echo "Examples:"
            echo "  $0 controller testView"
            echo "  $0 --filter SomeTest tests/TestCase/"
            echo "  $0 tests/TestCase/Controller/"
            exit 1
        fi
        ;;
esac
