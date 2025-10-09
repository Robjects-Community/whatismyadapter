#!/bin/bash

# WillowCMS Testing Progress Tracker
# Usage: ./tools/testing/progress.sh [--coverage] [--detailed]

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$PROJECT_ROOT"

echo -e "${BLUE}===================================${NC}"
echo -e "${BLUE}WillowCMS Testing Progress${NC}"
echo -e "${BLUE}===================================${NC}"
echo ""

# Count total components
echo -e "${YELLOW}📊 Component Inventory:${NC}"
TOTAL_MODELS=$(find app/src/Model/Table -name "*Table.php" 2>/dev/null | wc -l | tr -d ' ')
TOTAL_CONTROLLERS=$(find app/src/Controller -name "*Controller.php" 2>/dev/null | wc -l | tr -d ' ')
TOTAL_MIDDLEWARE=$(find app/src/Middleware -name "*.php" 2>/dev/null | wc -l | tr -d ' ')
TOTAL_COMMANDS=$(find app/src/Command -name "*Command.php" 2>/dev/null | wc -l | tr -d ' ')

echo "   Models: $TOTAL_MODELS"
echo "   Controllers: $TOTAL_CONTROLLERS"
echo "   Middleware: $TOTAL_MIDDLEWARE"
echo "   Commands: $TOTAL_COMMANDS"
echo ""

# Count test files
echo -e "${YELLOW}✅ Test Coverage:${NC}"
TEST_MODELS=$(find app/tests/TestCase/Model/Table -name "*Test.php" 2>/dev/null | wc -l | tr -d ' ')
TEST_CONTROLLERS=$(find app/tests/TestCase/Controller -name "*Test.php" 2>/dev/null | wc -l | tr -d ' ')
TEST_MIDDLEWARE=$(find app/tests/TestCase/Middleware -name "*Test.php" 2>/dev/null | wc -l | tr -d ' ')
TEST_COMMANDS=$(find app/tests/TestCase/Command -name "*Test.php" 2>/dev/null | wc -l | tr -d ' ')

# Calculate percentages
if [ "$TOTAL_MODELS" -gt 0 ]; then
    MODEL_PERCENT=$((TEST_MODELS * 100 / TOTAL_MODELS))
else
    MODEL_PERCENT=0
fi

if [ "$TOTAL_CONTROLLERS" -gt 0 ]; then
    CONTROLLER_PERCENT=$((TEST_CONTROLLERS * 100 / TOTAL_CONTROLLERS))
else
    CONTROLLER_PERCENT=0
fi

if [ "$TOTAL_MIDDLEWARE" -gt 0 ]; then
    MIDDLEWARE_PERCENT=$((TEST_MIDDLEWARE * 100 / TOTAL_MIDDLEWARE))
else
    MIDDLEWARE_PERCENT=0
fi

if [ "$TOTAL_COMMANDS" -gt 0 ]; then
    COMMAND_PERCENT=$((TEST_COMMANDS * 100 / TOTAL_COMMANDS))
else
    COMMAND_PERCENT=0
fi

# Color code based on coverage
get_color() {
    local percent=$1
    if [ "$percent" -ge 80 ]; then
        echo "$GREEN"
    elif [ "$percent" -ge 50 ]; then
        echo "$YELLOW"
    else
        echo "$RED"
    fi
}

MODEL_COLOR=$(get_color $MODEL_PERCENT)
CONTROLLER_COLOR=$(get_color $CONTROLLER_PERCENT)
MIDDLEWARE_COLOR=$(get_color $MIDDLEWARE_PERCENT)
COMMAND_COLOR=$(get_color $COMMAND_PERCENT)

echo -e "   Models: ${MODEL_COLOR}$TEST_MODELS/$TOTAL_MODELS ($MODEL_PERCENT%)${NC}"
echo -e "   Controllers: ${CONTROLLER_COLOR}$TEST_CONTROLLERS/$TOTAL_CONTROLLERS ($CONTROLLER_PERCENT%)${NC}"
echo -e "   Middleware: ${MIDDLEWARE_COLOR}$TEST_MIDDLEWARE/$TOTAL_MIDDLEWARE ($MIDDLEWARE_PERCENT%)${NC}"
echo -e "   Commands: ${COMMAND_COLOR}$TEST_COMMANDS/$TOTAL_COMMANDS ($COMMAND_PERCENT%)${NC}"
echo ""

# Calculate overall progress
TOTAL_COMPONENTS=$((TOTAL_MODELS + TOTAL_CONTROLLERS + TOTAL_MIDDLEWARE + TOTAL_COMMANDS))
TOTAL_TESTS=$((TEST_MODELS + TEST_CONTROLLERS + TEST_MIDDLEWARE + TEST_COMMANDS))

if [ "$TOTAL_COMPONENTS" -gt 0 ]; then
    OVERALL_PERCENT=$((TOTAL_TESTS * 100 / TOTAL_COMPONENTS))
else
    OVERALL_PERCENT=0
fi

OVERALL_COLOR=$(get_color $OVERALL_PERCENT)
echo -e "${YELLOW}📈 Overall Progress:${NC}"
echo -e "   ${OVERALL_COLOR}$TOTAL_TESTS/$TOTAL_COMPONENTS components tested ($OVERALL_PERCENT%)${NC}"
echo ""

# Progress bar
FILLED=$((OVERALL_PERCENT / 5))
EMPTY=$((20 - FILLED))
BAR="["
for ((i=0; i<$FILLED; i++)); do
    BAR+="█"
done
for ((i=0; i<$EMPTY; i++)); do
    BAR+="░"
done
BAR+="]"
echo -e "   $BAR ${OVERALL_PERCENT}%"
echo ""

# Show detailed breakdown if requested
if [ "$1" = "--detailed" ] || [ "$2" = "--detailed" ]; then
    echo -e "${YELLOW}📝 Untested Models:${NC}"
    # Find models without tests
    for model in app/src/Model/Table/*Table.php; do
        if [ -f "$model" ]; then
            basename_model=$(basename "$model" .php)
            test_file="app/tests/TestCase/Model/Table/${basename_model}Test.php"
            if [ ! -f "$test_file" ]; then
                echo -e "   ${RED}✗${NC} $(basename "$model")"
            fi
        fi
    done
    echo ""
    
    echo -e "${YELLOW}📝 Untested Controllers:${NC}"
    # Find controllers without tests
    for controller in app/src/Controller/*Controller.php; do
        if [ -f "$controller" ]; then
            basename_controller=$(basename "$controller" .php)
            test_file="app/tests/TestCase/Controller/${basename_controller}Test.php"
            if [ ! -f "$test_file" ]; then
                echo -e "   ${RED}✗${NC} $(basename "$controller")"
            fi
        fi
    done
    echo ""
fi

# Run tests and get coverage if requested
if [ "$1" = "--coverage" ] || [ "$2" = "--coverage" ]; then
    echo -e "${YELLOW}🔬 Running Test Suite with Coverage...${NC}"
    echo ""
    
    # Check if Docker is running
    if ! docker compose ps willowcms | grep -q "Up"; then
        echo -e "${RED}❌ Error: willowcms container is not running${NC}"
        echo "   Start it with: ./run_dev_env.sh"
        exit 1
    fi
    
    # Run tests with coverage
    docker compose exec -T willowcms vendor/bin/phpunit \
        --coverage-text \
        --colors=always
    
    echo ""
    echo -e "${GREEN}✅ Coverage report generated${NC}"
    echo "   HTML report: app/tmp/coverage/index.html"
    echo "   Open with: open app/tmp/coverage/index.html"
fi

# GitHub integration if gh CLI is available
if command -v gh &> /dev/null; then
    echo ""
    echo -e "${YELLOW}📋 GitHub Issues:${NC}"
    
    # Count testing issues
    OPEN_TESTING_ISSUES=$(gh issue list --label testing --state open 2>/dev/null | wc -l | tr -d ' ')
    CLOSED_TESTING_ISSUES=$(gh issue list --label testing --state closed 2>/dev/null | wc -l | tr -d ' ')
    
    if [ "$OPEN_TESTING_ISSUES" -gt 0 ] || [ "$CLOSED_TESTING_ISSUES" -gt 0 ]; then
        echo "   Open: $OPEN_TESTING_ISSUES"
        echo "   Closed: $CLOSED_TESTING_ISSUES"
        
        if [ "$OPEN_TESTING_ISSUES" -gt 0 ]; then
            echo ""
            echo -e "${YELLOW}   Next Issues:${NC}"
            gh issue list --label testing --state open --limit 3 2>/dev/null | head -3
        fi
    else
        echo "   No testing issues found"
        echo "   Create with: gh issue create --label testing"
    fi
fi

echo ""
echo -e "${BLUE}===================================${NC}"
echo -e "${GREEN}💡 Next Steps:${NC}"
echo ""

# Suggest next actions based on progress
if [ "$OVERALL_PERCENT" -eq 0 ]; then
    echo "1. Generate your first test:"
    echo "   docker compose exec willowcms bin/cake bake test table Users"
    echo ""
    echo "2. Start continuous testing:"
    echo "   ./tools/testing/continuous-test.sh --model Users --watch"
elif [ "$OVERALL_PERCENT" -lt 20 ]; then
    echo "1. Focus on critical models first"
    echo "2. Use watch mode for TDD:"
    echo "   ./tools/testing/continuous-test.sh --model [Name] --watch"
elif [ "$OVERALL_PERCENT" -lt 50 ]; then
    echo "1. Continue model testing"
    echo "2. Start controller tests for completed models"
    echo "3. Check coverage gaps:"
    echo "   ./tools/testing/progress.sh --coverage"
elif [ "$OVERALL_PERCENT" -lt 80 ]; then
    echo "1. Focus on remaining controllers"
    echo "2. Add integration tests"
    echo "3. Review coverage reports regularly"
else
    echo "🎉 Excellent progress! Almost there!"
    echo ""
    echo "1. Review edge cases"
    echo "2. Add integration tests"
    echo "3. Set up CI/CD pipeline"
fi

echo ""
echo -e "${BLUE}===================================${NC}"
