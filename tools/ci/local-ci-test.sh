#!/usr/bin/env bash
#
# Local CI Test Simulation
# Simulates the full CI/CD pipeline locally for testing before pushing
#
# Usage: ./tools/ci/local-ci-test.sh
#

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Willow CMS - Local CI Test Suite${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

cd "$PROJECT_ROOT"

# Check if docker compose is available
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed or not in PATH${NC}"
    exit 1
fi

# Check if services are running
echo -e "${YELLOW}[1/7] Checking Docker services...${NC}"
if ! docker compose ps | grep -q "willowcms"; then
    echo -e "${YELLOW}Starting Docker Compose services...${NC}"
    docker compose up -d
    
    echo -e "${YELLOW}Waiting for services to be healthy...${NC}"
    sleep 10
fi

# Check service health
echo -e "${YELLOW}[2/7] Verifying service health...${NC}"
docker compose ps

# Wait for MySQL
echo -e "${YELLOW}Waiting for MySQL...${NC}"
timeout=60
until docker compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null || [ $timeout -eq 0 ]; do
    sleep 2
    ((timeout-=2))
done

if [ $timeout -eq 0 ]; then
    echo -e "${RED}MySQL failed to start${NC}"
    exit 1
fi
echo -e "${GREEN}✓ MySQL is ready${NC}"

# Wait for Redis
echo -e "${YELLOW}Waiting for Redis...${NC}"
timeout=30
until docker compose exec -T redis redis-cli ping 2>/dev/null | grep -q PONG || [ $timeout -eq 0 ]; do
    sleep 2
    ((timeout-=2))
done

if [ $timeout -eq 0 ]; then
    echo -e "${RED}Redis failed to start${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Redis is ready${NC}"

# Run database migrations
echo -e "${YELLOW}[3/7] Running database migrations...${NC}"
if docker compose exec -T willowcms bin/cake migrations migrate; then
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi

# Verify Xdebug for coverage
echo -e "${YELLOW}[4/7] Verifying Xdebug configuration...${NC}"
if docker compose exec -T willowcms php -m | grep -q xdebug; then
    echo -e "${GREEN}✓ Xdebug is loaded${NC}"
else
    echo -e "${RED}✗ Xdebug is not loaded${NC}"
    exit 1
fi

# Run PHPUnit tests with coverage
echo -e "${YELLOW}[5/7] Running PHPUnit tests with coverage...${NC}"
if docker compose exec -T willowcms php vendor/bin/phpunit \
    --coverage-html /var/www/html/webroot/coverage \
    --coverage-text \
    --testdox; then
    echo -e "${GREEN}✓ All tests passed${NC}"
else
    echo -e "${RED}✗ Tests failed${NC}"
    exit 1
fi

# Run PHPStan
echo -e "${YELLOW}[6/7] Running PHPStan static analysis...${NC}"
if docker compose exec -T willowcms composer stan; then
    echo -e "${GREEN}✓ PHPStan analysis passed${NC}"
else
    echo -e "${YELLOW}⚠ PHPStan found issues (warnings only)${NC}"
fi

# Run PHPCS
echo -e "${YELLOW}[7/7] Running PHP CodeSniffer...${NC}"
if docker compose exec -T willowcms composer cs-check; then
    echo -e "${GREEN}✓ Code standards check passed${NC}"
else
    echo -e "${YELLOW}⚠ PHPCS found violations (warnings only)${NC}"
fi

# Summary
echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}  ✓ CI Test Suite Complete!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${GREEN}Coverage report available at:${NC}"
echo -e "  http://localhost:8080/coverage/"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Review coverage report in browser"
echo "  2. Fix any PHPStan or PHPCS issues"
echo "  3. Commit and push your changes"
echo ""
