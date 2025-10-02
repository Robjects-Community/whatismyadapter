#!/bin/bash
# Thread-Safe Testing Script for WillowCMS
# Enables parallel testing across multiple Warp threads with full MVC isolation

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse arguments
COMPONENT=""
FILTER=""
THREAD_ID=""
COVERAGE=false
STOP_ON_FAILURE=false
VERBOSE=false

while [[ "$#" -gt 0 ]]; do
    case $1 in
        --component=*) COMPONENT="${1#*=}" ;;
        --filter=*) FILTER="${1#*=}" ;;
        --thread=*) THREAD_ID="${1#*=}" ;;
        --coverage) COVERAGE=true ;;
        --stop-on-failure) STOP_ON_FAILURE=true ;;
        --verbose) VERBOSE=true ;;
        -h|--help) 
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --component=<name>     Test specific component (Controller, Model, Service, etc.)"
            echo "  --filter=<name>        Test specific class or method"
            echo "  --thread=<id>          Use specific thread ID (auto-generated if not provided)"
            echo "  --coverage             Generate coverage reports"
            echo "  --stop-on-failure      Stop on first test failure"
            echo "  --verbose              Show detailed output"
            echo "  -h, --help             Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0 --component=Controller --thread=1234"
            echo "  $0 --filter=ArticlesController"
            echo "  $0 --coverage --component=Model"
            exit 0
            ;;
        *) echo "Unknown parameter: $1"; exit 1 ;;
    esac
    shift
done

# Auto-detect thread ID if not provided
if [ -z "$THREAD_ID" ]; then
    # Use last 4 digits of process ID for uniqueness, plus current timestamp
    THREAD_ID=$(echo $$ | tail -c 4)$(date +%s | tail -c 4)
fi

echo -e "${BLUE}🧪 WillowCMS Thread-Safe Testing Framework${NC}"
echo -e "${BLUE}===========================================${NC}"

# Set environment variables for isolation
export THREAD_ID=$THREAD_ID
export CAKEPHP_TEST_DATABASE_URL="mysql://root:password@mysql/willowcms_test_${THREAD_ID}"
export CACHE_PREFIX="willow_test_${THREAD_ID}_"
export TEST_TMP_DIR="/tmp/willow_test_${THREAD_ID}"
export TEST_LOGS_DIR="app/tests/logs/${THREAD_ID}"

# Create necessary directories
mkdir -p "$TEST_TMP_DIR"
mkdir -p "app/tests/logs/${THREAD_ID}"

echo -e "${GREEN}✓ Thread ID: $THREAD_ID${NC}"
echo -e "${GREEN}✓ Test database: willowcms_test_${THREAD_ID}${NC}"
echo -e "${GREEN}✓ Cache prefix: ${CACHE_PREFIX}${NC}"

# Verify Docker environment
if ! docker compose ps >/dev/null 2>&1; then
    echo -e "${RED}❌ Docker Compose environment not running!${NC}"
    echo -e "${YELLOW}💡 Run: ./run_dev_env.sh${NC}"
    exit 1
fi

# Create isolated test database if needed
echo -e "${BLUE}🏗️  Setting up isolated test database...${NC}"
docker compose exec -T mysql mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS willowcms_test_${THREAD_ID};" 2>/dev/null

# Run database migrations in test database
echo -e "${BLUE}🏗️  Running test database migrations...${NC}"
docker compose exec -T -e DATABASE_URL="mysql://root:password@mysql/willowcms_test_${THREAD_ID}" willowcms php bin/cake migrations migrate 2>/dev/null || echo -e "${YELLOW}⚠️  No migrations found or migration failed${NC}"

# Build the test command
TEST_CMD="docker compose exec -T"

# Add environment variables
TEST_CMD+=" -e THREAD_ID=${THREAD_ID}"
TEST_CMD+=" -e DATABASE_URL=${CAKEPHP_TEST_DATABASE_URL}"
TEST_CMD+=" -e CACHE_PREFIX=${CACHE_PREFIX}"
TEST_CMD+=" -e TEST_TMP_DIR=${TEST_TMP_DIR}"
TEST_CMD+=" -e TEST_LOGS_DIR=${TEST_LOGS_DIR}"

# Add the service and PHPUnit command
TEST_CMD+=" willowcms php vendor/bin/phpunit"

# Add component filter if specified
if [ -n "$COMPONENT" ]; then
    echo -e "${YELLOW}🔍 Filtering by component: $COMPONENT${NC}"
    TEST_CMD+=" tests/TestCase/$COMPONENT"
fi

# Add specific filter if specified
if [ -n "$FILTER" ]; then
    echo -e "${YELLOW}🔍 Filtering by test: $FILTER${NC}"
    TEST_CMD+=" --filter=$FILTER"
fi

# Add coverage option
if [ "$COVERAGE" = true ]; then
    echo -e "${YELLOW}📊 Generating coverage reports...${NC}"
    TEST_CMD+=" --coverage-text --coverage-html coverage_${THREAD_ID}"
fi

# Add stop on failure option
if [ "$STOP_ON_FAILURE" = true ]; then
    TEST_CMD+=" --stop-on-failure"
fi

# Add verbose output
if [ "$VERBOSE" = true ]; then
    TEST_CMD+=" --verbose"
fi

# Execute the test command
echo -e "${BLUE}🚀 Executing tests...${NC}"
if [ "$VERBOSE" = true ]; then
    echo -e "${BLUE}Command: $TEST_CMD${NC}"
fi

START_TIME=$(date +%s)
eval $TEST_CMD
TEST_EXIT_CODE=$?
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo -e "${BLUE}📊 Test Summary${NC}"
echo -e "${BLUE}===============${NC}"
echo -e "Thread ID: $THREAD_ID"
echo -e "Duration: ${DURATION}s"
echo -e "Component: ${COMPONENT:-All}"
echo -e "Filter: ${FILTER:-None}"

if [ $TEST_EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ Tests completed successfully!${NC}"
else
    echo -e "${RED}❌ Tests failed with exit code: $TEST_EXIT_CODE${NC}"
fi

# Cleanup option (ask user)
echo ""
read -p "🧹 Clean up test environment for thread $THREAD_ID? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ./tools/testing/cleanup_thread.sh "$THREAD_ID"
fi

# Return the exit status
exit $TEST_EXIT_CODE