#!/usr/bin/env bash
# ==============================================================================
# WillowCMS Docker Build Script
# ==============================================================================
# This script builds the WillowCMS Docker image using the multi-stage
# Dockerfile with support for multi-architecture builds.
#
# Usage:
#   ./tools/docker/build-willowcms.sh [options]
#
# Options:
#   --multi-arch     Build for multiple architectures (amd64, arm64)
#   --push           Push image to registry after build
#   --no-cache       Build without using Docker cache
#   --tag TAG        Custom tag (default from .env or 'latest')
#   --help           Show this help message
#
# Environment Variables (from .env):
#   IMAGE_NAME       Docker image name
#   IMAGE_TAG        Docker image tag
#   DOCKER_REGISTRY  Docker registry URL (optional)
#   DOCKER_UID       User ID for nobody user
#   DOCKER_GID       Group ID for nobody group
# ==============================================================================

set -e
set -u

# ==============================================================================
# Configuration
# ==============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
ENV_FILE="${PROJECT_ROOT}/.env"
DOCKERFILE="${PROJECT_ROOT}/infrastructure/docker/willowcms/Dockerfile.multistage"

# Default values
DEFAULT_IMAGE_NAME="willowcms"
DEFAULT_IMAGE_TAG="latest"
DEFAULT_PLATFORMS="linux/arm64/v8"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

# Build options
MULTI_ARCH=false
PUSH=false
NO_CACHE=false
CUSTOM_TAG=""

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
WillowCMS Docker Build Script

Usage:
    $0 [options]

Options:
    --multi-arch     Build for multiple architectures (linux/amd64, linux/arm64/v8)
    --push           Push image to registry after building
    --no-cache       Build without using Docker cache
    --tag TAG        Override image tag (default: from .env or 'latest')
    --help           Show this help message

Environment Variables:
    The script reads configuration from .env file:
    - IMAGE_NAME       Docker image name (default: willowcms)
    - IMAGE_TAG        Docker image tag (default: latest)
    - DOCKER_REGISTRY  Registry URL (optional, for push)
    - DOCKER_UID       User ID for container user (default: 1000)
    - DOCKER_GID       Group ID for container user (default: 1000)

Examples:
    # Basic build for current architecture
    $0

    # Build for multiple architectures and push
    $0 --multi-arch --push

    # Clean build with custom tag
    $0 --no-cache --tag v1.0.0

    # Build and push with custom tag
    $0 --multi-arch --push --tag production

EOF
    exit 0
}

load_env() {
    print_info "Loading environment variables..."
    
    if [[ -f "${ENV_FILE}" ]]; then
        # Source .env file and export variables
        set -a
        source "${ENV_FILE}"
        set +a
        print_success "Environment loaded from ${ENV_FILE}"
    else
        print_warning ".env file not found, using default values"
    fi
    
    # Set defaults if not provided
    IMAGE_NAME="${IMAGE_NAME:-$DEFAULT_IMAGE_NAME}"
    IMAGE_TAG="${IMAGE_TAG:-$DEFAULT_IMAGE_TAG}"
    DOCKER_UID="${DOCKER_UID:-1000}"
    DOCKER_GID="${DOCKER_GID:-1000}"
    
    # Override tag if custom tag provided
    [[ -n "${CUSTOM_TAG}" ]] && IMAGE_TAG="${CUSTOM_TAG}"
    
    # Construct full image name
    if [[ -n "${DOCKER_REGISTRY:-}" ]]; then
        FULL_IMAGE_NAME="${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}"
    else
        FULL_IMAGE_NAME="${IMAGE_NAME}:${IMAGE_TAG}"
    fi
    
    print_info "Image name: ${FULL_IMAGE_NAME}"
    print_info "UID/GID: ${DOCKER_UID}/${DOCKER_GID}"
}

check_requirements() {
    print_info "Checking requirements..."
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed"
        exit 1
    fi
    print_success "Docker: $(docker --version)"
    
    # Check Docker daemon
    if ! docker info &> /dev/null; then
        print_error "Docker daemon is not running"
        exit 1
    fi
    
    # Check Dockerfile exists
    if [[ ! -f "${DOCKERFILE}" ]]; then
        print_error "Dockerfile not found: ${DOCKERFILE}"
        exit 1
    fi
    print_success "Dockerfile: ${DOCKERFILE}"
    
    # Check BuildKit for multi-arch
    if [[ "${MULTI_ARCH}" = true ]]; then
        if ! docker buildx version &> /dev/null 2>&1; then
            print_error "Docker BuildKit (buildx) is required for multi-arch builds"
            exit 1
        fi
        print_success "Docker BuildKit available"
    fi
}

setup_buildx() {
    if [[ "${MULTI_ARCH}" = true ]]; then
        print_info "Setting up BuildKit for multi-architecture build..."
        
        # Create builder if it doesn't exist
        if ! docker buildx inspect willowbuilder &> /dev/null; then
            print_info "Creating BuildKit builder instance..."
            docker buildx create --name willowbuilder --use
            print_success "Builder 'willowbuilder' created"
        else
            print_info "Using existing builder 'willowbuilder'"
            docker buildx use willowbuilder
        fi
        
        # Bootstrap builder
        docker buildx inspect --bootstrap
        print_success "Builder ready"
    fi
}

build_image() {
    print_header "Building Docker Image"
    
    # Build arguments
    local build_args=(
        "--build-arg" "UID=${DOCKER_UID}"
        "--build-arg" "GID=${DOCKER_GID}"
        "-f" "${DOCKERFILE}"
        "-t" "${FULL_IMAGE_NAME}"
    )
    
    # Add no-cache if requested
    [[ "${NO_CACHE}" = true ]] && build_args+=("--no-cache")
    
    # Build command depends on architecture options
    if [[ "${MULTI_ARCH}" = true ]]; then
        print_info "Building multi-architecture image..."
        
        # Multi-arch platforms
        build_args+=(
            "--platform" "linux/amd64,linux/arm64/v8"
        )
        
        # Add push if requested
        if [[ "${PUSH}" = true ]]; then
            build_args+=("--push")
            print_info "Image will be pushed to registry"
        else
            # Use load for single platform or output type
            build_args+=("--load")
        fi
        
        # Execute buildx build
        docker buildx build "${build_args[@]}" "${PROJECT_ROOT}"
        
    else
        print_info "Building single-architecture image..."
        
        # Add platform for current architecture
        build_args+=("--platform" "${DEFAULT_PLATFORMS}")
        
        # Execute standard build
        docker build "${build_args[@]}" "${PROJECT_ROOT}"
    fi
    
    print_success "Image built: ${FULL_IMAGE_NAME}"
}

push_image() {
    if [[ "${PUSH}" = true ]] && [[ "${MULTI_ARCH}" = false ]]; then
        print_header "Pushing Image to Registry"
        
        if [[ -z "${DOCKER_REGISTRY:-}" ]]; then
            print_error "DOCKER_REGISTRY not set in .env file"
            print_error "Cannot push without registry configuration"
            exit 1
        fi
        
        print_info "Pushing ${FULL_IMAGE_NAME}..."
        docker push "${FULL_IMAGE_NAME}"
        print_success "Image pushed successfully"
    elif [[ "${PUSH}" = true ]] && [[ "${MULTI_ARCH}" = true ]]; then
        print_success "Image already pushed (multi-arch build with --push)"
    fi
}

verify_image() {
    if [[ "${MULTI_ARCH}" = false ]]; then
        print_header "Verifying Image"
        
        # Check image exists
        if docker image inspect "${FULL_IMAGE_NAME}" &> /dev/null; then
            print_success "Image exists locally"
            
            # Get image size
            local size=$(docker image inspect "${FULL_IMAGE_NAME}" --format='{{.Size}}' | awk '{print $1/1024/1024}')
            print_info "Image size: $(printf "%.2f" ${size}) MB"
            
            # Get layer count
            local layers=$(docker history "${FULL_IMAGE_NAME}" --quiet | wc -l | tr -d ' ')
            print_info "Layers: ${layers}"
            
        else
            print_warning "Image not found locally (may have been pushed only)"
        fi
    fi
}

generate_summary() {
    print_header "Build Summary"
    
    cat << EOF

Build Configuration:
  Image:         ${FULL_IMAGE_NAME}
  Dockerfile:    ${DOCKERFILE}
  Multi-Arch:    ${MULTI_ARCH}
  Push:          ${PUSH}
  No Cache:      ${NO_CACHE}
  UID/GID:       ${DOCKER_UID}/${DOCKER_GID}

Next Steps:
  # Run the image locally
  docker run -d -p 8080:80 ${FULL_IMAGE_NAME}
  
  # Test with docker compose
  docker compose up -d
  
  # Inspect the image
  docker run --rm -it ${FULL_IMAGE_NAME} sh
  
EOF

    if [[ "${PUSH}" = false ]]; then
        cat << EOF
  # Push to registry (if needed)
  docker push ${FULL_IMAGE_NAME}

EOF
    fi
}

# ==============================================================================
# Main Script
# ==============================================================================

main() {
    print_header "WillowCMS Docker Build"
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --multi-arch)
                MULTI_ARCH=true
                shift
                ;;
            --push)
                PUSH=true
                shift
                ;;
            --no-cache)
                NO_CACHE=true
                shift
                ;;
            --tag)
                CUSTOM_TAG="$2"
                shift 2
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
    
    # Execute build process
    load_env
    check_requirements
    setup_buildx
    build_image
    push_image
    verify_image
    generate_summary
    
    print_header "Build Complete!"
    print_success "Docker image built successfully"
    
    exit 0
}

# Run main function
main "$@"
