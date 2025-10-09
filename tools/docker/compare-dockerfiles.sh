#!/usr/bin/env bash
# ==============================================================================
# Dockerfile Comparison Script
# ==============================================================================
# This script compares the original single-stage Dockerfile with the new
# multi-stage Dockerfile in terms of:
#   - Build time
#   - Final image size
#   - Layer count
#   - Security (presence of dev dependencies, secrets)
#
# Usage:
#   ./tools/docker/compare-dockerfiles.sh [options]
#
# Options:
#   --no-cache       Build without cache to get accurate timing
#   --iterations N   Number of build iterations for averaging (default: 1)
#   --export         Export results to CSV file
#   --help           Show this help message
# ==============================================================================

set -e
set -u

# ==============================================================================
# Configuration
# ==============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

SINGLE_STAGE_DOCKERFILE="${PROJECT_ROOT}/infrastructure/docker/willowcms/Dockerfile"
MULTI_STAGE_DOCKERFILE="${PROJECT_ROOT}/infrastructure/docker/willowcms/Dockerfile.multistage"

SINGLE_STAGE_IMAGE="willowcms:single-stage"
MULTI_STAGE_IMAGE="willowcms:multi-stage"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Options
NO_CACHE=false
ITERATIONS=1
EXPORT=false

# ==============================================================================
# Functions
# ==============================================================================

print_header() {
    echo ""
    echo -e "${BOLD}${CYAN}=================================================================${NC}"
    echo -e "${BOLD}${CYAN}  $1${NC}"
    echo -e "${BOLD}${CYAN}=================================================================${NC}"
    echo ""
}

print_section() {
    echo ""
    echo -e "${BOLD}${BLUE}─── $1 ───${NC}"
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
Dockerfile Comparison Script

Usage:
    $0 [options]

Options:
    --no-cache        Build without Docker cache for accurate timing
    --iterations N    Number of build iterations to average (default: 1)
    --export          Export results to CSV file
    --help            Show this help message

Description:
    Compares single-stage and multi-stage Dockerfiles on:
    - Build time (clean and incremental)
    - Final image size
    - Layer count and efficiency
    - Security posture (dev deps, secrets)

Examples:
    # Quick comparison
    $0

    # Detailed comparison with 3 iterations
    $0 --no-cache --iterations 3

    # Export results to CSV
    $0 --export

EOF
    exit 0
}

check_dockerfiles() {
    print_section "Checking Dockerfiles"
    
    if [[ -f "${SINGLE_STAGE_DOCKERFILE}" ]]; then
        print_success "Single-stage Dockerfile found"
    else
        print_error "Single-stage Dockerfile not found: ${SINGLE_STAGE_DOCKERFILE}"
        exit 1
    fi
    
    if [[ -f "${MULTI_STAGE_DOCKERFILE}" ]]; then
        print_success "Multi-stage Dockerfile found"
    else
        print_error "Multi-stage Dockerfile not found: ${MULTI_STAGE_DOCKERFILE}"
        exit 1
    fi
}

build_single_stage() {
    local iteration=$1
    print_info "Building single-stage (iteration ${iteration})..."
    
    local build_args=""
    [[ "${NO_CACHE}" = true ]] && build_args="--no-cache"
    
    local start_time=$(date +%s)
    
    docker build \
        ${build_args} \
        --quiet \
        -f "${SINGLE_STAGE_DOCKERFILE}" \
        -t "${SINGLE_STAGE_IMAGE}" \
        "${PROJECT_ROOT}" > /dev/null 2>&1
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    echo "${duration}"
}

build_multi_stage() {
    local iteration=$1
    print_info "Building multi-stage (iteration ${iteration})..."
    
    local build_args=""
    [[ "${NO_CACHE}" = true ]] && build_args="--no-cache"
    
    local start_time=$(date +%s)
    
    docker build \
        ${build_args} \
        --quiet \
        -f "${MULTI_STAGE_DOCKERFILE}" \
        -t "${MULTI_STAGE_IMAGE}" \
        "${PROJECT_ROOT}" > /dev/null 2>&1
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    echo "${duration}"
}

get_image_size() {
    local image=$1
    docker image inspect "${image}" --format='{{.Size}}' 2>/dev/null || echo "0"
}

get_layer_count() {
    local image=$1
    docker history "${image}" --quiet 2>/dev/null | wc -l | tr -d ' '
}

check_dev_dependencies() {
    local image=$1
    # Check for common dev tools that shouldn't be in production
    local dev_tools=("git" "gcc" "make" "build-base")
    local found=0
    
    for tool in "${dev_tools[@]}"; do
        if docker run --rm "${image}" sh -c "command -v ${tool}" &> /dev/null; then
            ((found++))
        fi
    done
    
    echo "${found}"
}

check_secrets() {
    local image=$1
    docker run --rm "${image}" sh -c "find / -name '*.env' 2>/dev/null | wc -l" 2>/dev/null || echo "0"
}

format_size() {
    local bytes=$1
    local mb=$(echo "scale=2; ${bytes} / 1024 / 1024" | bc)
    echo "${mb}"
}

format_time() {
    local seconds=$1
    local minutes=$((seconds / 60))
    local remaining_seconds=$((seconds % 60))
    
    if [[ ${minutes} -gt 0 ]]; then
        echo "${minutes}m ${remaining_seconds}s"
    else
        echo "${seconds}s"
    fi
}

calculate_percentage() {
    local value=$1
    local total=$2
    
    if [[ "${total}" -eq 0 ]]; then
        echo "N/A"
    else
        local percent=$(echo "scale=2; (${value} * 100) / ${total}" | bc)
        echo "${percent}"
    fi
}

run_comparison() {
    print_header "Building and Comparing Dockerfiles"
    
    # Arrays to store timing data
    declare -a single_times
    declare -a multi_times
    
    # Build both images multiple times
    print_section "Build Time Comparison"
    
    for ((i=1; i<=ITERATIONS; i++)); do
        print_info "Iteration ${i} of ${ITERATIONS}"
        
        # Build single-stage
        local single_time=$(build_single_stage ${i})
        single_times+=("${single_time}")
        print_info "Single-stage: $(format_time ${single_time})"
        
        # Build multi-stage
        local multi_time=$(build_multi_stage ${i})
        multi_times+=("${multi_time}")
        print_info "Multi-stage: $(format_time ${multi_time})"
        
        echo ""
    done
    
    # Calculate average times
    local single_total=0
    for time in "${single_times[@]}"; do
        ((single_total += time))
    done
    local single_avg=$((single_total / ITERATIONS))
    
    local multi_total=0
    for time in "${multi_times[@]}"; do
        ((multi_total += time))
    done
    local multi_avg=$((multi_total / ITERATIONS))
    
    # Get image sizes
    print_section "Image Size Comparison"
    
    local single_size=$(get_image_size "${SINGLE_STAGE_IMAGE}")
    local multi_size=$(get_image_size "${MULTI_STAGE_IMAGE}")
    
    local single_size_mb=$(format_size ${single_size})
    local multi_size_mb=$(format_size ${multi_size})
    
    print_info "Single-stage: ${single_size_mb} MB"
    print_info "Multi-stage:  ${multi_size_mb} MB"
    
    # Get layer counts
    print_section "Layer Analysis"
    
    local single_layers=$(get_layer_count "${SINGLE_STAGE_IMAGE}")
    local multi_layers=$(get_layer_count "${MULTI_STAGE_IMAGE}")
    
    print_info "Single-stage: ${single_layers} layers"
    print_info "Multi-stage:  ${multi_layers} layers"
    
    # Security checks
    print_section "Security Analysis"
    
    print_info "Checking for dev tools..."
    local single_dev=$(check_dev_dependencies "${SINGLE_STAGE_IMAGE}")
    local multi_dev=$(check_dev_dependencies "${MULTI_STAGE_IMAGE}")
    
    print_info "Single-stage: ${single_dev} dev tool(s) found"
    print_info "Multi-stage:  ${multi_dev} dev tool(s) found"
    
    print_info "Checking for secrets..."
    local single_secrets=$(check_secrets "${SINGLE_STAGE_IMAGE}")
    local multi_secrets=$(check_secrets "${MULTI_STAGE_IMAGE}")
    
    print_info "Single-stage: ${single_secrets} .env file(s) found"
    print_info "Multi-stage:  ${multi_secrets} .env file(s) found"
    
    # Calculate improvements
    print_header "Comparison Results"
    
    # Time comparison
    local time_diff=$((single_avg - multi_avg))
    local time_improvement=$(calculate_percentage ${time_diff} ${single_avg})
    
    # Size comparison
    local size_diff=$((single_size - multi_size))
    local size_diff_mb=$(format_size ${size_diff})
    local size_improvement=$(calculate_percentage ${size_diff} ${single_size})
    
    # Generate report
    cat << EOF

╔════════════════════════════════════════════════════════════════════╗
║                       COMPARISON SUMMARY                           ║
╚════════════════════════════════════════════════════════════════════╝

┌─ Build Time ───────────────────────────────────────────────────────┐
│ Single-Stage:  $(printf "%-15s" "$(format_time ${single_avg})")                                   │
│ Multi-Stage:   $(printf "%-15s" "$(format_time ${multi_avg})")                                   │
│ Difference:    $(printf "%-15s" "$(format_time ${time_diff}) (${time_improvement}%)")                      │
└────────────────────────────────────────────────────────────────────┘

┌─ Image Size ───────────────────────────────────────────────────────┐
│ Single-Stage:  $(printf "%-15s" "${single_size_mb} MB")                               │
│ Multi-Stage:   $(printf "%-15s" "${multi_size_mb} MB")                               │
│ Reduction:     $(printf "%-15s" "${size_diff_mb} MB (${size_improvement}%)")                      │
└────────────────────────────────────────────────────────────────────┘

┌─ Layer Count ──────────────────────────────────────────────────────┐
│ Single-Stage:  $(printf "%-15s" "${single_layers} layers")                              │
│ Multi-Stage:   $(printf "%-15s" "${multi_layers} layers")                              │
└────────────────────────────────────────────────────────────────────┘

┌─ Security Analysis ────────────────────────────────────────────────┐
│ Dev Tools Found:                                                   │
│   Single-Stage:  ${single_dev}                                                      │
│   Multi-Stage:   ${multi_dev}                                                      │
│                                                                    │
│ Secrets Found (.env files):                                       │
│   Single-Stage:  ${single_secrets}                                                      │
│   Multi-Stage:   ${multi_secrets}                                                      │
└────────────────────────────────────────────────────────────────────┘

EOF

    # Recommendations
    print_header "Recommendations"
    
    if [[ $(echo "${size_improvement} > 10" | bc) -eq 1 ]]; then
        print_success "Multi-stage provides significant size reduction (${size_improvement}%)"
    else
        print_warning "Size difference is minimal (${size_improvement}%)"
    fi
    
    if [[ ${multi_dev} -lt ${single_dev} ]]; then
        print_success "Multi-stage has fewer dev dependencies (better security)"
    elif [[ ${multi_dev} -eq 0 ]]; then
        print_success "Multi-stage has no dev dependencies (excellent)"
    else
        print_warning "Multi-stage still contains dev dependencies"
    fi
    
    if [[ ${multi_secrets} -eq 0 ]]; then
        print_success "No secrets found in multi-stage image (correct)"
    else
        print_error "Secrets found in multi-stage image (security risk)"
    fi
    
    # Export to CSV if requested
    if [[ "${EXPORT}" = true ]]; then
        export_results "${single_avg}" "${multi_avg}" "${single_size}" "${multi_size}" \
                      "${single_layers}" "${multi_layers}" "${single_dev}" "${multi_dev}" \
                      "${single_secrets}" "${multi_secrets}"
    fi
}

export_results() {
    local single_time=$1
    local multi_time=$2
    local single_size=$3
    local multi_size=$4
    local single_layers=$5
    local multi_layers=$6
    local single_dev=$7
    local multi_dev=$8
    local single_secrets=$9
    local multi_secrets=${10}
    
    local csv_file="${PROJECT_ROOT}/docker-comparison-${TIMESTAMP}.csv"
    
    print_section "Exporting Results"
    
    cat > "${csv_file}" << EOF
Metric,Single-Stage,Multi-Stage,Improvement
Build Time (seconds),${single_time},${multi_time},$((single_time - multi_time))
Image Size (bytes),${single_size},${multi_size},$((single_size - multi_size))
Image Size (MB),$(format_size ${single_size}),$(format_size ${multi_size}),$(format_size $((single_size - multi_size)))
Layer Count,${single_layers},${multi_layers},$((single_layers - multi_layers))
Dev Tools Found,${single_dev},${multi_dev},$((single_dev - multi_dev))
Secrets Found,${single_secrets},${multi_secrets},$((single_secrets - multi_secrets))
Timestamp,${TIMESTAMP},${TIMESTAMP},
EOF
    
    print_success "Results exported to: ${csv_file}"
}

cleanup() {
    print_section "Cleanup"
    
    print_info "Removing comparison images..."
    docker rmi "${SINGLE_STAGE_IMAGE}" &> /dev/null || true
    docker rmi "${MULTI_STAGE_IMAGE}" &> /dev/null || true
    
    print_success "Cleanup completed"
}

# ==============================================================================
# Main Script
# ==============================================================================

main() {
    print_header "Dockerfile Comparison Analysis"
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --no-cache)
                NO_CACHE=true
                shift
                ;;
            --iterations)
                ITERATIONS="$2"
                shift 2
                ;;
            --export)
                EXPORT=true
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
    
    # Validate iterations
    if ! [[ "${ITERATIONS}" =~ ^[0-9]+$ ]] || [[ "${ITERATIONS}" -lt 1 ]]; then
        print_error "Invalid iterations value: ${ITERATIONS}"
        exit 1
    fi
    
    # Check requirements
    check_dockerfiles
    
    # Run comparison
    run_comparison
    
    # Cleanup
    cleanup
    
    print_header "Comparison Complete!"
    
    exit 0
}

# Trap cleanup on exit
trap cleanup EXIT

# Run main function
main "$@"
