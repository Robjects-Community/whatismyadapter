#!/bin/bash

################################################################################
# Continuous Testing Workflow for CakePHP 5.x MVC Components
################################################################################
#
# Purpose: Run PHPUnit tests continuously with auto-reload on file changes
#
# Features:
#   - Test specific MVC components (Model, View, Controller)
#   - Watch mode with auto-reload on file changes
#   - Focused testing on single components
#   - Coverage reports for each component
#   - Integration with CakePHP 5.x test suite
#   - Color-coded output and progress tracking
#
# Usage:
#   ./continuous-test.sh [options]
#
# Examples:
#   # Test a specific model continuously
#   ./continuous-test.sh --model Users --watch
#
#   # Test a controller with coverage
#   ./continuous-test.sh --controller Articles --coverage
#
#   # Test all models
#   ./continuous-test.sh --type model --all
#
################################################################################

set -euo pipefail

# --- Configuration ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
APP_DIR="${PROJECT_ROOT}/app"
DOCKER_SERVICE="willowcms"
WATCH_MODE=false
COVERAGE_MODE=false
COMPONENT_TYPE=""
COMPONENT_NAME=""
TEST_ALL=false
FILTER=""
STOP_ON_FAILURE=true
VERBOSE=false
ITERATIONS=0
MAX_ITERATIONS=0

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}${BOLD}"
    echo "╔═══════════════════════════════════════════════════════════════╗"
    echo "║     CakePHP 5.x Continuous Testing Workflow                 ║"
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
${BOLD}CakePHP 5.x Continuous Testing Workflow${RESET}

${BOLD}USAGE:${RESET}
    $0 [OPTIONS]

${BOLD}COMPONENT OPTIONS:${RESET}
    ${GREEN}--model NAME${RESET}         Test a specific model (e.g., Users, Articles)
    ${GREEN}--controller NAME${RESET}    Test a specific controller (e.g., Users, Articles)
    ${GREEN}--component NAME${RESET}     Test a specific component
    ${GREEN}--behavior NAME${RESET}      Test a specific behavior
    ${GREEN}--helper NAME${RESET}        Test a specific helper
    ${GREEN}--command NAME${RESET}       Test a specific command

${BOLD}TYPE OPTIONS:${RESET}
    ${GREEN}--type TYPE${RESET}          Test all components of type (model|controller|component)
    ${GREEN}--all${RESET}                Test all components of specified type

${BOLD}MODE OPTIONS:${RESET}
    ${GREEN}-w, --watch${RESET}          Enable watch mode (auto-reload on changes)
    ${GREEN}-c, --coverage${RESET}       Generate code coverage report
    ${GREEN}-v, --verbose${RESET}        Enable verbose output
    ${GREEN}--no-stop${RESET}            Continue testing even after failures
    ${GREEN}--filter PATTERN${RESET}     Filter tests by pattern
    ${GREEN}--iterations N${RESET}       Run tests N times (0 = infinite)

${BOLD}EXAMPLES:${RESET}
    # Test UsersTable continuously with watch mode
    ${CYAN}$0 --model Users --watch${RESET}

    # Test ArticlesController with coverage
    ${CYAN}$0 --controller Articles --coverage${RESET}

    # Test all models once
    ${CYAN}$0 --type model --all${RESET}

    # Test specific method in UsersTable
    ${CYAN}$0 --model Users --filter testValidation${RESET}

    # Run all controller tests 5 times
    ${CYAN}$0 --type controller --all --iterations 5${RESET}

${BOLD}WATCH MODE:${RESET}
    In watch mode, tests will automatically re-run when files change.
    Monitors: src/, tests/, config/ directories
    Press Ctrl+C to exit watch mode

${BOLD}COVERAGE:${RESET}
    Coverage reports are generated in: app/tmp/coverage/
    View with: open app/tmp/coverage/index.html

EOF
}

check_docker() {
    if ! docker compose ps --services --filter "status=running" | grep -q "^${DOCKER_SERVICE}$"; then
        print_error "Docker service '${DOCKER_SERVICE}' is not running"
        print_info "Run: ./run_dev_env.sh"
        exit 1
    fi
}

get_test_path() {
    local type="$1"
    local name="$2"
    
    case "$type" in
        model)
            echo "tests/TestCase/Model/Table/${name}TableTest.php"
            ;;
        controller)
            echo "tests/TestCase/Controller/${name}ControllerTest.php"
            ;;
        component)
            echo "tests/TestCase/Controller/Component/${name}ComponentTest.php"
            ;;
        behavior)
            echo "tests/TestCase/Model/Behavior/${name}BehaviorTest.php"
            ;;
        helper)
            echo "tests/TestCase/View/Helper/${name}HelperTest.php"
            ;;
        command)
            echo "tests/TestCase/Command/${name}CommandTest.php"
            ;;
        *)
            echo ""
            ;;
    esac
}

check_test_exists() {
    local test_path="$1"
    local full_path="${APP_DIR}/${test_path}"
    
    if [[ ! -f "$full_path" ]]; then
        print_warning "Test file not found: ${test_path}"
        print_info "Available test files:"
        find "${APP_DIR}/tests/TestCase" -name "*Test.php" -type f | sed 's|.*/tests/TestCase/||' | sort
        return 1
    fi
    return 0
}

build_phpunit_command() {
    local test_path="$1"
    local cmd="php /var/www/html/vendor/bin/phpunit"
    
    # Add test path if specified
    if [[ -n "$test_path" ]]; then
        cmd="$cmd /var/www/html/${test_path}"
    fi
    
    # Add filter if specified
    if [[ -n "$FILTER" ]]; then
        cmd="$cmd --filter ${FILTER}"
    fi
    
    # Add coverage if requested
    if [[ "$COVERAGE_MODE" == true ]]; then
        cmd="$cmd --coverage-html /var/www/html/tmp/coverage"
        if [[ "$VERBOSE" == true ]]; then
            cmd="$cmd --coverage-text"
        fi
    fi
    
    # Add stop on failure unless disabled
    if [[ "$STOP_ON_FAILURE" == true ]]; then
        cmd="$cmd --stop-on-failure"
    fi
    
    # Add verbose if requested
    if [[ "$VERBOSE" == true ]]; then
        cmd="$cmd --verbose"
    fi
    
    echo "$cmd"
}

run_tests() {
    local test_path="$1"
    local iteration="${2:-1}"
    
    ((ITERATIONS++))
    
    echo ""
    print_step "Running tests (iteration: ${iteration})"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [[ -n "$test_path" ]]; then
        print_info "Test file: ${test_path}"
    else
        print_info "Running all tests"
    fi
    
    if [[ -n "$FILTER" ]]; then
        print_info "Filter: ${FILTER}"
    fi
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    
    local cmd
    cmd=$(build_phpunit_command "$test_path")
    
    local start_time
    start_time=$(date +%s)
    
    if docker compose exec -T "$DOCKER_SERVICE" bash -c "$cmd"; then
        local end_time
        end_time=$(date +%s)
        local duration=$((end_time - start_time))
        
        echo ""
        print_success "Tests passed in ${duration}s"
        
        if [[ "$COVERAGE_MODE" == true ]]; then
            print_info "Coverage report: ${APP_DIR}/tmp/coverage/index.html"
        fi
        
        return 0
    else
        local end_time
        end_time=$(date +%s)
        local duration=$((end_time - start_time))
        
        echo ""
        print_error "Tests failed after ${duration}s"
        
        return 1
    fi
}

watch_for_changes() {
    local test_path="$1"
    
    print_info "Watch mode enabled"
    print_info "Monitoring: src/, tests/, config/ directories"
    print_warning "Press Ctrl+C to exit"
    echo ""
    
    # Install fswatch if not available (macOS)
    if [[ "$(uname -s)" == "Darwin" ]] && ! command -v fswatch &> /dev/null; then
        print_warning "fswatch not found. Install with: brew install fswatch"
        print_info "Falling back to polling mode (slower)"
        watch_polling "$test_path"
        return
    fi
    
    # Run tests initially
    run_tests "$test_path" 1 || true
    
    local iteration=2
    
    # Watch for file changes
    if command -v fswatch &> /dev/null; then
        # Use fswatch (macOS)
        fswatch -o \
            "${APP_DIR}/src" \
            "${APP_DIR}/tests" \
            "${APP_DIR}/config" \
            2>/dev/null | while read -r; do
            
            echo ""
            print_info "File change detected, re-running tests..."
            sleep 0.5  # Debounce
            
            if [[ "$MAX_ITERATIONS" -gt 0 ]] && [[ "$iteration" -gt "$MAX_ITERATIONS" ]]; then
                print_success "Reached maximum iterations (${MAX_ITERATIONS})"
                break
            fi
            
            run_tests "$test_path" "$iteration" || true
            ((iteration++))
        done
    else
        watch_polling "$test_path"
    fi
}

watch_polling() {
    local test_path="$1"
    local iteration=1
    local last_checksum=""
    
    print_info "Using polling mode (checks every 2 seconds)"
    
    while true; do
        # Calculate checksum of relevant files
        local current_checksum
        current_checksum=$(find "${APP_DIR}/src" "${APP_DIR}/tests" "${APP_DIR}/config" \
            -type f \( -name "*.php" -o -name "*.yml" -o -name "*.yaml" \) \
            -exec md5 {} \; 2>/dev/null | md5)
        
        if [[ "$last_checksum" != "$current_checksum" ]]; then
            if [[ -n "$last_checksum" ]]; then
                echo ""
                print_info "File change detected, re-running tests..."
                
                if [[ "$MAX_ITERATIONS" -gt 0 ]] && [[ "$iteration" -gt "$MAX_ITERATIONS" ]]; then
                    print_success "Reached maximum iterations (${MAX_ITERATIONS})"
                    break
                fi
                
                run_tests "$test_path" "$iteration" || true
                ((iteration++))
            fi
            last_checksum="$current_checksum"
        fi
        
        sleep 2
    done
}

run_continuous() {
    local test_path="$1"
    
    if [[ "$MAX_ITERATIONS" -eq 0 ]]; then
        print_info "Running continuously (press Ctrl+C to stop)"
    else
        print_info "Running ${MAX_ITERATIONS} iterations"
    fi
    
    local iteration=1
    
    while true; do
        if [[ "$MAX_ITERATIONS" -gt 0 ]] && [[ "$iteration" -gt "$MAX_ITERATIONS" ]]; then
            print_success "Completed ${MAX_ITERATIONS} iterations"
            break
        fi
        
        if ! run_tests "$test_path" "$iteration"; then
            if [[ "$STOP_ON_FAILURE" == true ]]; then
                print_error "Stopping due to test failure"
                exit 1
            fi
        fi
        
        ((iteration++))
        
        if [[ "$MAX_ITERATIONS" -eq 0 ]] || [[ "$iteration" -le "$MAX_ITERATIONS" ]]; then
            sleep 1
        fi
    done
}

################################################################################
# Main Script
################################################################################

main() {
    print_header
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --model)
                COMPONENT_TYPE="model"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --controller)
                COMPONENT_TYPE="controller"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --component)
                COMPONENT_TYPE="component"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --behavior)
                COMPONENT_TYPE="behavior"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --helper)
                COMPONENT_TYPE="helper"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --command)
                COMPONENT_TYPE="command"
                COMPONENT_NAME="$2"
                shift 2
                ;;
            --type)
                COMPONENT_TYPE="$2"
                shift 2
                ;;
            --all)
                TEST_ALL=true
                shift
                ;;
            -w|--watch)
                WATCH_MODE=true
                shift
                ;;
            -c|--coverage)
                COVERAGE_MODE=true
                shift
                ;;
            -v|--verbose)
                VERBOSE=true
                shift
                ;;
            --no-stop)
                STOP_ON_FAILURE=false
                shift
                ;;
            --filter)
                FILTER="$2"
                shift 2
                ;;
            --iterations)
                MAX_ITERATIONS="$2"
                shift 2
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
    
    # Determine test path
    local test_path=""
    
    if [[ -n "$COMPONENT_NAME" ]]; then
        test_path=$(get_test_path "$COMPONENT_TYPE" "$COMPONENT_NAME")
        
        if [[ -z "$test_path" ]]; then
            print_error "Invalid component type: $COMPONENT_TYPE"
            exit 1
        fi
        
        if ! check_test_exists "$test_path"; then
            print_error "Test file not found"
            exit 1
        fi
        
        print_info "Testing: ${COMPONENT_TYPE} '${COMPONENT_NAME}'"
    elif [[ "$TEST_ALL" == true ]]; then
        if [[ -n "$COMPONENT_TYPE" ]]; then
            print_info "Testing all: ${COMPONENT_TYPE}s"
            test_path="tests/TestCase/"
            case "$COMPONENT_TYPE" in
                model) test_path+="Model/Table/" ;;
                controller) test_path+="Controller/" ;;
                component) test_path+="Controller/Component/" ;;
                behavior) test_path+="Model/Behavior/" ;;
                helper) test_path+="View/Helper/" ;;
                command) test_path+="Command/" ;;
            esac
        else
            print_info "Testing all test suites"
            test_path=""
        fi
    else
        print_error "No component specified"
        print_info "Use --model, --controller, etc., or --all"
        show_help
        exit 1
    fi
    
    # Display configuration
    echo ""
    print_step "Configuration"
    echo "  Component: ${COMPONENT_TYPE:-all} ${COMPONENT_NAME:-all}"
    echo "  Watch mode: ${WATCH_MODE}"
    echo "  Coverage: ${COVERAGE_MODE}"
    echo "  Stop on failure: ${STOP_ON_FAILURE}"
    if [[ -n "$FILTER" ]]; then
        echo "  Filter: ${FILTER}"
    fi
    if [[ "$MAX_ITERATIONS" -gt 0 ]]; then
        echo "  Iterations: ${MAX_ITERATIONS}"
    fi
    echo ""
    
    # Run tests
    if [[ "$WATCH_MODE" == true ]]; then
        watch_for_changes "$test_path"
    elif [[ "$MAX_ITERATIONS" -gt 0 ]] || [[ "$MAX_ITERATIONS" -eq 0 && "$WATCH_MODE" == false ]]; then
        run_continuous "$test_path"
    else
        run_tests "$test_path" 1
    fi
    
    # Summary
    echo ""
    print_success "Testing workflow completed"
    print_info "Total iterations run: ${ITERATIONS}"
}

# Run main function
main "$@"
