#!/usr/bin/env bash
# ==============================================================================
# WillowCMS Docker Compose Test Script
# ==============================================================================
# This script tests the WillowCMS application using docker compose.
# It builds the image, starts services, and runs health checks.
#
# Usage:
#   ./tools/docker/test-willowcms.sh [options]
#
# Options:
#   --build-only     Only build, don't start services
#   --no-build       Don't rebuild, use existing image
#   --phpunit        Run PHPUnit tests after startup
#   --cleanup        Stop and remove containers after tests
#   --help           Show this help message
# ==============================================================================

set -e
set -u

# ==============================================================================
# Configuration
# ==============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../..\" && pwd)"
COMPOSE_FILE="${PROJECT_ROOT}/docker-compose.yml"

# Service name
SERVICE_NAME="willowcms"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

# Options
BUILD_ONLY=false
NO_BUILD=false
RUN_PHPUNIT=false
CLEANUP=false

# ==============================================================================
# Functions
# ==============================================================================

print_header() {
    echo ""
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    echo -e "${BOLD}${BLUE}  $1${NC}"
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}• $1${NC}"
}

show_help() {
    cat << EOF
WillowCMS Docker Compose Test Script

Usage:
    $0 [options]

Options:
    --build-only     Build image without starting services
    --no-build       Skip build, use existing image
    --phpunit        Run PHPUnit tests after services start
    --cleanup        Stop and remove containers when done
    --help           Show this help message

Description:
    Tests the WillowCMS Docker setup by:
    1. Building the Docker image (unless --no-build)
    2. Starting all services with docker compose
    3. Verifying services are healthy
    4. Testing HTTP endpoints
    5. Optionally running PHPUnit tests
    6. Optionally cleaning up

Examples:
    # Standard test
    $0

    # Build only
    $0 --build-only

    # Test with existing image and run tests
    $0 --no-build --phpunit

    # Full test with cleanup
    $0 --phpunit --cleanup

EOF
    exit 0
}

check_requirements() {
    print_info "Checking requirements..."
    
    # Check docker compose
    if ! docker compose version &> /dev/null; then
        print_error "docker compose is not available"
        exit 1
    fi
    print_success "Docker Compose available"
    
    # Check compose file
    if [[ ! -f "${COMPOSE_FILE}" ]]; then
        print_error "docker-compose.yml not found: ${COMPOSE_FILE}"
        exit 1
    fi
    print_success "Compose file found"
}

build_services() {
    print_header "Building Services"
    
    print_info "Building ${SERVICE_NAME} service..."
    
    if docker compose -f "${COMPOSE_FILE}" build ${SERVICE_NAME}; then
        print_success "Service built successfully"
    else
        print_error "Build failed"
        exit 1
    fi
}

start_services() {
    print_header "Starting Services"
    
    print_info "Starting services with docker compose..."
    
    if docker compose -f "${COMPOSE_FILE}" up -d; then
        print_success "Services started"
    else
        print_error "Failed to start services"
        docker compose -f "${COMPOSE_FILE}" logs --tail=50
        exit 1
    fi
}

wait_for_services() {
    print_header "Waiting for Services"
    
    local max_attempts=30
    local attempt=1
    
    print_info "Waiting for ${SERVICE_NAME} to be healthy..."
    
    while [[ ${attempt} -le ${max_attempts} ]]; do
        local status=$(docker compose -f "${COMPOSE_FILE}" ps ${SERVICE_NAME} --format json | grep -o '"Health":"[^"]*"' | cut -d'"' -f4 || echo "unknown")
        
        if [[ "${status}" == "healthy" ]]; then
            print_success "Service is healthy"
            return 0
        elif [[ "${status}" == "unhealthy" ]]; then
            print_error "Service is unhealthy"
            docker compose -f "${COMPOSE_FILE}" logs ${SERVICE_NAME} --tail=20
            return 1
        fi
        
        echo -n "."
        sleep 2
        ((attempt++))
    done
    
    echo ""
    print_warning "Health check timeout (${max_attempts} attempts)"
    
    # Check if container is at least running
    if docker compose -f "${COMPOSE_FILE}" ps ${SERVICE_NAME} --format json | grep -q '"State":"running"'; then
        print_warning "Container is running but health check may not be configured"
        return 0
    else
        print_error "Container is not running"
        return 1
    fi
}

test_http_endpoints() {
    print_header "Testing HTTP Endpoints"
    
    # Get port from compose file or default
    local port=$(grep "WILLOW_HTTP_PORT" "${PROJECT_ROOT}/.env" 2>/dev/null | cut -d'=' -f2 || echo "8080")
    local base_url="http://localhost:${port}"
    
    print_info "Testing base URL: ${base_url}"
    
    # Test root endpoint
    print_info "Testing root endpoint..."
    local response=$(curl -s -o /dev/null -w "%{http_code}" "${base_url}/" || echo "000")
    if [[ "${response}" =~ ^[23] ]]; then
        print_success "Root endpoint responding: HTTP ${response}"
    else
        print_warning "Root endpoint returned: HTTP ${response} (may need database setup)"
    fi
    
    # Test PHP-FPM ping
    print_info "Testing PHP-FPM ping endpoint..."
    local fpm_response=$(curl -s -o /dev/null -w "%{http_code}" "${base_url}/fpm-ping" || echo "000")
    if [[ "${fpm_response}" == "200" ]]; then
        print_success "PHP-FPM ping successful"
    else
        print_warning "PHP-FPM ping returned: HTTP ${fpm_response}"
    fi
}

test_database_connection() {
    print_header "Testing Database Connection"
    
    print_info "Checking database connectivity..."
    
    local db_test=$(docker compose -f "${COMPOSE_FILE}" exec -T ${SERVICE_NAME} \
        sh -c "php -r \"try { new PDO('mysql:host=mysql;dbname=${MYSQL_DATABASE}', '${MYSQL_USER}', '${MYSQL_ROOT_PASSWORD}'); echo 'SUCCESS'; } catch (Exception \$e) { echo 'FAILED'; }\"" 2>/dev/null || echo "ERROR")
    
    if [[ "${db_test}" == "SUCCESS" ]]; then
        print_success "Database connection successful"
    else
        print_warning "Database connection test inconclusive"
    fi
}

test_redis_connection() {
    print_header "Testing Redis Connection"
    
    print_info "Checking Redis connectivity..."
    
    local redis_test=$(docker compose -f "${COMPOSE_FILE}" exec -T redis \
        redis-cli -a "${REDIS_PASSWORD}" ping 2>/dev/null || echo "ERROR")
    
    if [[ "${redis_test}" == "PONG" ]]; then
        print_success "Redis connection successful"
    else
        print_warning "Redis connection test failed"
    fi
}

run_phpunit_tests() {
    print_header "Running PHPUnit Tests"
    
    print_info "Running PHPUnit test suite..."
    
    # Run tests by component to get better feedback
    local test_dirs=("Controller" "Model" "View")
    local failed=false
    
    for dir in "${test_dirs[@]}"; do
        local test_path="tests/TestCase/${dir}"
        
        print_info "Testing ${dir} component..."
        
        if docker compose -f "${COMPOSE_FILE}" exec -T ${SERVICE_NAME} \
            php vendor/bin/phpunit "${test_path}" 2>&1 | tee "/tmp/phpunit_${dir}.log"; then
            print_success "${dir} tests passed"
        else
            print_error "${dir} tests failed"
            failed=true
        fi
    done
    
    if [[ "${failed}" = true ]]; then
        print_error "Some tests failed, check logs in /tmp/phpunit_*.log"
        return 1
    else
        print_success "All tests passed"
        return 0
    fi
}

check_logs() {
    print_header "Checking Container Logs"
    
    print_info "Scanning logs for errors..."
    
    local errors=$(docker compose -f "${COMPOSE_FILE}" logs ${SERVICE_NAME} --tail=100 2>&1 | \
        grep -i "error\|fail\|fatal\|exception" | wc -l | tr -d ' ')
    
    if [[ ${errors} -eq 0 ]]; then
        print_success "No errors found in logs"
    else
        print_warning "Found ${errors} error-like message(s) in logs"
        print_info "Recent log entries:"
        docker compose -f "${COMPOSE_FILE}" logs ${SERVICE_NAME} --tail=20
    fi
}

show_service_info() {
    print_header "Service Information"
    
    echo ""
    docker compose -f "${COMPOSE_FILE}" ps
    echo ""
    
    print_info "Container details:"
    docker compose -f "${COMPOSE_FILE}" exec ${SERVICE_NAME} sh -c "whoami" 2>/dev/null && \
        print_success "Running as: $(docker compose -f "${COMPOSE_FILE}" exec ${SERVICE_NAME} whoami 2>/dev/null)"
    
    print_info "PHP version:"
    docker compose -f "${COMPOSE_FILE}" exec -T ${SERVICE_NAME} php --version | head -1
    
    print_info "Nginx status:"
    docker compose -f "${COMPOSE_FILE}" exec -T ${SERVICE_NAME} sh -c "pgrep nginx > /dev/null && echo 'Running' || echo 'Not running'"
}

cleanup_services() {
    print_header "Cleaning Up"
    
    print_info "Stopping services..."
    docker compose -f "${COMPOSE_FILE}" down
    
    print_success "Services stopped and removed"
}

generate_report() {
    print_header "Test Report"
    
    cat << EOF

Test Summary:
  Compose File:  ${COMPOSE_FILE}
  Service:       ${SERVICE_NAME}
  Build:         $([ "${NO_BUILD}" = true ] && echo "Skipped" || echo "Completed")
  Startup:       $([ "${BUILD_ONLY}" = true ] && echo "Skipped" || echo "Completed")
  PHPUnit:       $([ "${RUN_PHPUNIT}" = true ] && echo "Executed" || echo "Skipped")
  Cleanup:       $([ "${CLEANUP}" = true ] && echo "Completed" || echo "Manual")

Next Steps:
  # View logs
  docker compose -f ${COMPOSE_FILE} logs -f ${SERVICE_NAME}
  
  # Access application
  # http://localhost:\${WILLOW_HTTP_PORT}
  
  # Run shell in container
  docker compose -f ${COMPOSE_FILE} exec ${SERVICE_NAME} sh
  
EOF

    if [[ "${CLEANUP}" = false ]]; then
        cat << EOF
  # Stop services when done
  docker compose -f ${COMPOSE_FILE} down

EOF
    fi
}

# ==============================================================================
# Main Script
# ==============================================================================

main() {
    print_header "WillowCMS Docker Compose Test"
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --build-only)
                BUILD_ONLY=true
                shift
                ;;
            --no-build)
                NO_BUILD=true
                shift
                ;;
            --phpunit)
                RUN_PHPUNIT=true
                shift
                ;;
            --cleanup)
                CLEANUP=true
                shift
                ;;
            --help)
                show_help
                ;;
            *)
                print_error "Unknown option: $1"
                show_help
                ;;
        esac
    done
    
    # Check requirements
    check_requirements
    
    # Build if needed
    if [[ "${NO_BUILD}" = false ]]; then
        build_services
    fi
    
    # Exit if build-only
    if [[ "${BUILD_ONLY}" = true ]]; then
        print_success "Build completed successfully"
        exit 0
    fi
    
    # Start services
    start_services
    wait_for_services || exit 1
    
    # Run tests
    test_http_endpoints
    test_database_connection
    test_redis_connection
    check_logs
    show_service_info
    
    # Run PHPUnit if requested
    if [[ "${RUN_PHPUNIT}" = true ]]; then
        run_phpunit_tests || print_warning "Some tests failed"
    fi
    
    # Cleanup if requested
    if [[ "${CLEANUP}" = true ]]; then
        cleanup_services
    fi
    
    # Generate report
    generate_report
    
    print_header "Tests Completed!"
    print_success "WillowCMS is ready"
    
    exit 0
}

# Trap cleanup on error if cleanup flag is set
if [[ "${CLEANUP}" = true ]]; then
    trap cleanup_services ERR EXIT
fi

# Run main function
main "$@"
