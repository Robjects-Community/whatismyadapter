#!/bin/bash

# Docker Compose Helper Script for WillowCMS
# This script ensures proper environment variable substitution from config/.env

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if config/.env exists
if [ ! -f "config/.env" ]; then
    print_error "config/.env file not found!"
    print_error "Please ensure the config/.env file exists in the config directory."
    exit 1
fi

# Get the directory of this script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

print_status "Using environment file: $(pwd)/config/.env"

# Method 1: Docker Compose v2 with --env-file flag (RECOMMENDED)
if docker compose version >/dev/null 2>&1; then
    print_status "Using Docker Compose v2 with --env-file flag"
    
    # Test configuration first
    if docker compose --env-file config/.env config >/dev/null 2>&1; then
        print_status "Configuration validated successfully"
        # Execute the docker compose command with all passed arguments
        exec docker compose --env-file config/.env "$@"
    else
        print_error "Configuration validation failed"
        exit 1
    fi
    
# Method 2: Legacy docker-compose or fallback method
elif docker-compose --version >/dev/null 2>&1; then
    print_warning "Using legacy docker-compose, falling back to environment export method"
    
    # Export variables from config/.env into current shell
    set -a  # automatically export all variables
    source config/.env
    set +a  # stop auto-exporting
    
    # Test configuration
    if docker-compose config >/dev/null 2>&1; then
        print_status "Configuration validated successfully with exported variables"
        # Execute the docker-compose command with all passed arguments
        exec docker-compose "$@"
    else
        print_error "Configuration validation failed"
        exit 1
    fi
else
    print_error "Neither 'docker compose' nor 'docker-compose' found"
    print_error "Please install Docker Compose"
    exit 1
fi
