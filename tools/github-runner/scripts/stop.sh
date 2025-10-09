#!/bin/bash
# Stop GitHub Actions Runner Container
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Stopping GitHub Actions Runner${NC}"

# Check if container exists
if ! docker ps -a | grep -q "github-runner"; then
    echo -e "${YELLOW}Runner container does not exist${NC}"
    exit 0
fi

# Check if container is running
if docker ps | grep -q "github-runner"; then
    echo "Stopping runner container..."
    docker stop github-runner
    echo -e "${GREEN}✓ Runner stopped${NC}"
else
    echo -e "${YELLOW}Runner container is not running${NC}"
fi

# Ask if user wants to remove the container
read -p "Do you want to remove the container? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Removing runner container..."
    docker rm github-runner
    echo -e "${GREEN}✓ Runner container removed${NC}"
fi

echo -e "${GREEN}Done!${NC}"