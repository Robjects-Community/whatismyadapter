#!/bin/bash

# WillowCMS Test Generator
# Generates PHPUnit test stubs for all models and controllers
# Usage: ./tools/testing/generate-all-tests.sh [--models] [--controllers] [--dry-run]

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

# Parse arguments
DRY_RUN=false
GENERATE_MODELS=false
GENERATE_CONTROLLERS=false

for arg in "$@"; do
    case $arg in
        --dry-run)
            DRY_RUN=true
            ;;
        --models)
            GENERATE_MODELS=true
            ;;
        --controllers)
            GENERATE_CONTROLLERS=true
            ;;
        --all)
            GENERATE_MODELS=true
            GENERATE_CONTROLLERS=true
            ;;
        --help)
            echo "Usage: $0 [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --models         Generate tests for all models"
            echo "  --controllers    Generate tests for all controllers"
            echo "  --all            Generate tests for both models and controllers"
            echo "  --dry-run        Show what would be generated without actually generating"
            echo "  --help           Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0 --models                  # Generate all model tests"
            echo "  $0 --controllers --dry-run   # Preview controller tests"
            echo "  $0 --all                     # Generate all tests"
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $arg${NC}"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Default to all if nothing specified
if [ "$GENERATE_MODELS" = false ] && [ "$GENERATE_CONTROLLERS" = false ]; then
    GENERATE_MODELS=true
    GENERATE_CONTROLLERS=true
fi

echo -e "${BLUE}===================================${NC}"
echo -e "${BLUE}WillowCMS Test Generator${NC}"
echo -e "${BLUE}===================================${NC}"
echo ""

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}🔍 DRY RUN MODE - No files will be generated${NC}"
    echo ""
fi

# Check if Docker is running
if ! docker compose ps willowcms | grep -q "Up"; then
    echo -e "${RED}❌ Error: willowcms container is not running${NC}"
    echo "   Start it with: ./run_dev_env.sh"
    exit 1
fi

# Generate model tests
if [ "$GENERATE_MODELS" = true ]; then
    echo -e "${YELLOW}📊 Generating Model Tests...${NC}"
    echo ""
    
    MODEL_COUNT=0
    SKIPPED_COUNT=0
    
    for model_file in app/src/Model/Table/*Table.php; do
        if [ -f "$model_file" ]; then
            # Extract model name (e.g., UsersTable -> Users)
            basename_model=$(basename "$model_file" Table.php)
            test_file="app/tests/TestCase/Model/Table/${basename_model}TableTest.php"
            
            # Check if test already exists
            if [ -f "$test_file" ]; then
                echo -e "   ${YELLOW}⊖${NC} $basename_model (test exists, skipping)"
                ((SKIPPED_COUNT++))
                continue
            fi
            
            if [ "$DRY_RUN" = true ]; then
                echo -e "   ${BLUE}→${NC} Would generate: ${basename_model}TableTest.php"
                ((MODEL_COUNT++))
            else
                echo -e "   ${GREEN}+${NC} Generating: ${basename_model}TableTest.php"
                
                # Generate test with CakePHP bake
                if docker compose exec -T willowcms bin/cake bake test table "$basename_model" --force >/dev/null 2>&1; then
                    echo -e "      ${GREEN}✓${NC} Generated successfully"
                    ((MODEL_COUNT++))
                else
                    echo -e "      ${RED}✗${NC} Failed to generate"
                fi
            fi
        fi
    done
    
    echo ""
    echo -e "${GREEN}Model Tests: $MODEL_COUNT generated, $SKIPPED_COUNT skipped${NC}"
    echo ""
fi

# Generate controller tests
if [ "$GENERATE_CONTROLLERS" = true ]; then
    echo -e "${YELLOW}🎮 Generating Controller Tests...${NC}"
    echo ""
    
    CONTROLLER_COUNT=0
    SKIPPED_COUNT=0
    
    for controller_file in app/src/Controller/*Controller.php; do
        if [ -f "$controller_file" ]; then
            # Skip AppController and ErrorController (base classes)
            basename_controller=$(basename "$controller_file" .php)
            if [[ "$basename_controller" == "AppController" ]] || [[ "$basename_controller" == "ErrorController" ]]; then
                continue
            fi
            
            # Extract controller name (e.g., UsersController -> Users)
            controller_name=$(basename "$controller_file" Controller.php)
            test_file="app/tests/TestCase/Controller/${controller_name}ControllerTest.php"
            
            # Check if test already exists
            if [ -f "$test_file" ]; then
                echo -e "   ${YELLOW}⊖${NC} $controller_name (test exists, skipping)"
                ((SKIPPED_COUNT++))
                continue
            fi
            
            if [ "$DRY_RUN" = true ]; then
                echo -e "   ${BLUE}→${NC} Would generate: ${controller_name}ControllerTest.php"
                ((CONTROLLER_COUNT++))
            else
                echo -e "   ${GREEN}+${NC} Generating: ${controller_name}ControllerTest.php"
                
                # Generate test with CakePHP bake
                if docker compose exec -T willowcms bin/cake bake test controller "$controller_name" --force >/dev/null 2>&1; then
                    echo -e "      ${GREEN}✓${NC} Generated successfully"
                    ((CONTROLLER_COUNT++))
                else
                    echo -e "      ${RED}✗${NC} Failed to generate"
                fi
            fi
        fi
    done
    
    echo ""
    echo -e "${GREEN}Controller Tests: $CONTROLLER_COUNT generated, $SKIPPED_COUNT skipped${NC}"
    echo ""
fi

echo -e "${BLUE}===================================${NC}"

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}📋 Summary (Dry Run):${NC}"
    echo ""
    echo "   This was a dry run. No files were created."
    echo "   Run without --dry-run to generate tests."
else
    echo -e "${GREEN}✅ Test Generation Complete${NC}"
    echo ""
    echo -e "${YELLOW}📋 Next Steps:${NC}"
    echo ""
    echo "1. Check generated tests:"
    echo "   ls app/tests/TestCase/Model/Table/"
    echo "   ls app/tests/TestCase/Controller/"
    echo ""
    echo "2. View progress:"
    echo "   ./tools/testing/progress.sh"
    echo ""
    echo "3. Start testing a component:"
    echo "   ./tools/testing/continuous-test.sh --model [Name] --watch"
    echo ""
    echo "4. Run all tests:"
    echo "   docker compose exec willowcms vendor/bin/phpunit"
fi

echo ""
echo -e "${BLUE}===================================${NC}"
