#!/usr/bin/env bash
#
# Code Quality Checker
# Runs PHPStan and PHP CodeSniffer checks
#
# Usage: ./tools/ci/code-quality.sh [--fix]
#

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Check for --fix flag
FIX_MODE=false
if [[ "$1" == "--fix" ]]; then
    FIX_MODE=true
fi

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Code Quality Checker${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

cd "$PROJECT_ROOT"

# Check if services are running
if ! docker compose ps | grep -q "willowcms"; then
    echo -e "${YELLOW}Starting Docker Compose services...${NC}"
    docker compose up -d
    sleep 10
fi

# PHPStan Analysis
echo -e "${YELLOW}[1/2] Running PHPStan static analysis (Level 5)...${NC}"
echo ""
if docker compose exec -T willowcms composer stan; then
    echo ""
    echo -e "${GREEN}✓ PHPStan: No issues found${NC}"
    PHPSTAN_STATUS=0
else
    echo ""
    echo -e "${RED}✗ PHPStan: Issues detected${NC}"
    PHPSTAN_STATUS=1
fi

echo ""

# PHP CodeSniffer
if [ "$FIX_MODE" = true ]; then
    echo -e "${YELLOW}[2/2] Auto-fixing code standard violations...${NC}"
    echo ""
    if docker compose exec -T willowcms composer cs-fix; then
        echo ""
        echo -e "${GREEN}✓ PHPCS: Violations auto-fixed${NC}"
        PHPCS_STATUS=0
    else
        echo ""
        echo -e "${YELLOW}⚠ PHPCS: Some violations could not be auto-fixed${NC}"
        PHPCS_STATUS=1
    fi
else
    echo -e "${YELLOW}[2/2] Checking code standards...${NC}"
    echo ""
    if docker compose exec -T willowcms composer cs-check; then
        echo ""
        echo -e "${GREEN}✓ PHPCS: No violations found${NC}"
        PHPCS_STATUS=0
    else
        echo ""
        echo -e "${RED}✗ PHPCS: Violations detected${NC}"
        echo -e "${YELLOW}Tip: Run with --fix flag to auto-fix violations:${NC}"
        echo -e "  ./tools/ci/code-quality.sh --fix"
        PHPCS_STATUS=1
    fi
fi

# Summary
echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Code Quality Summary${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

if [ $PHPSTAN_STATUS -eq 0 ] && [ $PHPCS_STATUS -eq 0 ]; then
    echo -e "${GREEN}✓ All quality checks passed!${NC}"
    echo ""
    exit 0
else
    if [ $PHPSTAN_STATUS -ne 0 ]; then
        echo -e "${RED}✗ PHPStan found issues${NC}"
    fi
    if [ $PHPCS_STATUS -ne 0 ]; then
        echo -e "${RED}✗ PHPCS found violations${NC}"
    fi
    echo ""
    echo -e "${YELLOW}Please fix the issues above before committing.${NC}"
    echo ""
    exit 1
fi
