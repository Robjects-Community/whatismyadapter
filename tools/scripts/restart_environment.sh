#!/usr/bin/env bash

# Willow CMS Environment Restart Script
# Automated shutdown, cleanup, and restart with verification
# Usage: ./scripts/restart-environment.sh [--hard] [--soft] [--no-verify]

set -euo pipefail

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "${SCRIPT_DIR}")"
LOG_FILE="${PROJECT_DIR}/logs/restart-environment.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default options
HARD_RESET=false
SOFT_RESET=false
VERIFY=true
FORCE=false

# Logging function
log() {
    local level="$1"
    shift
    local message="$*"
    
    # Create logs directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    
    # Log to file
    echo "[$TIMESTAMP] [$level] $message" >> "$LOG_FILE"
    
    # Output to console with colors
    case "$level" in
        "INFO")
            echo -e "${BLUE}[INFO]${NC} $message"
            ;;
        "WARN")
            echo -e "${YELLOW}[WARN]${NC} $message"
            ;;
        "ERROR")
            echo -e "${RED}[ERROR]${NC} $message"
            ;;
        "SUCCESS")
            echo -e "${GREEN}[SUCCESS]${NC} $message"
            ;;
        *)
            echo "$message"
            ;;
    esac
}

# Error handler
error_exit() {
    log "ERROR" "$1"
    exit 1
}

# Cleanup function for script interruption
cleanup() {
    log "WARN" "Script interrupted. Cleaning up..."
    exit 1
}
trap cleanup INT TERM

# Show help
show_help() {
    cat << EOF
Willow CMS Environment Restart Script

Usage: $0 [OPTIONS]

OPTIONS:
    --hard          Perform hard reset (clears all data including volumes)
    --soft          Perform soft reset (preserves volumes, clears containers)
    --no-verify     Skip post-restart verification
    --force         Skip confirmation prompts
    --help          Show this help message

EXAMPLES:
    $0                          # Standard restart with verification
    $0 --soft                   # Soft reset preserving data
    $0 --hard --force          # Hard reset without confirmation
    $0 --no-verify             # Quick restart without health checks

DESCRIPTION:
    This script automates the shutdown, cleanup, and restart of the Willow CMS
    Docker environment. It includes health checks and verification to ensure
    the environment starts correctly.

    - Soft reset: Stops containers and clears cache, preserves database
    - Hard reset: Removes containers, volumes, and networks (full cleanup)
    - Verification: Checks all services are healthy after restart

EOF
}

# Parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --hard)
                HARD_RESET=true
                shift
                ;;
            --soft)
                SOFT_RESET=true
                shift
                ;;
            --no-verify)
                VERIFY=false
                shift
                ;;
            --force)
                FORCE=true
                shift
                ;;
            --help)
                show_help
                exit 0
                ;;
            *)
                error_exit "Unknown option: $1. Use --help for usage information."
                ;;
        esac
    done

    # Validate options
    if [[ "$HARD_RESET" == true && "$SOFT_RESET" == true ]]; then
        error_exit "Cannot specify both --hard and --soft options"
    fi
}

# Confirm action with user
confirm_action() {
    if [[ "$FORCE" == true ]]; then
        return 0
    fi

    local action="standard restart"
    if [[ "$HARD_RESET" == true ]]; then
        action="HARD RESET (will destroy all data)"
    elif [[ "$SOFT_RESET" == true ]]; then
        action="soft reset"
    fi

    echo
    echo -e "${YELLOW}WARNING: This will perform a $action of the Willow CMS environment.${NC}"
    echo
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log "INFO" "Operation cancelled by user"
        exit 0
    fi
}

# Check if docker-compose file exists
check_prerequisites() {
    log "INFO" "Checking prerequisites..."
    
    if [[ ! -f "$PROJECT_DIR/docker-compose.yml" ]]; then
        error_exit "docker-compose.yml not found in $PROJECT_DIR"
    fi

    if ! command -v docker-compose >/dev/null 2>&1 && ! command -v docker >/dev/null 2>&1; then
        error_exit "Neither docker-compose nor docker compose command found"
    fi

    # Check if we can run docker commands
    if ! docker info >/dev/null 2>&1; then
        error_exit "Cannot connect to Docker daemon. Is Docker running?"
    fi

    log "SUCCESS" "Prerequisites check passed"
}

# Get docker compose command
get_docker_compose_cmd() {
    if command -v docker-compose >/dev/null 2>&1; then
        echo "docker-compose"
    else
        echo "docker compose"
    fi
}

# Stop all services
stop_services() {
    log "INFO" "Stopping all services..."
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    cd "$PROJECT_DIR"
    if $compose_cmd ps --services 2>/dev/null | grep -q .; then
        $compose_cmd down --timeout 30 || log "WARN" "Some services may not have stopped cleanly"
        log "SUCCESS" "Services stopped"
    else
        log "INFO" "No running services found"
    fi
}

# Perform cleanup based on reset type
perform_cleanup() {
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    cd "$PROJECT_DIR"

    if [[ "$HARD_RESET" == true ]]; then
        log "INFO" "Performing hard reset - removing all containers, volumes, and networks..."
        
        # Remove containers, volumes, and orphaned containers
        $compose_cmd down --volumes --remove-orphans --timeout 30 || true
        
        # Prune unused volumes (be careful with this)
        log "INFO" "Pruning unused Docker volumes..."
        docker volume prune -f || log "WARN" "Could not prune volumes"
        
        # Remove unused networks
        log "INFO" "Pruning unused Docker networks..."
        docker network prune -f || log "WARN" "Could not prune networks"
        
        # Clear application caches if directories exist
        if [[ -d "$PROJECT_DIR/tmp/cache" ]]; then
            log "INFO" "Clearing application cache..."
            rm -rf "$PROJECT_DIR/tmp/cache"/* 2>/dev/null || true
        fi
        
        log "SUCCESS" "Hard reset completed"
        
    elif [[ "$SOFT_RESET" == true ]]; then
        log "INFO" "Performing soft reset - preserving data volumes..."
        
        # Just remove containers, keep volumes
        $compose_cmd down --remove-orphans --timeout 30 || true
        
        # Clear application caches only
        if [[ -d "$PROJECT_DIR/tmp/cache" ]]; then
            log "INFO" "Clearing application cache..."
            rm -rf "$PROJECT_DIR/tmp/cache"/* 2>/dev/null || true
        fi
        
        log "SUCCESS" "Soft reset completed"
    else
        log "INFO" "Standard cleanup - stopping services only..."
        # Already stopped in stop_services, just clear cache
        if [[ -d "$PROJECT_DIR/tmp/cache" ]]; then
            log "INFO" "Clearing application cache..."
            rm -rf "$PROJECT_DIR/tmp/cache"/* 2>/dev/null || true
        fi
        log "SUCCESS" "Standard cleanup completed"
    fi
}

# Start all services
start_services() {
    log "INFO" "Starting all services..."
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    cd "$PROJECT_DIR"
    
    # Pull latest images if hard reset
    if [[ "$HARD_RESET" == true ]]; then
        log "INFO" "Pulling latest images..."
        $compose_cmd pull || log "WARN" "Could not pull all images"
    fi
    
    # Start services
    $compose_cmd up -d --build
    
    if [[ $? -eq 0 ]]; then
        log "SUCCESS" "Services started"
    else
        error_exit "Failed to start services"
    fi
}

# Wait for service to be ready
wait_for_service() {
    local service_name="$1"
    local port="$2"
    local max_attempts=30
    local attempt=1

    log "INFO" "Waiting for $service_name to be ready on port $port..."
    
    while [[ $attempt -le $max_attempts ]]; do
        if nc -z localhost "$port" 2>/dev/null; then
            log "SUCCESS" "$service_name is ready (attempt $attempt/$max_attempts)"
            return 0
        fi
        
        log "INFO" "Waiting for $service_name... (attempt $attempt/$max_attempts)"
        sleep 2
        ((attempt++))
    done
    
    log "ERROR" "$service_name failed to start after $max_attempts attempts"
    return 1
}

# Verify services are running
verify_services() {
    if [[ "$VERIFY" == false ]]; then
        log "INFO" "Skipping verification as requested"
        return 0
    fi

    log "INFO" "Verifying all services are healthy..."
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    cd "$PROJECT_DIR"
    
    # Check if services are running
    local services_status
    services_status=$($compose_cmd ps --format "table {{.Service}}\t{{.Status}}" | tail -n +2)
    
    log "INFO" "Service status:"
    echo "$services_status"
    
    # Wait for key services to be ready
    local failed_services=()
    
    # Wait for main application
    if ! wait_for_service "willowcms" "8080"; then
        failed_services+=("willowcms")
    fi
    
    # Wait for database
    if ! wait_for_service "mysql" "3310"; then
        failed_services+=("mysql")
    fi
    
    # Wait for redis
    if ! wait_for_service "redis" "6379"; then
        failed_services+=("redis")
    fi
    
    # Check if any services failed
    if [[ ${#failed_services[@]} -gt 0 ]]; then
        log "ERROR" "The following services failed to start: ${failed_services[*]}"
        log "INFO" "You can check service logs with: docker-compose logs [service_name]"
        return 1
    fi
    
    # Test main application endpoint
    log "INFO" "Testing main application endpoint..."
    local http_status
    http_status=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 || echo "000")
    
    if [[ "$http_status" == "200" ]] || [[ "$http_status" == "302" ]]; then
        log "SUCCESS" "Application is responding (HTTP $http_status)"
    else
        log "WARN" "Application returned HTTP $http_status - may still be initializing"
    fi
    
    log "SUCCESS" "Service verification completed successfully"
    return 0
}

# Show service URLs
show_service_info() {
    log "INFO" "Willow CMS environment is ready!"
    echo
    echo "Available services:"
    echo "  📱 Main Application:  http://localhost:8080"
    echo "  👤 Admin Panel:       http://localhost:8080/admin"
    echo "  🗄️  phpMyAdmin:       http://localhost:8082"
    echo "  📧 Mailpit:          http://localhost:8025"
    echo "  🔧 Redis Commander:  http://localhost:8084"
    echo "  🏗️  Jenkins:          http://localhost:8081"
    echo
    echo "To view service logs: docker-compose logs [service_name]"
    echo "To stop services: docker-compose down"
    echo
}

# Main execution
main() {
    log "INFO" "Starting Willow CMS environment restart script"
    
    parse_arguments "$@"
    confirm_action
    check_prerequisites
    
    local start_time
    start_time=$(date +%s)
    
    stop_services
    perform_cleanup
    start_services
    
    if verify_services; then
        local end_time
        end_time=$(date +%s)
        local duration=$((end_time - start_time))
        
        log "SUCCESS" "Environment restart completed successfully in ${duration} seconds"
        show_service_info
    else
        error_exit "Environment restart completed but some services may not be fully ready"
    fi
}

# Execute main function with all arguments
main "$@"
