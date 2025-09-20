#!/bin/bash

# WillowCMS Docker Cleanup Script
# This script provides interactive cleanup options for the WillowCMS Docker environment

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_color() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to show usage information
show_usage() {
    cat << EOF
WillowCMS Docker Cleanup Script

USAGE:
    $0 [OPTIONS]

OPTIONS:
    -s, --soft      Perform soft cleanup (preserve data)
    -h, --hard      Perform hard cleanup (remove all data)
    -n, --nuclear   Perform nuclear cleanup (system-wide)
    --help          Show this help message

DESCRIPTION:
    Interactive cleanup script for the WillowCMS Docker environment.
    Provides different cleanup levels with safety prompts.

EXAMPLES:
    $0              # Interactive mode
    $0 --soft       # Direct soft cleanup
    $0 --hard       # Direct hard cleanup

EOF
}

# Function to create backup before cleanup
create_backup() {
    print_color $BLUE "Creating backup before cleanup..."
    
    # Check if manage.sh exists and is executable
    if [[ -f "./manage.sh" && -x "./manage.sh" ]]; then
        print_color $GREEN "Using manage.sh for database backup..."
        echo "Please run the manage.sh script manually and select option 3 to create a database backup."
        read -p "Press Enter when backup is complete, or 'skip' to continue without backup: " backup_choice
        if [[ "$backup_choice" != "skip" ]]; then
            return 0
        fi
    fi
    
    # Manual backup as fallback
    print_color $YELLOW "Creating manual backup..."
    
    # Create backup directory
    mkdir -p ./backups/$(date +%Y%m%d_%H%M%S)
    local backup_dir="./backups/$(date +%Y%m%d_%H%M%S)"
    
    # Backup configuration files
    if [[ -d "./config" ]]; then
        cp -r ./config/ "$backup_dir/"
        print_color $GREEN "Configuration files backed up"
    fi
    
    if [[ -f "docker-compose.yml" ]]; then
        cp docker-compose.yml "$backup_dir/"
        print_color $GREEN "Docker compose file backed up"
    fi
    
    print_color $GREEN "Backup created in: $backup_dir"
}

# Function to perform soft cleanup
soft_cleanup() {
    print_color $BLUE "🧹 Starting Soft Cleanup (Preserve Data)..."
    
    print_color $YELLOW "This will:"
    echo "  ✓ Stop all services"
    echo "  ✓ Remove stopped containers"
    echo "  ✓ Clean up unused networks"  
    echo "  ✓ Remove dangling images"
    echo "  ✓ Clean build cache"
    echo "  ✗ Preserve all data volumes"
    echo ""
    
    if [[ "$1" != "--force" ]]; then
        read -p "Continue with soft cleanup? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_color $YELLOW "Cleanup cancelled."
            return 1
        fi
    fi
    
    print_color $BLUE "Stopping all services..."
    docker-compose down || true
    
    print_color $BLUE "Removing stopped containers..."
    docker-compose rm -f || true
    
    print_color $BLUE "Cleaning up unused networks..."
    docker network prune -f
    
    print_color $BLUE "Removing dangling images..."
    docker image prune -f
    
    print_color $BLUE "Cleaning build cache..."
    docker builder prune -f
    
    print_color $BLUE "Restarting services..."
    docker-compose up -d
    
    print_color $GREEN "✅ Soft cleanup completed successfully!"
    docker-compose ps
}

# Function to perform hard cleanup
hard_cleanup() {
    print_color $RED "🔥 Starting Hard Cleanup (Fresh Start)..."
    
    print_color $YELLOW "⚠️  WARNING: This will PERMANENTLY DELETE ALL DATA!"
    echo "  ✓ Stop and remove all containers"
    echo "  ✓ Remove ALL data volumes (mysql_data, redis_data, jenkins_home, mailpit_data)"
    echo "  ✓ Remove custom images"
    echo "  ✓ Clean build cache"
    echo ""
    
    if [[ "$1" != "--force" ]]; then
        print_color $RED "This action cannot be undone!"
        read -p "Are you ABSOLUTELY sure you want to continue? Type 'DELETE' to confirm: " confirm
        if [[ "$confirm" != "DELETE" ]]; then
            print_color $YELLOW "Cleanup cancelled."
            return 1
        fi
    fi
    
    # Create backup before hard cleanup
    create_backup
    
    print_color $BLUE "Stopping and removing all containers with volumes..."
    docker-compose down -v || true
    
    print_color $BLUE "Removing named volumes..."
    docker volume rm mysql_data redis_data rabbitmq_data jenkins_home mailpit_data 2>/dev/null || echo "Some volumes may not exist"
    
    print_color $BLUE "Removing custom images..."
    docker rmi garzarobmdocker/willowcms:latest garzarobmdocker/jenkins:latest 2>/dev/null || echo "Some images may not exist"
    
    print_color $BLUE "Removing additional stack images..."
    docker rmi mysql:8.4.3 phpmyadmin redis:7-alpine axllent/mailpit:latest rediscommander/redis-commander:latest 2>/dev/null || echo "Some images may not exist"
    
    print_color $BLUE "Cleaning build cache..."
    docker builder prune -f
    
    print_color $BLUE "Removing custom networks..."
    docker network rm willow_cms_default 2>/dev/null || true
    
    print_color $GREEN "✅ Hard cleanup completed!"
    print_color $BLUE "To restart with fresh data, run: docker-compose up -d --build"
}

# Function to perform nuclear cleanup
nuclear_cleanup() {
    print_color $RED "☢️  NUCLEAR CLEANUP - SYSTEM WIDE RESET"
    
    print_color $RED "⚠️  EXTREME WARNING: This will remove ALL Docker data on your system!"
    print_color $YELLOW "This includes:"
    echo "  ✓ ALL containers (from all projects)"
    echo "  ✓ ALL images (from all projects)" 
    echo "  ✓ ALL volumes (from all projects)"
    echo "  ✓ ALL networks (from all projects)"
    echo "  ✓ ALL build cache"
    echo ""
    
    if [[ "$1" != "--force" ]]; then
        print_color $RED "This will affect ALL Docker projects on your system!"
        read -p "Type 'NUCLEAR' to confirm system-wide cleanup: " confirm
        if [[ "$confirm" != "NUCLEAR" ]]; then
            print_color $YELLOW "Nuclear cleanup cancelled."
            return 1
        fi
    fi
    
    print_color $BLUE "Stopping all running containers..."
    docker stop $(docker ps -aq) 2>/dev/null || true
    
    print_color $BLUE "Removing all containers..."
    docker rm $(docker ps -aq) 2>/dev/null || true
    
    print_color $BLUE "Removing all images..."
    docker rmi $(docker images -q) 2>/dev/null || true
    
    print_color $BLUE "Removing all volumes..."
    docker volume rm $(docker volume ls -q) 2>/dev/null || true
    
    print_color $BLUE "Removing all networks..."
    docker network rm $(docker network ls -q) 2>/dev/null || true
    
    print_color $BLUE "System-wide cleanup with volumes..."
    docker system prune -a --volumes -f
    
    print_color $BLUE "Cleaning all build cache..."
    docker builder prune -a -f
    
    print_color $GREEN "✅ Nuclear cleanup completed!"
    print_color $BLUE "All Docker data has been removed from your system."
}

# Function to show interactive menu
show_menu() {
    clear
    print_color $BLUE "🐳 WillowCMS Docker Cleanup Tool"
    print_color $BLUE "=================================="
    echo ""
    print_color $GREEN "1) Soft Cleanup - Preserve Data"
    echo "   Remove containers, networks, dangling images"
    echo "   Keep all data volumes intact"
    echo ""
    print_color $YELLOW "2) Hard Cleanup - Fresh Start"  
    echo "   Remove ALL data and start fresh"
    echo "   ⚠️  This deletes databases, Jenkins, etc."
    echo ""
    print_color $RED "3) Nuclear Cleanup - System Wide"
    echo "   Remove ALL Docker data on the system"
    echo "   ⚠️  Affects ALL Docker projects!"
    echo ""
    print_color $BLUE "4) Show Current Docker Status"
    echo ""
    print_color $BLUE "0) Exit"
    echo ""
}

# Function to show docker status
show_status() {
    print_color $BLUE "Current Docker Status:"
    echo ""
    
    print_color $GREEN "Running Containers:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "No running containers"
    echo ""
    
    print_color $GREEN "All Containers:"
    docker ps -a --format "table {{.Names}}\t{{.Status}}" 2>/dev/null || echo "No containers"
    echo ""
    
    print_color $GREEN "Volumes:"
    docker volume ls 2>/dev/null || echo "No volumes"
    echo ""
    
    print_color $GREEN "Images:"
    docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}" 2>/dev/null || echo "No images"
    echo ""
    
    print_color $GREEN "Disk Usage:"
    docker system df 2>/dev/null || echo "Unable to get disk usage"
    
    read -p "Press Enter to continue..."
}

# Main script logic
main() {
    # Check if Docker is running
    if ! docker info > /dev/null 2>&1; then
        print_color $RED "Error: Docker is not running or accessible."
        exit 1
    fi
    
    # Check command line arguments
    case "${1:-}" in
        -s|--soft)
            soft_cleanup --force
            exit 0
            ;;
        -h|--hard)
            hard_cleanup --force
            exit 0
            ;;
        -n|--nuclear)
            nuclear_cleanup --force
            exit 0
            ;;
        --help)
            show_usage
            exit 0
            ;;
    esac
    
    # Interactive mode
    while true; do
        show_menu
        read -p "Select an option (0-4): " choice
        
        case $choice in
            1)
                soft_cleanup
                read -p "Press Enter to continue..."
                ;;
            2)
                hard_cleanup
                read -p "Press Enter to continue..."
                ;;
            3)
                nuclear_cleanup
                read -p "Press Enter to continue..."
                ;;
            4)
                show_status
                ;;
            0)
                print_color $GREEN "Goodbye!"
                exit 0
                ;;
            *)
                print_color $RED "Invalid option. Please try again."
                read -p "Press Enter to continue..."
                ;;
        esac
    done
}

# Run main function
main "$@"
