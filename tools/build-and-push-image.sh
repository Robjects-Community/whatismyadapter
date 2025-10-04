#!/usr/bin/env bash
# =============================================================================
# Build and Push Docker Image to GHCR
# =============================================================================
# This script builds the WhatIsMyAdapter image and pushes it to GitHub Container Registry
#
# Prerequisites:
# - Docker installed and running
# - Authenticated to GHCR: docker login ghcr.io
# - GitHub Personal Access Token with packages:write permission
#
# Usage:
#   ./tools/build-and-push-image.sh [TAG]
#
# Example:
#   ./tools/build-and-push-image.sh pre-willowcms-beta
#   ./tools/build-and-push-image.sh latest
# =============================================================================

set -euo pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
REGISTRY="ghcr.io"
NAMESPACE="robjects-community"
IMAGE_NAME="whatismyadapter_cms"
DEFAULT_TAG="pre-willowcms-beta"

# Get tag from argument or use default
TAG="${1:-$DEFAULT_TAG}"

# Full image name
FULL_IMAGE="${REGISTRY}/${NAMESPACE}/${IMAGE_NAME}:${TAG}"

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}Build and Push Docker Image${NC}"
echo -e "${BLUE}==============================================================================${NC}"
echo ""
echo -e "${GREEN}Image:${NC} ${FULL_IMAGE}"
echo -e "${GREEN}Tag:${NC} ${TAG}"
echo ""

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo -e "${RED}ERROR: Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

# Check if authenticated to GHCR
echo -e "${YELLOW}Checking GHCR authentication...${NC}"
if ! docker login ghcr.io --password-stdin < /dev/null 2>&1 | grep -q "Login Succeeded"; then
    echo -e "${YELLOW}Not authenticated to GHCR. Please login first:${NC}"
    echo ""
    echo "  docker login ghcr.io -u YOUR_GITHUB_USERNAME"
    echo ""
    echo "When prompted for password, use a GitHub Personal Access Token with 'packages:write' scope"
    echo "Create token at: https://github.com/settings/tokens/new"
    echo ""
    read -p "Press Enter to continue after logging in, or Ctrl+C to exit..."
fi

# Get UID and GID for the build
HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo -e "${BLUE}Building image with UID:GID ${HOST_UID}:${HOST_GID}...${NC}"

# Build the image
echo -e "${YELLOW}Step 1/3: Building Docker image...${NC}"
if docker build \
    --platform linux/amd64,linux/arm64 \
    --build-arg UID=${HOST_UID} \
    --build-arg GID=${HOST_GID} \
    -t ${FULL_IMAGE} \
    -f infrastructure/docker/willowcms/Dockerfile \
    .; then
    echo -e "${GREEN}✓ Build completed successfully${NC}"
else
    echo -e "${RED}✗ Build failed${NC}"
    exit 1
fi

# Tag as latest if building a specific version
if [[ "${TAG}" != "latest" ]]; then
    LATEST_IMAGE="${REGISTRY}/${NAMESPACE}/${IMAGE_NAME}:latest"
    echo -e "${YELLOW}Step 2/3: Tagging as latest...${NC}"
    docker tag ${FULL_IMAGE} ${LATEST_IMAGE}
    echo -e "${GREEN}✓ Tagged as ${LATEST_IMAGE}${NC}"
else
    echo -e "${YELLOW}Step 2/3: Skipping latest tag (already latest)${NC}"
fi

# Push the image
echo -e "${YELLOW}Step 3/3: Pushing to registry...${NC}"
if docker push ${FULL_IMAGE}; then
    echo -e "${GREEN}✓ Pushed ${FULL_IMAGE}${NC}"
else
    echo -e "${RED}✗ Push failed${NC}"
    exit 1
fi

# Push latest tag if we created it
if [[ "${TAG}" != "latest" ]]; then
    if docker push ${LATEST_IMAGE}; then
        echo -e "${GREEN}✓ Pushed ${LATEST_IMAGE}${NC}"
    else
        echo -e "${YELLOW}⚠ Failed to push latest tag${NC}"
    fi
fi

echo ""
echo -e "${GREEN}==============================================================================${NC}"
echo -e "${GREEN}✓ Image build and push completed successfully!${NC}"
echo -e "${GREEN}==============================================================================${NC}"
echo ""
echo -e "${BLUE}Image Details:${NC}"
echo "  Registry: ${REGISTRY}"
echo "  Repository: ${NAMESPACE}/${IMAGE_NAME}"
echo "  Tag: ${TAG}"
echo "  Full Image: ${FULL_IMAGE}"
echo ""
echo -e "${BLUE}To verify:${NC}"
echo "  docker pull ${FULL_IMAGE}"
echo "  docker inspect ${FULL_IMAGE}"
echo ""
echo -e "${BLUE}To use in Portainer:${NC}"
echo "  Set WILLOWCMS_IMAGE=${FULL_IMAGE} in your stack.env.cloud"
echo ""
echo -e "${GREEN}Ready for deployment! 🚀${NC}"
