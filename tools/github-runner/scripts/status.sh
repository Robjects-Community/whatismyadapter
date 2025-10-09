#!/bin/bash
# Check GitHub Actions Runner Status
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=== GitHub Actions Runner Status ===${NC}"
echo ""

# Check if container exists
if ! docker ps -a | grep -q "github-runner"; then
    echo -e "${RED}✗ Runner container does not exist${NC}"
    echo "Run ./start.sh to create and start the runner"
    exit 1
fi

# Check container status
if docker ps | grep -q "github-runner"; then
    echo -e "${GREEN}✓ Container Status: Running${NC}"
    
    # Get container details
    CONTAINER_ID=$(docker ps -q -f name=github-runner)
    echo -e "${BLUE}Container ID:${NC} $CONTAINER_ID"
    
    # Check container health
    HEALTH_STATUS=$(docker inspect --format='{{.State.Health.Status}}' github-runner 2>/dev/null || echo "not configured")
    if [ "$HEALTH_STATUS" = "healthy" ]; then
        echo -e "${GREEN}✓ Health Status: Healthy${NC}"
    elif [ "$HEALTH_STATUS" = "unhealthy" ]; then
        echo -e "${RED}✗ Health Status: Unhealthy${NC}"
    else
        echo -e "${YELLOW}⚠ Health Status: $HEALTH_STATUS${NC}"
    fi
    
    # Get container uptime
    UPTIME=$(docker inspect --format='{{.State.StartedAt}}' github-runner)
    echo -e "${BLUE}Started:${NC} $UPTIME"
    
    # Check runner process
    echo ""
    echo -e "${BLUE}Runner Process Status:${NC}"
    if docker exec github-runner pgrep -f "Runner.Listener" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Runner.Listener is running${NC}"
    else
        echo -e "${RED}✗ Runner.Listener is not running${NC}"
        echo "The runner may be restarting or having issues"
    fi
    
    # Show recent logs
    echo ""
    echo -e "${BLUE}Recent Logs:${NC}"
    docker logs --tail 10 github-runner 2>&1 | sed 's/^/  /'
    
else
    echo -e "${YELLOW}⚠ Container Status: Stopped${NC}"
    
    # Get exit code
    EXIT_CODE=$(docker inspect --format='{{.State.ExitCode}}' github-runner)
    echo -e "${BLUE}Exit Code:${NC} $EXIT_CODE"
    
    # Show last logs
    echo ""
    echo -e "${BLUE}Last Logs Before Stop:${NC}"
    docker logs --tail 20 github-runner 2>&1 | sed 's/^/  /'
fi

echo ""
echo -e "${BLUE}=== Actions ===${NC}"
echo "• Start runner:   ./start.sh"
echo "• Stop runner:    ./stop.sh"
echo "• View full logs: docker logs -f github-runner"