#!/bin/bash
# GitHub Actions Runner Entrypoint Script
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" >&2
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

# Check required environment variables
check_env() {
    local required_vars=("GITHUB_TOKEN" "GITHUB_OWNER" "GITHUB_REPOSITORY")
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            error "Environment variable $var is not set"
            exit 1
        fi
    done
}

# Configure the runner
configure_runner() {
    log "Configuring GitHub Actions Runner..."
    
    # Set runner name
    RUNNER_NAME="${RUNNER_NAME:-$(hostname)}"
    RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,$(uname -m)}"
    RUNNER_WORKDIR="${RUNNER_WORKDIR:-_work}"
    
    # Check if runner is already configured
    if [ -f ".runner" ]; then
        log "Runner already configured"
        return 0
    fi
    
    # Configure runner with retry logic
    local max_attempts=3
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        log "Configuration attempt $attempt of $max_attempts"
        
        if ./config.sh \
            --url "https://github.com/${GITHUB_OWNER}/${GITHUB_REPOSITORY}" \
            --token "${GITHUB_TOKEN}" \
            --name "${RUNNER_NAME}" \
            --labels "${RUNNER_LABELS}" \
            --work "${RUNNER_WORKDIR}" \
            --unattended \
            --replace \
            ${RUNNER_EPHEMERAL:+--ephemeral}; then
            log "Runner configured successfully"
            return 0
        else
            error "Configuration attempt $attempt failed"
            ((attempt++))
            if [ $attempt -le $max_attempts ]; then
                log "Retrying in 5 seconds..."
                sleep 5
            fi
        fi
    done
    
    error "Failed to configure runner after $max_attempts attempts"
    exit 1
}

# Cleanup function
cleanup() {
    log "Cleaning up runner..."
    
    # Try to remove the runner if token is available
    if [ -n "${GITHUB_TOKEN}" ] && [ -f ".runner" ]; then
        ./config.sh remove --token "${GITHUB_TOKEN}" || true
    fi
    
    log "Cleanup completed"
}

# Trap signals for cleanup
trap cleanup EXIT INT TERM

# Main execution
main() {
    log "Starting GitHub Actions Runner for WillowCMS"
    log "Runner version: $(./run.sh --version)"
    log "Platform: $(uname -m)"
    
    # Check environment variables
    check_env
    
    # Configure the runner
    configure_runner
    
    # Start the runner
    log "Starting runner..."
    exec ./run.sh
}

# Run main function
main "$@"