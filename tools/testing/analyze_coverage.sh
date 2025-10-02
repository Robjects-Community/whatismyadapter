#!/bin/bash
# Test Coverage Analysis Script for WillowCMS
# Identifies gaps in test coverage across MVC components

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 WillowCMS Test Coverage Analysis${NC}"
echo -e "${BLUE}===================================${NC}"
echo ""

# Change to app directory for proper path resolution
cd app

# Count existing tests by component
echo -e "${PURPLE}📊 Current Test Statistics:${NC}"
echo -e "${BLUE}=============================${NC}"

CONTROLLER_TESTS=$(find tests/TestCase/Controller -name "*.php" 2>/dev/null | wc -l)
MODEL_TESTS=$(find tests/TestCase/Model -name "*.php" 2>/dev/null | wc -l)
SERVICE_TESTS=$(find tests/TestCase/Service -name "*.php" 2>/dev/null | wc -l)
MIDDLEWARE_TESTS=$(find tests/TestCase/Middleware -name "*.php" 2>/dev/null | wc -l)
INTEGRATION_TESTS=$(find tests/TestCase/Integration -name "*.php" 2>/dev/null | wc -l)
OTHER_TESTS=$(find tests/TestCase -maxdepth 1 -name "*.php" 2>/dev/null | wc -l)

TOTAL_TESTS=$(find tests/TestCase -name "*.php" 2>/dev/null | wc -l)

echo -e "${GREEN}✓ Controller Tests: $CONTROLLER_TESTS${NC}"
echo -e "${GREEN}✓ Model Tests: $MODEL_TESTS${NC}"
echo -e "${GREEN}✓ Service Tests: $SERVICE_TESTS${NC}"
echo -e "${GREEN}✓ Middleware Tests: $MIDDLEWARE_TESTS${NC}"
echo -e "${GREEN}✓ Integration Tests: $INTEGRATION_TESTS${NC}"
echo -e "${GREEN}✓ Other Tests: $OTHER_TESTS${NC}"
echo -e "${BLUE}📈 Total Test Files: $TOTAL_TESTS${NC}"
echo ""

# Analyze source code structure
echo -e "${PURPLE}🏗️  Source Code Analysis:${NC}"
echo -e "${BLUE}==========================${NC}"

TOTAL_CONTROLLERS=$(find src/Controller -name "*.php" 2>/dev/null | wc -l)
ADMIN_CONTROLLERS=$(find src/Controller/Admin -name "*.php" 2>/dev/null | wc -l)
API_CONTROLLERS=$(find src/Controller/Api -name "*.php" 2>/dev/null | wc -l)
PUBLIC_CONTROLLERS=$((TOTAL_CONTROLLERS - ADMIN_CONTROLLERS - API_CONTROLLERS))

TOTAL_MODELS=$(find src/Model -name "*.php" 2>/dev/null | wc -l)
TOTAL_SERVICES=$(find src/Service -name "*.php" 2>/dev/null | wc -l)
TOTAL_MIDDLEWARE=$(find src/Middleware -name "*.php" 2>/dev/null | wc -l)

echo -e "${YELLOW}📁 Controllers: $TOTAL_CONTROLLERS (Admin: $ADMIN_CONTROLLERS, API: $API_CONTROLLERS, Public: $PUBLIC_CONTROLLERS)${NC}"
echo -e "${YELLOW}📁 Models: $TOTAL_MODELS${NC}"
echo -e "${YELLOW}📁 Services: $TOTAL_SERVICES${NC}"
echo -e "${YELLOW}📁 Middleware: $TOTAL_MIDDLEWARE${NC}"
echo ""

# Calculate coverage percentages
echo -e "${PURPLE}📈 Test Coverage Estimation:${NC}"
echo -e "${BLUE}=============================${NC}"

if [ $TOTAL_CONTROLLERS -gt 0 ]; then
    CONTROLLER_COVERAGE=$((CONTROLLER_TESTS * 100 / TOTAL_CONTROLLERS))
    echo -e "${GREEN}📊 Controller Coverage: ~${CONTROLLER_COVERAGE}% (${CONTROLLER_TESTS}/${TOTAL_CONTROLLERS})${NC}"
else
    echo -e "${YELLOW}📊 Controller Coverage: No controllers found${NC}"
fi

if [ $TOTAL_MODELS -gt 0 ]; then
    MODEL_COVERAGE=$((MODEL_TESTS * 100 / TOTAL_MODELS))
    echo -e "${GREEN}📊 Model Coverage: ~${MODEL_COVERAGE}% (${MODEL_TESTS}/${TOTAL_MODELS})${NC}"
else
    echo -e "${YELLOW}📊 Model Coverage: No models found${NC}"
fi

if [ $TOTAL_SERVICES -gt 0 ]; then
    SERVICE_COVERAGE=$((SERVICE_TESTS * 100 / TOTAL_SERVICES))
    echo -e "${GREEN}📊 Service Coverage: ~${SERVICE_COVERAGE}% (${SERVICE_TESTS}/${TOTAL_SERVICES})${NC}"
else
    echo -e "${YELLOW}📊 Service Coverage: No services found${NC}"
fi

if [ $TOTAL_MIDDLEWARE -gt 0 ]; then
    MIDDLEWARE_COVERAGE=$((MIDDLEWARE_TESTS * 100 / TOTAL_MIDDLEWARE))
    echo -e "${GREEN}📊 Middleware Coverage: ~${MIDDLEWARE_COVERAGE}% (${MIDDLEWARE_TESTS}/${TOTAL_MIDDLEWARE})${NC}"
else
    echo -e "${YELLOW}📊 Middleware Coverage: No middleware found${NC}"
fi

echo ""

# Identify missing controller tests
echo -e "${PURPLE}🔍 Missing Controller Tests:${NC}"
echo -e "${BLUE}=============================${NC}"

echo -e "${YELLOW}🎯 Public Controllers without tests:${NC}"
find src/Controller -maxdepth 1 -name "*Controller.php" | while read controller; do
    controller_name=$(basename "$controller" .php)
    if [ ! -f "tests/TestCase/Controller/${controller_name}Test.php" ]; then
        echo -e "${RED}   ❌ $controller_name${NC}"
    fi
done

echo -e "${YELLOW}🎯 Admin Controllers without tests:${NC}"
find src/Controller/Admin -name "*Controller.php" 2>/dev/null | while read controller; do
    controller_name=$(basename "$controller" .php)
    if [ ! -f "tests/TestCase/Controller/Admin/${controller_name}Test.php" ]; then
        echo -e "${RED}   ❌ Admin/$controller_name${NC}"
    fi
done

echo -e "${YELLOW}🎯 API Controllers without tests:${NC}"
find src/Controller/Api -name "*Controller.php" 2>/dev/null | while read controller; do
    controller_name=$(basename "$controller" .php)
    if [ ! -f "tests/TestCase/Controller/Api/${controller_name}Test.php" ]; then
        echo -e "${RED}   ❌ Api/$controller_name${NC}"
    fi
done

# Identify missing model tests
echo ""
echo -e "${PURPLE}🔍 Missing Model Tests:${NC}"
echo -e "${BLUE}=======================${NC}"

echo -e "${YELLOW}🎯 Table Models without tests:${NC}"
find src/Model/Table -name "*Table.php" 2>/dev/null | while read model; do
    model_name=$(basename "$model" .php)
    if [ ! -f "tests/TestCase/Model/Table/${model_name}Test.php" ]; then
        echo -e "${RED}   ❌ $model_name${NC}"
    fi
done

echo -e "${YELLOW}🎯 Entity Models without tests:${NC}"
find src/Model/Entity -name "*.php" 2>/dev/null | while read entity; do
    entity_name=$(basename "$entity" .php)
    if [ ! -f "tests/TestCase/Model/Entity/${entity_name}Test.php" ]; then
        echo -e "${RED}   ❌ $entity_name${NC}"
    fi
done

# Identify missing service tests
echo ""
echo -e "${PURPLE}🔍 Missing Service Tests:${NC}"
echo -e "${BLUE}=========================${NC}"

echo -e "${YELLOW}🎯 Services without tests:${NC}"
find src/Service -name "*.php" 2>/dev/null | while read service; do
    # Get relative path from src/Service
    relative_path=${service#src/Service/}
    service_dir=$(dirname "$relative_path")
    service_name=$(basename "$service" .php)
    
    if [ "$service_dir" = "." ]; then
        test_path="tests/TestCase/Service/${service_name}Test.php"
    else
        test_path="tests/TestCase/Service/${service_dir}/${service_name}Test.php"
    fi
    
    if [ ! -f "$test_path" ]; then
        echo -e "${RED}   ❌ $relative_path${NC}"
    fi
done

# Identify missing middleware tests
echo ""
echo -e "${PURPLE}🔍 Missing Middleware Tests:${NC}"
echo -e "${BLUE}============================${NC}"

echo -e "${YELLOW}🎯 Middleware without tests:${NC}"
find src/Middleware -name "*.php" 2>/dev/null | while read middleware; do
    middleware_name=$(basename "$middleware" .php)
    if [ ! -f "tests/TestCase/Middleware/${middleware_name}Test.php" ]; then
        echo -e "${RED}   ❌ $middleware_name${NC}"
    fi
done

echo ""
echo -e "${PURPLE}📋 Summary and Recommendations:${NC}"
echo -e "${BLUE}===============================${NC}"

if [ $TOTAL_CONTROLLERS -gt 0 ] && [ $CONTROLLER_COVERAGE -lt 80 ]; then
    echo -e "${YELLOW}⚠️  Controller test coverage is below 80%${NC}"
fi

if [ $TOTAL_MODELS -gt 0 ] && [ $MODEL_COVERAGE -lt 80 ]; then
    echo -e "${YELLOW}⚠️  Model test coverage is below 80%${NC}"
fi

if [ $TOTAL_SERVICES -gt 0 ] && [ $SERVICE_COVERAGE -lt 80 ]; then
    echo -e "${YELLOW}⚠️  Service test coverage is below 80%${NC}"
fi

echo ""
echo -e "${GREEN}💡 Next Steps:${NC}"
echo -e "${BLUE}1. Create missing test files using CakePHP bake command${NC}"
echo -e "${BLUE}2. Focus on critical business logic in Controllers and Services${NC}"
echo -e "${BLUE}3. Add integration tests for complete user workflows${NC}"
echo -e "${BLUE}4. Implement end-to-end tests for admin interface${NC}"
echo ""
echo -e "${GREEN}🛠️  Use these commands to generate missing tests:${NC}"
echo -e "${BLUE}   docker compose exec willowcms php bin/cake bake test <component> <name>${NC}"
echo -e "${BLUE}   Example: docker compose exec willowcms php bin/cake bake test controller Articles${NC}"
echo ""
echo -e "${GREEN}✅ Analysis complete!${NC}"