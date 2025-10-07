#!/bin/bash
#
# Apply AdminControllerTestCase Pattern to All Admin Controller Tests
#
# This script applies the standardized test pattern to all Admin controller tests
# Usage: ./apply_pattern_to_all.sh

set -e

# Color codes for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Counter for statistics
TOTAL=0
SUCCESS=0
SKIPPED=0
FAILED=0

echo -e "${BLUE}=================================${NC}"
echo -e "${BLUE}Admin Controller Test Refactoring${NC}"
echo -e "${BLUE}=================================${NC}"
echo ""

# List of all Admin controllers (extracted from filenames)
CONTROLLERS=(
    "AiMetrics"
    "Aiprompts"
    "Articles"
    "BlockedIps"
    "CableCapabilities"
    "Cache"
    "Comments"
    "EmailTemplates"
    "HomepageFeeds"
    "ImageGalleries"
    "ImageGeneration"
    "Images"
    "Internationalisations"
    "Pages"
    "PageViews"
    "ProductFormFields"
    "ProductPageViews"
    "Products"
    "QueueConfigurations"
    "Reliability"
    "Settings"
    "Slugs"
    "SystemLogs"
    "Tags"
    "Users"
    "Videos"
)

echo -e "${YELLOW}Found ${#CONTROLLERS[@]} controllers to process${NC}"
echo ""

# Process each controller
for controller in "${CONTROLLERS[@]}"; do
    TOTAL=$((TOTAL + 1))
    echo -e "${BLUE}[$TOTAL/${#CONTROLLERS[@]}]${NC} Processing ${controller}Controller..."
    
    # Run the apply script
    if php apply_admin_test_pattern.php "$controller" 2>&1 | grep -q "Successfully updated"; then
        SUCCESS=$((SUCCESS + 1))
        echo -e "${GREEN}✓${NC} Successfully updated ${controller}Controller"
    elif php apply_admin_test_pattern.php "$controller" 2>&1 | grep -q "No changes needed"; then
        SKIPPED=$((SKIPPED + 1))
        echo -e "${YELLOW}⊘${NC} No changes needed for ${controller}Controller"
    else
        FAILED=$((FAILED + 1))
        echo -e "${RED}✗${NC} Failed to update ${controller}Controller"
    fi
    echo ""
done

# Print summary
echo ""
echo -e "${BLUE}=================================${NC}"
echo -e "${BLUE}Summary${NC}"
echo -e "${BLUE}=================================${NC}"
echo -e "Total controllers: ${TOTAL}"
echo -e "${GREEN}Successfully updated: ${SUCCESS}${NC}"
echo -e "${YELLOW}Skipped (no changes): ${SKIPPED}${NC}"
echo -e "${RED}Failed: ${FAILED}${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All controllers processed successfully!${NC}"
    exit 0
else
    echo -e "${RED}⚠ Some controllers failed to process${NC}"
    exit 1
fi
