#!/usr/bin/env bash
# WillowCMS SSH Access Deployment Script
# Path 1: Setup/sporadic-usage SSH access for whatismyadapter user (UID=1034, GID=100)
# Worst case scenario - only used periodically with SSH access turned off afterward

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
ENV_FILE="${PROJECT_ROOT}/stack.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if running as correct user
check_user() {
    local current_uid=$(id -u)
    local current_gid=$(id -g)
    
    if [ "$current_uid" != "1034" ] || [ "$current_gid" != "100" ]; then
        log_error "This script should be run as user 'whatismyadapter' (UID=1034, GID=100)"
        log_error "Current user: UID=$current_uid, GID=$current_gid"
        echo
        echo "To switch to the correct user:"
        echo "  sudo su - whatismyadapter"
        exit 1
    fi
    
    log_success "Running as correct user: whatismyadapter (UID=1034, GID=100)"
}

# Check SSH access status
check_ssh_status() {
    log "Checking SSH access status..."
    
    if systemctl is-active --quiet ssh 2>/dev/null || systemctl is-active --quiet sshd 2>/dev/null; then
        log_warning "SSH service is currently ACTIVE"
        echo "  Remember to disable SSH access after completing maintenance!"
    else
        log "SSH service appears to be inactive (good for security)"
    fi
}

# Display deployment menu
show_menu() {
    echo
    echo "=== WillowCMS SSH Access Deployment Menu ==="
    echo
    echo "1. Run development environment (./run_dev_env.sh)"
    echo "2. Start services with main docker-compose.yml (main-clean branch)"
    echo "3. Stop all WillowCMS services"
    echo "4. View service logs"
    echo "5. Access WillowCMS container shell"
    echo "6. Run database migrations"
    echo "7. Clear application cache"
    echo "8. Show service status"
    echo "9. Backup current configuration"
    echo "0. Exit"
    echo
}

# Execute run_dev_env.sh script
run_dev_environment() {
    log "Starting development environment..."
    
    if [ -f "${PROJECT_ROOT}/run_dev_env.sh" ]; then
        cd "$PROJECT_ROOT"
        chmod +x run_dev_env.sh
        ./run_dev_env.sh
    else
        log_error "run_dev_env.sh script not found at $PROJECT_ROOT"
        return 1
    fi
}

# Start services with main docker-compose.yml
start_main_services() {
    log "Starting services with main docker-compose.yml (hardened image)..."
    
    cd "$PROJECT_ROOT"
    
    if [ -f "docker-compose.yml" ]; then
        # Use the hardened configuration from main-clean branch
        log "Using hardened Docker configuration..."
        docker compose --env-file "$ENV_FILE" up -d
        
        log_success "Services started successfully"
        echo
        echo "Available services:"
        docker compose --env-file "$ENV_FILE" ps
        
    else
        log_error "docker-compose.yml not found at $PROJECT_ROOT"
        return 1
    fi
}

# Stop all services
stop_services() {
    log "Stopping all WillowCMS services..."
    
    cd "$PROJECT_ROOT"
    
    # Stop main compose services
    if [ -f "docker-compose.yml" ]; then
        docker compose --env-file "$ENV_FILE" down
    fi
    
    log_success "All services stopped"
}

# View service logs
view_logs() {
    cd "$PROJECT_ROOT"
    
    echo "Select service to view logs:"
    echo "1. WillowCMS (app)"
    echo "2. MySQL"
    echo "3. Redis"
    echo "4. All services"
    
    read -p "Enter choice [1-4]: " log_choice
    
    case $log_choice in
        1) docker compose --env-file "$ENV_FILE" logs -f willowcms ;;
        2) docker compose --env-file "$ENV_FILE" logs -f mysql ;;
        3) docker compose --env-file "$ENV_FILE" logs -f redis ;;
        4) docker compose --env-file "$ENV_FILE" logs -f ;;
        *) log_error "Invalid choice" ;;
    esac
}

# Access container shell
access_shell() {
    log "Accessing WillowCMS container shell..."
    
    cd "$PROJECT_ROOT"
    
    if docker compose --env-file "$ENV_FILE" ps | grep -q "willowcms.*Up"; then
        docker compose --env-file "$ENV_FILE" exec willowcms bash
    else
        log_error "WillowCMS container is not running"
        return 1
    fi
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    cd "$PROJECT_ROOT"
    
    if docker compose --env-file "$ENV_FILE" ps | grep -q "willowcms.*Up"; then
        docker compose --env-file "$ENV_FILE" exec willowcms bin/cake migrations migrate
        log_success "Migrations completed"
    else
        log_error "WillowCMS container is not running"
        return 1
    fi
}

# Clear application cache
clear_cache() {
    log "Clearing application cache..."
    
    cd "$PROJECT_ROOT"
    
    if docker compose --env-file "$ENV_FILE" ps | grep -q "willowcms.*Up"; then
        docker compose --env-file "$ENV_FILE" exec willowcms bin/cake cache clear_all
        log_success "Cache cleared"
    else
        log_error "WillowCMS container is not running"
        return 1
    fi
}

# Show service status
show_status() {
    log "Service status:"
    
    cd "$PROJECT_ROOT"
    
    echo
    echo "=== Docker Compose Services ==="
    if [ -f "docker-compose.yml" ]; then
        docker compose --env-file "$ENV_FILE" ps
    else
        echo "No docker-compose.yml found"
    fi
    
    echo
    echo "=== Docker Images ==="
    docker images | grep willowcms || echo "No WillowCMS images found"
    
    echo
    echo "=== System Resources ==="
    df -h / || true
    free -m || true
}

# Backup configuration
backup_config() {
    log "Creating configuration backup..."
    
    if [ -f "${PROJECT_ROOT}/tools/backup/backup_portainer_stack.sh" ]; then
        cd "$PROJECT_ROOT"
        ./tools/backup/backup_portainer_stack.sh
    else
        log_error "Backup script not found"
        return 1
    fi
}

# Main execution
main() {
    log "WillowCMS SSH Access Deployment Tool"
    log "For user: whatismyadapter (UID=1034, GID=100)"
    
    # Perform checks
    check_user
    check_ssh_status
    
    # Main menu loop
    while true; do
        show_menu
        read -p "Enter your choice [0-9]: " choice
        
        case $choice in
            1) run_dev_environment ;;
            2) start_main_services ;;
            3) stop_services ;;
            4) view_logs ;;
            5) access_shell ;;
            6) run_migrations ;;
            7) clear_cache ;;
            8) show_status ;;
            9) backup_config ;;
            0) 
                log "Exiting..."
                log_warning "Remember to disable SSH access for security!"
                exit 0
                ;;
            *) log_error "Invalid choice. Please enter 0-9." ;;
        esac
        
        echo
        read -p "Press Enter to continue..."
    done
}

# Run main function
main "$@"