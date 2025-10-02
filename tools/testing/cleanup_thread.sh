#!/bin/bash
# Thread Cleanup Script for WillowCMS Testing
# Removes all thread-specific testing resources

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Thread ID to clean up
THREAD_ID=$1

if [ -z "$THREAD_ID" ]; then
    echo -e "${RED}❌ Error: Thread ID must be provided${NC}"
    echo "Usage: $0 <thread_id>"
    echo ""
    echo "Example: $0 12345678"
    exit 1
fi

echo -e "${BLUE}🧹 WillowCMS Thread Cleanup${NC}"
echo -e "${BLUE}===========================${NC}"
echo -e "${YELLOW}Thread ID: $THREAD_ID${NC}"
echo ""

# Confirmation prompt
read -p "⚠️  This will permanently delete all resources for thread $THREAD_ID. Continue? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}🛑 Cleanup cancelled${NC}"
    exit 0
fi

echo -e "${BLUE}🗄️  Dropping test database...${NC}"
# Drop the test database
if docker compose exec -T mysql mysql -u root -ppassword -e "DROP DATABASE IF EXISTS willowcms_test_${THREAD_ID};" 2>/dev/null; then
    echo -e "${GREEN}✓ Database willowcms_test_${THREAD_ID} dropped${NC}"
else
    echo -e "${YELLOW}⚠️  Database may not exist or couldn't be dropped${NC}"
fi

echo -e "${BLUE}📁 Cleaning temporary files...${NC}"
# Clean up temporary files
if [ -d "/tmp/willow_test_${THREAD_ID}" ]; then
    rm -rf "/tmp/willow_test_${THREAD_ID}"
    echo -e "${GREEN}✓ Temporary directory /tmp/willow_test_${THREAD_ID} removed${NC}"
else
    echo -e "${YELLOW}⚠️  Temporary directory doesn't exist${NC}"
fi

if [ -d "app/tests/logs/${THREAD_ID}" ]; then
    rm -rf "app/tests/logs/${THREAD_ID}"
    echo -e "${GREEN}✓ Test logs directory app/tests/logs/${THREAD_ID} removed${NC}"
else
    echo -e "${YELLOW}⚠️  Test logs directory doesn't exist${NC}"
fi

echo -e "${BLUE}🔴 Clearing Redis cache...${NC}"
# Clear Redis cache with thread-specific prefix
REDIS_PATTERN="willow_test_${THREAD_ID}_*"
REDIS_KEYS=$(docker compose exec -T redis redis-cli --scan --pattern "$REDIS_PATTERN" 2>/dev/null || true)

if [ -n "$REDIS_KEYS" ]; then
    echo "$REDIS_KEYS" | while read -r key; do
        if [ -n "$key" ]; then
            docker compose exec -T redis redis-cli del "$key" >/dev/null 2>&1
        fi
    done
    echo -e "${GREEN}✓ Redis cache entries with prefix willow_test_${THREAD_ID}_ cleared${NC}"
else
    echo -e "${YELLOW}⚠️  No Redis cache entries found for thread${NC}"
fi

echo -e "${BLUE}📊 Removing coverage reports...${NC}"
# Clean up coverage reports
if [ -d "coverage_${THREAD_ID}" ]; then
    rm -rf "coverage_${THREAD_ID}"
    echo -e "${GREEN}✓ Coverage reports coverage_${THREAD_ID} removed${NC}"
else
    echo -e "${YELLOW}⚠️  No coverage reports found for thread${NC}"
fi

echo ""
echo -e "${GREEN}✅ Cleanup complete for thread ID: $THREAD_ID${NC}"
echo -e "${BLUE}📋 Summary:${NC}"
echo -e "   • Database: willowcms_test_${THREAD_ID} dropped"
echo -e "   • Temporary files: /tmp/willow_test_${THREAD_ID} removed"
echo -e "   • Test logs: app/tests/logs/${THREAD_ID} removed"
echo -e "   • Redis cache: willow_test_${THREAD_ID}_* cleared"
echo -e "   • Coverage reports: coverage_${THREAD_ID} removed"
echo ""
echo -e "${GREEN}🎉 Thread environment fully cleaned!${NC}"