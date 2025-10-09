#!/usr/bin/env bash
# WillowCMS Compose Validation Script
# Validates docker-compose.yml with environment variables

set -euo pipefail

# Configuration
ROOT="/Volumes/1TB_DAVINCI/docker/willow"
COMPOSE_DIR="${ROOT}/portainer-stacks"
ENV_FILE="${ROOT}/stack.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
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

# Check if files exist
if [ ! -f "$COMPOSE_DIR/docker-compose.yml" ]; then
    log_error "docker-compose.yml not found at $COMPOSE_DIR"
    exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
    log_error "stack.env not found at $ENV_FILE"
    exit 1
fi

log "Validating Docker Compose configuration..."

# Change to compose directory
cd "$COMPOSE_DIR"

# Validate configuration
OUTPUT_FILE="/tmp/willowcms.stack.validated.yml"

if docker compose -f docker-compose.yml config > "$OUTPUT_FILE" 2>&1; then
    log_success "Compose configuration is valid!"
    
    # Check for unresolved variables
    if grep -q '\$\{' "$OUTPUT_FILE"; then
        log_warning "Found unresolved variables in configuration:"
        grep '\$\{' "$OUTPUT_FILE" | sed 's/^/  /'
        echo
    fi
    
    # Show basic service info
    log "Services found:"
    docker compose -f docker-compose.yml config --services | sed 's/^/  - /'
    echo
    
    # Show volume info
    log "Volumes found:"
    grep -E "^  [a-z_]+:" "$OUTPUT_FILE" | grep -A1 -B1 "driver:" | grep -E "^  [a-z_]+:" | sed 's/^  /  - /' | sed 's/:$//'
    echo
    
    # Show network info  
    log "Networks found:"
    grep -E "^  [a-z_]+:" "$OUTPUT_FILE" | grep -A5 "driver: bridge" | grep -E "^  [a-z_]+:" | sed 's/^  /  - /' | sed 's/:$//'
    echo
    
    log_success "Validation completed successfully!"
    log "Full configuration saved to: $OUTPUT_FILE"
    
else
    log_error "Compose configuration is invalid!"
    echo
    cat "$OUTPUT_FILE"
    exit 1
fi