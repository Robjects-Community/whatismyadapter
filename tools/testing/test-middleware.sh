#!/bin/bash
#
# Middleware Unit Test Execution Script
# 
# This script runs middleware unit tests with various options and filters
#
# Usage:
#   ./test-middleware.sh [OPTIONS]
#
# Options:
#   -a, --all           Run all middleware tests
#   -c, --coverage      Run with code coverage (text format)
#   -h, --html-coverage Run with HTML coverage report
#   -f, --filter NAME   Run tests matching filter pattern
#   -v, --verbose       Verbose output
#   --help              Show this help message
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Default options
RUN_ALL=false
WITH_COVERAGE=false
HTML_COVERAGE=false
FILTER=""
VERBOSE=""

# Function to print colored output
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to show usage
show_usage() {
    cat << EOF
Middleware Unit Test Execution Script

Usage:
  ./test-middleware.sh [OPTIONS]

Options:
  -a, --all            Run all middleware tests
  -c, --coverage       Run with code coverage (text format)
  -h, --html-coverage  Run with HTML coverage report  
  -f, --filter NAME    Run tests matching filter pattern
  -v, --verbose        Verbose output
  --help               Show this help message

Examples:
  # Run all middleware tests
  ./test-middleware.sh --all

  # Run specific middleware test
  ./test-middleware.sh --filter IpBlockerMiddlewareTest

  # Run with text coverage
  ./test-middleware.sh --all --coverage

  # Run with HTML coverage report
  ./test-middleware.sh --all --html-coverage

  # Run specific test method
  ./test-middleware.sh --filter testBlocksRequestsFromBlockedIps

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -a|--all)
            RUN_ALL=true
            shift
            ;;
        -c|--coverage)
            WITH_COVERAGE=true
            shift
            ;;
        -h|--html-coverage)
            HTML_COVERAGE=true
            shift
            ;;
        -f|--filter)
            FILTER="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE="--verbose"
            shift
            ;;
        --help)
            show_usage
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# Check if Docker is running
if ! docker compose ps | grep -q "willowcms"; then
    print_error "Docker container 'willowcms' is not running"
    print_info "Please start the container first with: docker compose up -d"
    exit 1
fi

print_info "Starting middleware unit tests..."
echo ""

# Build base PHPUnit command
PHPUNIT_CMD="docker compose exec willowcms php vendor/bin/phpunit"

# Add testdox for better output
PHPUNIT_CMD="$PHPUNIT_CMD --testdox"

# Add verbose flag if specified
if [ -n "$VERBOSE" ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD $VERBOSE"
fi

# Add coverage options
if [ "$HTML_COVERAGE" = true ]; then
    print_info "Generating HTML coverage report..."
    COVERAGE_DIR="$PROJECT_ROOT/app/webroot/coverage/middleware"
    mkdir -p "$COVERAGE_DIR"
    PHPUNIT_CMD="$PHPUNIT_CMD --coverage-html $COVERAGE_DIR"
elif [ "$WITH_COVERAGE" = true ]; then
    print_info "Generating text coverage report..."
    PHPUNIT_CMD="$PHPUNIT_CMD --coverage-text"
fi

# Add filter if specified
if [ -n "$FILTER" ]; then
    print_info "Running tests matching filter: $FILTER"
    PHPUNIT_CMD="$PHPUNIT_CMD --filter $FILTER"
fi

# Determine test path
if [ "$RUN_ALL" = true ] || [ -n "$FILTER" ]; then
    TEST_PATH="tests/TestCase/Middleware/"
    print_info "Running all middleware tests..."
else
    # Interactive menu if no options specified
    print_info "Select middleware test to run:"
    echo ""
    echo "  1) IpBlockerMiddlewareTest"
    echo "  2) ApiCsrfMiddlewareTest"
    echo "  3) LogIntegrityMiddlewareTest"
    echo "  4) RateLimitMiddlewareTest"
    echo "  5) All middleware tests"
    echo "  0) Exit"
    echo ""
    read -p "Enter selection [0-5]: " selection
    
    case $selection in
        1)
            TEST_PATH="tests/TestCase/Middleware/IpBlockerMiddlewareTest.php"
            print_info "Running IpBlockerMiddlewareTest..."
            ;;
        2)
            TEST_PATH="tests/TestCase/Middleware/ApiCsrfMiddlewareTest.php"
            print_info "Running ApiCsrfMiddlewareTest..."
            ;;
        3)
            TEST_PATH="tests/TestCase/Middleware/LogIntegrityMiddlewareTest.php"
            print_info "Running LogIntegrityMiddlewareTest..."
            ;;
        4)
            TEST_PATH="tests/TestCase/Middleware/RateLimitMiddlewareTest.php"
            print_info "Running RateLimitMiddlewareTest..."
            ;;
        5)
            TEST_PATH="tests/TestCase/Middleware/"
            print_info "Running all middleware tests..."
            ;;
        0)
            print_info "Exiting..."
            exit 0
            ;;
        *)
            print_error "Invalid selection"
            exit 1
            ;;
    esac
fi

# Add test path to command
PHPUNIT_CMD="$PHPUNIT_CMD $TEST_PATH"

echo ""
print_info "Executing: $PHPUNIT_CMD"
echo ""

# Execute the tests
if eval "$PHPUNIT_CMD"; then
    echo ""
    print_success "All tests passed!"
    
    # Show coverage report location if HTML coverage was generated
    if [ "$HTML_COVERAGE" = true ]; then
        echo ""
        print_info "HTML coverage report generated at:"
        echo "  file://$COVERAGE_DIR/index.html"
        print_info "Open in browser to view detailed coverage"
    fi
    
    exit 0
else
    echo ""
    print_error "Some tests failed!"
    exit 1
fi
