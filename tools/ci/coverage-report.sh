#!/usr/bin/env bash
#
# Coverage Report Generator
# Generates code coverage reports without running full CI
#
# Usage: ./tools/ci/coverage-report.sh [--text|--html|--both]
#

set -e

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Default to HTML report
REPORT_TYPE="${1:---html}"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Code Coverage Report Generator${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

cd "$PROJECT_ROOT"

# Check if services are running
if ! docker compose ps | grep -q "willowcms"; then
    echo -e "${YELLOW}Starting Docker Compose services...${NC}"
    docker compose up -d
    sleep 10
fi

# Verify Xdebug
echo -e "${YELLOW}Verifying Xdebug is configured...${NC}"
if ! docker compose exec -T willowcms php -m | grep -q xdebug; then
    echo -e "${RED}Error: Xdebug is not loaded${NC}"
    echo "Please ensure Xdebug is installed and configured in php.ini"
    exit 1
fi
echo -e "${GREEN}✓ Xdebug is ready${NC}"

# Generate coverage based on type
case "$REPORT_TYPE" in
    --text)
        echo -e "${YELLOW}Generating text coverage report...${NC}"
        docker compose exec -T willowcms php vendor/bin/phpunit --coverage-text
        ;;
    --html)
        echo -e "${YELLOW}Generating HTML coverage report...${NC}"
        docker compose exec -T willowcms php vendor/bin/phpunit \
            --coverage-html /var/www/html/webroot/coverage \
            --testdox
        echo ""
        echo -e "${GREEN}✓ HTML report generated!${NC}"
        echo -e "  View at: ${BLUE}http://localhost:8080/coverage/${NC}"
        ;;
    --both)
        echo -e "${YELLOW}Generating HTML and text coverage reports...${NC}"
        docker compose exec -T willowcms php vendor/bin/phpunit \
            --coverage-html /var/www/html/webroot/coverage \
            --coverage-text \
            --testdox
        echo ""
        echo -e "${GREEN}✓ Reports generated!${NC}"
        echo -e "  View HTML at: ${BLUE}http://localhost:8080/coverage/${NC}"
        ;;
    *)
        echo "Usage: $0 [--text|--html|--both]"
        exit 1
        ;;
esac

echo ""
