#!/bin/bash
# Start GitHub Actions Runner Container
set -e

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
RUNNER_DIR="$(dirname "$SCRIPT_DIR")"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Load environment configuration
if [ -f "$RUNNER_DIR/.env.runner" ]; then
    echo -e "${GREEN}Loading configuration from .env.runner${NC}"
    export $(cat "$RUNNER_DIR/.env.runner" | grep -v '^#' | xargs)
else
    echo -e "${RED}Error: .env.runner file not found!${NC}"
    echo "Please create $RUNNER_DIR/.env.runner from the template"
    exit 1
fi

# Validate required variables
REQUIRED_VARS=("GITHUB_TOKEN" "GITHUB_OWNER" "GITHUB_REPOSITORY" "RUNNER_NAME")
for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        echo -e "${RED}Error: $var is not set in .env.runner${NC}"
        exit 1
    fi
done

# Set defaults
RUNNER_NAME="${RUNNER_NAME:-willowcms-runner}"
RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,x64,willowcms}"
PLATFORM="${PLATFORM:-linux/amd64}"

echo -e "${GREEN}Starting GitHub Actions Runner${NC}"
echo "Runner Name: $RUNNER_NAME"
echo "Repository: $GITHUB_OWNER/$GITHUB_REPOSITORY"
echo "Labels: $RUNNER_LABELS"
echo "Platform: $PLATFORM"

# Check if runner is already running
if docker ps | grep -q "github-runner"; then
    echo -e "${YELLOW}Warning: Runner container is already running${NC}"
    echo "Use ./stop.sh to stop it first if you want to restart"
    exit 1
fi

# Build the runner image if it doesn't exist
echo -e "${GREEN}Building runner image...${NC}"
docker build \
    --platform "$PLATFORM" \
    -t willowcms-github-runner:latest \
    -f "$RUNNER_DIR/Dockerfile" \
    "$RUNNER_DIR"

# Start the runner container
echo -e "${GREEN}Starting runner container...${NC}"
docker run -d \
    --name github-runner \
    --platform "$PLATFORM" \
    --restart unless-stopped \
    --network willow_default \
    -e GITHUB_TOKEN="$GITHUB_TOKEN" \
    -e GITHUB_OWNER="$GITHUB_OWNER" \
    -e GITHUB_REPOSITORY="$GITHUB_REPOSITORY" \
    -e RUNNER_NAME="$RUNNER_NAME" \
    -e RUNNER_LABELS="$RUNNER_LABELS" \
    -e RUNNER_EPHEMERAL="${RUNNER_EPHEMERAL:-false}" \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v "$RUNNER_DIR/workspace:/home/runner/work" \
    -v "$RUNNER_DIR/logs:/home/runner/logs" \
    willowcms-github-runner:latest

# Wait for container to start
echo -e "${GREEN}Waiting for container to start...${NC}"
sleep 5

# Check container status
if docker ps | grep -q "github-runner"; then
    echo -e "${GREEN}✓ Runner container started successfully${NC}"
    echo ""
    echo "To view logs: docker logs -f github-runner"
    echo "To stop: ./stop.sh"
    echo "To check status: ./status.sh"
else
    echo -e "${RED}✗ Failed to start runner container${NC}"
    echo "Check logs: docker logs github-runner"
    exit 1
fi