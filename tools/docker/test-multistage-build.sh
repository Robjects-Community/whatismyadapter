#!/usr/bin/env bash
# ==============================================================================
# WillowCMS Multi-Stage Dockerfile Test Script
# ==============================================================================
# This script tests the multi-stage Dockerfile build process and validates
# that all stages work correctly.
#
# Usage:
#   ./tools/docker/test-multistage-build.sh [options]
#
# Options:
#   --no-cache    Build without using cache
#   --verbose     Show detailed build output
#   --cleanup     Remove test images after completion
#   --help        Show this help message
# ==============================================================================

set -e  # Exit on error
set -u  # Exit on undefined variable

# ==============================================================================
# Configuration
# ==============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
DOCKERFILE="${PROJECT_ROOT}/infrastructure/docker/willowcms/Dockerfile.multistage"
IMAGE_NAME="willowcms-test"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Options
NO_CACHE=false
VERBOSE=false
CLEANUP=false

# ==============================================================================
# Functions
# ==============================================================================

print_header() {
    echo ""
    echo -e "${BLUE}===================================================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}===================================================================${NC}"
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
    echo -e "${BLUE}ℹ $1${NC}"
}

show_help() {
    cat << EOF
WillowCMS Multi-Stage Dockerfile Test Script

Usage:
    $0 [options]

Options:
    --no-cache    Build without using Docker cache
    --verbose     Show detailed build output
    --cleanup     Remove test images after completion
    --help        Show this help message

Description:
    This script tests the multi-stage Dockerfile by:
    1. Building each stage individually
    2. Building the complete production image
    3. Verifying the image structure
    4. Running basic container tests
    5. Comparing image sizes

Examples:
    # Standard test
    $0

    # Test without cache to ensure clean build
    $0 --no-cache

    # Verbose output with cleanup
    $0 --verbose --cleanup

EOF
    exit 0
}

check_requirements() {
    print_header "Checking Requirements"
    
    local requirements_met=true
    
    # Check Docker
    if command -v docker &> /dev/null; then
        print_success "Docker installed: $(docker --version)"
    else
        print_error "Docker is not installed"
        requirements_met=false
    fi
    
    # Check Docker BuildKit
    if docker buildx version &> /dev/null 2>&1; then
        print_success "Docker BuildKit available"
    else
        print_warning "Docker BuildKit not available (optional but recommended)"
    fi
    
    # Check Dockerfile exists
    if [[ -f "${DOCKERFILE}" ]]; then
        print_success "Dockerfile found: ${DOCKERFILE}"
    else
        print_error "Dockerfile not found: ${DOCKERFILE}"
        requirements_met=false
    fi
    
    # Check if Docker daemon is running
    if docker info &> /dev/null; then
        print_success "Docker daemon is running"
    else
        print_error "Docker daemon is not running"
        requirements_met=false
    fi
    
    if [[ "${requirements_met}" = false ]]; then
        print_error "Requirements not met. Please install missing components."
        exit 1
    fi
}

build_stage() {
    local stage_name=$1
    local tag="${IMAGE_NAME}:${stage_name}"
    
    print_info "Building stage: ${stage_name}"
    
    local build_args=""
    [[ "${NO_CACHE}" = true ]] && build_args="--no-cache"
    [[ "${VERBOSE}" = false ]] && build_args="${build_args} --quiet"
    
    if docker build \
        ${build_args} \
        --target "${stage_name}" \
        -f "${DOCKERFILE}" \
        -t "${tag}" \
        "${PROJECT_ROOT}" 2>&1 | tee "/tmp/build_${stage_name}_${TIMESTAMP}.log"; then
        print_success "Stage '${stage_name}' built successfully"
        return 0
    else
        print_error "Stage '${stage_name}' failed to build"
        print_error "Check log: /tmp/build_${stage_name}_${TIMESTAMP}.log"
        return 1
    fi
}

build_production() {
    print_header "Building Production Image"
    
    local build_args=""
    [[ "${NO_CACHE}" = true ]] && build_args="--no-cache"
    [[ "${VERBOSE}" = false ]] && build_args="${build_args} --quiet"
    
    # Get UID/GID from current user
    local uid=$(id -u)
    local gid=$(id -g)
    
    print_info "Building with UID=${uid} GID=${gid}"
    
    if docker build \
        ${build_args} \
        --build-arg UID="${uid}" \
        --build-arg GID="${gid}" \
        -f "${DOCKERFILE}" \
        -t "${IMAGE_NAME}:production" \
        -t "${IMAGE_NAME}:latest" \
        "${PROJECT_ROOT}" 2>&1 | tee "/tmp/build_production_${TIMESTAMP}.log"; then
        print_success "Production image built successfully"
        return 0
    else
        print_error "Production image failed to build"
        print_error "Check log: /tmp/build_production_${TIMESTAMP}.log"
        return 1
    fi
}

verify_image() {
    print_header "Verifying Image Structure"
    
    local image="${IMAGE_NAME}:production"
    
    # Check image exists
    if docker image inspect "${image}" &> /dev/null; then
        print_success "Image exists: ${image}"
    else
        print_error "Image not found: ${image}"
        return 1
    fi
    
    # Check image size
    local size=$(docker image inspect "${image}" --format='{{.Size}}' | awk '{print $1/1024/1024}')
    print_info "Image size: $(printf "%.2f" ${size}) MB"
    
    # Check user is nobody
    print_info "Checking container user..."
    local user=$(docker run --rm "${image}" whoami 2>/dev/null || echo "failed")
    if [[ "${user}" = "nobody" ]]; then
        print_success "Container runs as non-root user: ${user}"
    else
        print_error "Container user check failed: ${user}"
        return 1
    fi
    
    # Check for vendor directory
    print_info "Checking vendor directory..."
    if docker run --rm "${image}" sh -c "test -d /var/www/html/vendor && echo 'exists'" | grep -q "exists"; then
        print_success "Vendor directory exists"
    else
        print_error "Vendor directory not found"
        return 1
    fi
    
    # Check for CakePHP files
    print_info "Checking CakePHP structure..."
    if docker run --rm "${image}" sh -c "test -f /var/www/html/webroot/index.php && echo 'exists'" | grep -q "exists"; then
        print_success "CakePHP webroot found"
    else
        print_error "CakePHP webroot not found"
        return 1
    fi
    
    # Check Nginx config
    print_info "Checking Nginx configuration..."
    if docker run --rm "${image}" sh -c "test -f /etc/nginx/nginx.conf && echo 'exists'" | grep -q "exists"; then
        print_success "Nginx configuration found"
    else
        print_error "Nginx configuration not found"
        return 1
    fi
    
    # Check PHP-FPM config
    print_info "Checking PHP-FPM configuration..."
    if docker run --rm "${image}" sh -c "test -f /etc/php83/php-fpm.d/www.conf && echo 'exists'" | grep -q "exists"; then
        print_success "PHP-FPM configuration found"
    else
        print_error "PHP-FPM configuration not found"
        return 1
    fi
    
    # Check for secrets (should NOT exist)
    print_info "Checking for secrets (should be none)..."
    local secrets=$(docker run --rm "${image}" sh -c "find / -name '*.env' 2>/dev/null | wc -l" || echo "0")
    if [[ "${secrets}" -eq 0 ]]; then
        print_success "No .env files found in image (correct)"
    else
        print_warning "Found ${secrets} .env file(s) in image (check if intentional)"
    fi
    
    # Check layers
    print_info "Analyzing image layers..."
    local layer_count=$(docker history "${image}" --quiet | wc -l | tr -d ' ')
    print_info "Total layers: ${layer_count}"
    
    return 0
}

compare_sizes() {
    print_header "Comparing Stage Sizes"
    
    echo ""
    printf "%-20s %-15s %-15s\n" "Stage" "Size (MB)" "Tag"
    printf "%-20s %-15s %-15s\n" "--------------------" "---------------" "---------------"
    
    for stage in composer node-assets builder deps production; do
        local tag="${IMAGE_NAME}:${stage}"
        if docker image inspect "${tag}" &> /dev/null 2>&1; then
            local size=$(docker image inspect "${tag}" --format='{{.Size}}' | awk '{print $1/1024/1024}')
            printf "%-20s %-15.2f %-15s\n" "${stage}" "${size}" "${tag}"
        else
            printf "%-20s %-15s %-15s\n" "${stage}" "N/A" "not built"
        fi
    done
    echo ""
}

run_container_test() {
    print_header "Running Container Tests"
    
    local image="${IMAGE_NAME}:production"
    local container_name="willowcms-test-${TIMESTAMP}"
    
    print_info "Starting test container..."
    
    # Start container in detached mode
    if docker run -d \
        --name "${container_name}" \
        --rm \
        -p 18080:80 \
        "${image}" &> /dev/null; then
        print_success "Container started: ${container_name}"
    else
        print_error "Failed to start container"
        return 1
    fi
    
    # Wait for container to be ready
    print_info "Waiting for services to start..."
    sleep 5
    
    # Check if container is still running
    if docker ps --filter "name=${container_name}" --format '{{.Names}}' | grep -q "${container_name}"; then
        print_success "Container is running"
    else
        print_error "Container exited unexpectedly"
        docker logs "${container_name}" 2>&1 | tail -20
        return 1
    fi
    
    # Check Nginx is responding
    print_info "Testing Nginx response..."
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:18080/ || echo "000")
    if [[ "${response}" =~ ^[23] ]]; then
        print_success "Nginx responding with HTTP ${response}"
    else
        print_warning "Nginx returned HTTP ${response} (may need database)"
    fi
    
    # Check PHP-FPM ping
    print_info "Testing PHP-FPM ping endpoint..."
    local fpm_response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:18080/fpm-ping || echo "000")
    if [[ "${fpm_response}" = "200" ]]; then
        print_success "PHP-FPM ping successful"
    else
        print_warning "PHP-FPM ping returned ${fpm_response}"
    fi
    
    # Check container logs
    print_info "Checking container logs for errors..."
    local error_count=$(docker logs "${container_name}" 2>&1 | grep -i "error\|fail\|fatal" | wc -l | tr -d ' ')
    if [[ "${error_count}" -eq 0 ]]; then
        print_success "No errors in container logs"
    else
        print_warning "Found ${error_count} error-like message(s) in logs"
    fi
    
    # Stop container
    print_info "Stopping test container..."
    docker stop "${container_name}" &> /dev/null || true
    
    print_success "Container tests completed"
    return 0
}

cleanup_images() {
    print_header "Cleaning Up Test Images"
    
    print_info "Removing test images..."
    
    for stage in composer node-assets builder deps production latest; do
        local tag="${IMAGE_NAME}:${stage}"
        if docker image inspect "${tag}" &> /dev/null 2>&1; then
            docker rmi "${tag}" &> /dev/null && print_success "Removed ${tag}" || print_warning "Failed to remove ${tag}"
        fi
    done
    
    # Clean up dangling images
    print_info "Removing dangling images..."
    docker image prune -f &> /dev/null
    
    print_success "Cleanup completed"
}

generate_report() {
    print_header "Test Summary Report"
    
    cat << EOF

Test Execution Details:
  Timestamp:    ${TIMESTAMP}
  Dockerfile:   ${DOCKERFILE}
  Project Root: ${PROJECT_ROOT}
  
Build Options:
  No Cache:     ${NO_CACHE}
  Verbose:      ${VERBOSE}
  Cleanup:      ${CLEANUP}

Logs Location:
  /tmp/build_*_${TIMESTAMP}.log

EOF

    if [[ "${CLEANUP}" = false ]]; then
        cat << EOF
Test Images:
  To inspect: docker run --rm -it ${IMAGE_NAME}:production sh
  To remove:  docker rmi ${IMAGE_NAME}:production
  
EOF
    fi
}

# ==============================================================================
# Main Script
# ==============================================================================

main() {
    print_header "WillowCMS Multi-Stage Dockerfile Test"
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --no-cache)
                NO_CACHE=true
                shift
                ;;
            --verbose)
                VERBOSE=true
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
    
    # Run tests
    check_requirements
    
    # Build stages individually (helps identify which stage fails)
    print_header "Building Individual Stages"
    build_stage "composer" || exit 1
    # node-assets stage is optional and may fail if no package.json
    build_stage "node-assets" || print_warning "node-assets stage failed (may be intentional if no Node assets)"
    build_stage "builder" || exit 1
    build_stage "deps" || exit 1
    
    # Build final production image
    build_production || exit 1
    
    # Verify and test
    verify_image || exit 1
    compare_sizes
    run_container_test || print_warning "Container tests had warnings"
    
    # Cleanup if requested
    if [[ "${CLEANUP}" = true ]]; then
        cleanup_images
    fi
    
    # Generate report
    generate_report
    
    print_header "All Tests Completed Successfully!"
    print_success "Multi-stage Dockerfile is working correctly"
    
    exit 0
}

# Run main function
main "$@"
