#!/bin/bash

# deploy-swarm.sh - Build and deploy WillowCMS to Docker Swarm
# This script handles building the image and deploying the stack

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Load environment variables
if [ -f config/.env ]; then
    set -a
    source config/.env
    set +a
    echo -e "${GREEN}✓ Loaded environment variables from config/.env${NC}"
else
    echo -e "${RED}✗ Error: config/.env file not found${NC}"
    exit 1
fi

# Set default values if not in .env
WILLOWCMS_IMAGE=${WILLOWCMS_IMAGE:-willowcms}
TAG=${TAG:-latest}
STACK_NAME=${STACK_NAME:-willowcms-swarm-test}
DOCKER_UID=${DOCKER_UID:-1034}
DOCKER_GID=${DOCKER_GID:-100}

echo -e "${YELLOW}Building Docker image: ${WILLOWCMS_IMAGE}:${TAG}${NC}"

# Build the image with the correct tag
docker build \
    --build-arg UID=${DOCKER_UID} \
    --build-arg GID=${DOCKER_GID} \
    -t ${WILLOWCMS_IMAGE}:${TAG} \
    -f infrastructure/docker/willowcms/Dockerfile \
    .

echo -e "${GREEN}✓ Image built successfully${NC}"

# Initialize swarm if not already initialized
if ! docker info | grep -q "Swarm: active"; then
    echo -e "${YELLOW}Initializing Docker Swarm...${NC}"
    docker swarm init
    echo -e "${GREEN}✓ Docker Swarm initialized${NC}"
else
    echo -e "${GREEN}✓ Docker Swarm already active${NC}"
fi

# Deploy the stack
echo -e "${YELLOW}Deploying stack: ${STACK_NAME}${NC}"
docker stack deploy \
    --compose-file docker-compose.yml \
    ${STACK_NAME}

echo -e "${GREEN}✓ Stack deployed successfully${NC}"
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "  View services:  docker stack services ${STACK_NAME}"
echo "  View logs:      docker service logs ${STACK_NAME}_willowcms"
echo "  Remove stack:   docker stack rm ${STACK_NAME}"
