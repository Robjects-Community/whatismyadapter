#!/bin/bash

# Willow CMS - Code Style Automation Script
# This script automates checking and fixing code style violations
# Usage: ./scripts/code-style-check.sh [--fix] [--verbose] [--path=src/]

set -e

# Configuration
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOCKER_SERVICE="willowcms"
PHPCS_STANDARD="vendor/cakephp/cakephp-codesniffer/CakePHP"
PATHS_TO_CHECK=("src/" "tests/" "plugins/")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse command line arguments
FIX_MODE=false
VERBOSE=false
CUSTOM_PATH=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --fix)
            FIX_MODE=true
            shift
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        --path=*)
            CUSTOM_PATH="${1#*=}"
            shift
            ;;
        --help|-h)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --fix       Auto-fix violations where possible"
            echo "  --verbose   Show detailed output"
            echo "  --path=DIR  Check specific directory only"
            echo "  --help      Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option $1"
            exit 1
            ;;
    esac
done

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running and service is available
check_docker() {
    if ! docker compose ps | grep -q "${DOCKER_SERVICE}.*Up"; then
        log_error "Docker service '${DOCKER_SERVICE}' is not running"
        log_info "Please start the development environment with: ./run_dev_env.sh"
        exit 1
    fi
}

# Run command in Docker container
docker_exec() {
    if [ "$VERBOSE" = true ]; then
        docker compose exec -T "${DOCKER_SERVICE}" "$@"
    else
        docker compose exec -T "${DOCKER_SERVICE}" "$@" 2>/dev/null
    fi
}

# Check PHP CodeSniffer violations
check_style() {
    local path="$1"
    local violations=0
    
    log_info "Checking code style for: $path"
    
    if docker_exec php vendor/bin/phpcs \
        --standard="$PHPCS_STANDARD" \
        --colors \
        --report=summary \
        "$path" 2>/dev/null; then
        log_success "No violations found in $path"
    else
        violations=$?
        log_warning "Found violations in $path"
        
        # Show detailed report if verbose
        if [ "$VERBOSE" = true ]; then
            docker_exec php vendor/bin/phpcs \
                --standard="$PHPCS_STANDARD" \
                --colors \
                --report=full \
                "$path" 2>/dev/null || true
        fi
    fi
    
    return $violations
}

# Auto-fix PHP CodeSniffer violations
fix_style() {
    local path="$1"
    
    log_info "Auto-fixing code style for: $path"
    
    if docker_exec php vendor/bin/phpcbf \
        --standard="$PHPCS_STANDARD" \
        --colors \
        "$path" 2>/dev/null; then
        log_success "Auto-fixed violations in $path"
        return 0
    else
        local exit_code=$?
        if [ $exit_code -eq 1 ]; then
            log_success "Auto-fixed some violations in $path"
            return 0
        else
            log_warning "Some violations in $path could not be auto-fixed"
            return $exit_code
        fi
    fi
}

# Run PHPStan static analysis
check_static_analysis() {
    local path="$1"
    
    log_info "Running static analysis for: $path"
    
    if docker_exec php vendor/bin/phpstan analyse \
        --level=5 \
        --no-progress \
        "$path" 2>/dev/null; then
        log_success "Static analysis passed for $path"
        return 0
    else
        log_warning "Static analysis found issues in $path"
        
        # Show detailed report if verbose
        if [ "$VERBOSE" = true ]; then
            docker_exec php vendor/bin/phpstan analyse \
                --level=5 \
                "$path" 2>/dev/null || true
        fi
        return 1
    fi
}

# Generate code style report
generate_report() {
    local report_file="$PROJECT_ROOT/reports/code-style-$(date +%Y%m%d-%H%M%S).txt"
    
    mkdir -p "$PROJECT_ROOT/reports"
    
    log_info "Generating detailed code style report: $report_file"
    
    {
        echo "Willow CMS Code Style Report"
        echo "Generated: $(date)"
        echo "=================================="
        echo
        
        for path in "${PATHS_TO_CHECK[@]}"; do
            echo "=== $path ==="
            docker_exec php vendor/bin/phpcs \
                --standard="$PHPCS_STANDARD" \
                --report=full \
                "$path" 2>/dev/null || true
            echo
        done
    } > "$report_file"
    
    log_success "Report saved to: $report_file"
}

# Main execution
main() {
    cd "$PROJECT_ROOT"
    
    log_info "Starting code style automation for Willow CMS"
    log_info "Fix mode: $FIX_MODE | Verbose: $VERBOSE"
    
    # Check Docker environment
    check_docker
    
    # Clear caches first
    log_info "Clearing caches..."
    docker_exec bin/cake cache clear_all > /dev/null 2>&1
    
    # Determine paths to check
    local paths_to_process=()
    if [ -n "$CUSTOM_PATH" ]; then
        paths_to_process=("$CUSTOM_PATH")
    else
        paths_to_process=("${PATHS_TO_CHECK[@]}")
    fi
    
    local total_violations=0
    local total_fixes=0
    
    # Process each path
    for path in "${paths_to_process[@]}"; do
        if [ ! -d "$path" ]; then
            log_warning "Path not found: $path (skipping)"
            continue
        fi
        
        echo
        log_info "Processing: $path"
        echo "----------------------------------------"
        
        if [ "$FIX_MODE" = true ]; then
            # Auto-fix first
            if fix_style "$path"; then
                ((total_fixes++))
            fi
            
            # Check again after fixes
            if ! check_style "$path"; then
                ((total_violations++))
            fi
        else
            # Just check without fixing
            if ! check_style "$path"; then
                ((total_violations++))
            fi
        fi
        
        # Run static analysis if no violations or after fixes
        if [ "$VERBOSE" = true ]; then
            check_static_analysis "$path" || true
        fi
    done
    
    echo
    echo "========================================"
    log_info "Summary"
    echo "========================================"
    
    if [ "$FIX_MODE" = true ]; then
        log_info "Paths auto-fixed: $total_fixes"
    fi
    
    if [ $total_violations -eq 0 ]; then
        log_success "All code style checks passed! ✅"
        
        # Generate report if verbose
        if [ "$VERBOSE" = true ]; then
            generate_report
        fi
        
        exit 0
    else
        log_warning "Found violations in $total_violations path(s)"
        
        if [ "$FIX_MODE" = false ]; then
            log_info "Run with --fix to auto-fix violations where possible"
        fi
        
        # Generate detailed report
        generate_report
        
        exit 1
    fi
}

# Run main function
main "$@"
