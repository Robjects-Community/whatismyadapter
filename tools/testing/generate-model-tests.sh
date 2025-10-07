#!/bin/bash

################################################################################
# Generate All Model/Table Tests for WillowCMS
################################################################################
#
# Purpose: Generate PHPUnit test stubs for all Model/Table classes using CakePHP bake
#
# Features:
#   - Generates test files for all 33 models
#   - Creates fixtures automatically
#   - Validates Docker environment
#   - Provides progress feedback
#   - Generates summary report
#
# Usage:
#   ./tools/testing/generate-model-tests.sh [--force] [--fixtures-only]
#
# Options:
#   --force           Overwrite existing test files
#   --fixtures-only   Only generate fixtures, skip test files
#   --dry-run         Show what would be generated without actually generating
#
################################################################################

set -euo pipefail

# --- Configuration ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DOCKER_SERVICE="willowcms"
FORCE_FLAG=""
FIXTURES_ONLY=false
DRY_RUN=false

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

# All models to generate tests for
MODELS=(
    "AiMetrics"
    "Aiprompts"
    "Articles"
    "ArticlesTags"
    "ArticlesTranslations"
    "BlockedIps"
    "CableCapabilities"
    "Comments"
    "CookieConsents"
    "DeviceCompatibility"
    "EmailTemplates"
    "ImageGalleriesImages"
    "ImageGalleries"
    "Images"
    "Internationalisations"
    "ModelsImages"
    "PageViews"
    "PortTypes"
    "ProductFormFields"
    "ProductsReliabilityFields"
    "ProductsReliabilityLogs"
    "ProductsReliability"
    "Products"
    "ProductsTags"
    "QueueConfigurations"
    "QuizSubmissions"
    "Settings"
    "Slugs"
    "SystemLogs"
    "Tags"
    "TagsTranslations"
    "UserAccountConfirmations"
    "Users"
)

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}${BOLD}"
    echo "╔═══════════════════════════════════════════════════════════════╗"
    echo "║     WillowCMS Model/Table Test Generation                  ║"
    echo "╚═══════════════════════════════════════════════════════════════╝"
    echo -e "${RESET}"
}

print_error() {
    echo -e "${RED}${BOLD}ERROR:${RESET} ${RED}$*${RESET}" >&2
}

print_success() {
    echo -e "${GREEN}${BOLD}✓${RESET} ${GREEN}$*${RESET}"
}

print_warning() {
    echo -e "${YELLOW}${BOLD}⚠${RESET}  ${YELLOW}$*${RESET}"
}

print_info() {
    echo -e "${BLUE}${BOLD}ℹ${RESET}  ${BLUE}$*${RESET}"
}

print_step() {
    echo -e "${CYAN}${BOLD}➜${RESET} ${CYAN}$*${RESET}"
}

show_help() {
    cat << EOF
${BOLD}WillowCMS Model/Table Test Generation${RESET}

${BOLD}USAGE:${RESET}
    $0 [OPTIONS]

${BOLD}OPTIONS:${RESET}
    ${GREEN}--force${RESET}           Overwrite existing test files
    ${GREEN}--fixtures-only${RESET}   Only generate fixtures, skip test files
    ${GREEN}--dry-run${RESET}         Show what would be generated without actually generating
    ${GREEN}-h, --help${RESET}        Show this help message

${BOLD}EXAMPLES:${RESET}
    # Generate all tests (will skip existing)
    ${CYAN}$0${RESET}
    
    # Force regenerate all tests (overwrites existing)
    ${CYAN}$0 --force${RESET}
    
    # Only generate fixtures
    ${CYAN}$0 --fixtures-only${RESET}
    
    # Preview what would be generated
    ${CYAN}$0 --dry-run${RESET}

${BOLD}WHAT THIS SCRIPT DOES:${RESET}
    1. Validates Docker environment is running
    2. Generates PHPUnit test stubs for all 33 Model/Table classes
    3. Creates fixture files with sample data
    4. Provides detailed progress feedback
    5. Generates summary report with statistics

${BOLD}OUTPUT LOCATIONS:${RESET}
    Tests:    app/tests/TestCase/Model/Table/
    Fixtures: app/tests/Fixture/

EOF
}

check_docker() {
    if ! docker compose ps --services --filter "status=running" | grep -q "^${DOCKER_SERVICE}$"; then
        print_error "Docker service '${DOCKER_SERVICE}' is not running"
        print_info "Run: docker compose up -d"
        exit 1
    fi
}

generate_test() {
    local model=$1
    local test_file="${PROJECT_ROOT}/app/tests/TestCase/Model/Table/${model}TableTest.php"
    
    # Check if test already exists
    if [[ -f "$test_file" ]] && [[ -z "$FORCE_FLAG" ]]; then
        echo -e "   ${YELLOW}⊖${RESET} ${model}Table (test exists, skipping)"
        return 1
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        echo -e "   ${BLUE}→${RESET} Would generate: ${model}TableTest.php"
        return 0
    fi
    
    echo -e "   ${CYAN}→${RESET} Generating: ${model}TableTest.php"
    
    # Generate test with CakePHP bake
    if docker compose exec -T "$DOCKER_SERVICE" bin/cake bake test table "$model" $FORCE_FLAG >/dev/null 2>&1; then
        echo -e "      ${GREEN}✓${RESET} Generated successfully"
        return 0
    else
        echo -e "      ${RED}✗${RESET} Failed to generate"
        return 1
    fi
}

generate_fixture() {
    local model=$1
    local fixture_file="${PROJECT_ROOT}/app/tests/Fixture/${model}Fixture.php"
    
    # Check if fixture already exists
    if [[ -f "$fixture_file" ]] && [[ -z "$FORCE_FLAG" ]]; then
        echo -e "   ${YELLOW}⊖${RESET} ${model}Fixture (fixture exists, skipping)"
        return 1
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        echo -e "   ${BLUE}→${RESET} Would generate: ${model}Fixture.php"
        return 0
    fi
    
    echo -e "   ${CYAN}→${RESET} Generating: ${model}Fixture.php"
    
    # Generate fixture with CakePHP bake
    if docker compose exec -T "$DOCKER_SERVICE" bin/cake bake fixture "$model" $FORCE_FLAG >/dev/null 2>&1; then
        echo -e "      ${GREEN}✓${RESET} Generated successfully"
        return 0
    else
        echo -e "      ${RED}✗${RESET} Failed to generate"
        return 1
    fi
}

################################################################################
# Main Script
################################################################################

main() {
    print_header
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --force)
                FORCE_FLAG="--force"
                shift
                ;;
            --fixtures-only)
                FIXTURES_ONLY=true
                shift
                ;;
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                print_error "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done
    
    # Check Docker
    check_docker
    
    # Display configuration
    echo ""
    print_step "Configuration"
    echo "  Force overwrite: ${FORCE_FLAG:-false}"
    echo "  Fixtures only: $FIXTURES_ONLY"
    echo "  Dry run: $DRY_RUN"
    echo "  Total models: ${#MODELS[@]}"
    echo ""
    
    if [[ "$DRY_RUN" == true ]]; then
        print_warning "DRY RUN MODE - No files will be generated"
        echo ""
    fi
    
    # Counters
    local tests_generated=0
    local tests_skipped=0
    local tests_failed=0
    local fixtures_generated=0
    local fixtures_skipped=0
    local fixtures_failed=0
    
    # Generate test files (unless fixtures-only mode)
    if [[ "$FIXTURES_ONLY" == false ]]; then
        print_step "Generating Test Files"
        echo ""
        
        for model in "${MODELS[@]}"; do
            if generate_test "$model"; then
                ((tests_generated++))
            else
                if [[ -f "${PROJECT_ROOT}/app/tests/TestCase/Model/Table/${model}TableTest.php" ]]; then
                    ((tests_skipped++))
                else
                    ((tests_failed++))
                fi
            fi
        done
        
        echo ""
        print_success "Tests: $tests_generated generated, $tests_skipped skipped, $tests_failed failed"
        echo ""
    fi
    
    # Generate fixtures
    print_step "Generating Fixture Files"
    echo ""
    
    for model in "${MODELS[@]}"; do
        if generate_fixture "$model"; then
            ((fixtures_generated++))
        else
            if [[ -f "${PROJECT_ROOT}/app/tests/Fixture/${model}Fixture.php" ]]; then
                ((fixtures_skipped++))
            else
                ((fixtures_failed++))
            fi
        fi
    done
    
    echo ""
    print_success "Fixtures: $fixtures_generated generated, $fixtures_skipped skipped, $fixtures_failed failed"
    echo ""
    
    # Summary
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${RESET}"
    
    if [[ "$DRY_RUN" == true ]]; then
        print_info "Dry Run Complete - No files were generated"
    else
        print_success "Generation Complete!"
    fi
    
    echo ""
    print_info "Summary:"
    
    if [[ "$FIXTURES_ONLY" == false ]]; then
        echo "  Test Files:"
        echo "    Generated: $tests_generated"
        echo "    Skipped:   $tests_skipped"
        echo "    Failed:    $tests_failed"
    fi
    
    echo "  Fixture Files:"
    echo "    Generated: $fixtures_generated"
    echo "    Skipped:   $fixtures_skipped"
    echo "    Failed:    $fixtures_failed"
    
    echo ""
    print_info "Next Steps:"
    echo ""
    echo "1. Check generated test files:"
    echo "   ${CYAN}ls app/tests/TestCase/Model/Table/${RESET}"
    echo ""
    echo "2. Check generated fixture files:"
    echo "   ${CYAN}ls app/tests/Fixture/${RESET}"
    echo ""
    echo "3. View testing progress:"
    echo "   ${CYAN}./tools/testing/progress.sh${RESET}"
    echo ""
    echo "4. Start continuous testing:"
    echo "   ${CYAN}./tools/testing/continuous-test.sh --model Users --watch${RESET}"
    echo ""
    echo "5. Run all model tests:"
    echo "   ${CYAN}docker compose exec willowcms vendor/bin/phpunit tests/TestCase/Model/Table/${RESET}"
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${RESET}"
}

# Run main function
main "$@"
