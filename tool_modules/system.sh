#!/bin/bash

# Execute system commands (options 11-13, 0)
execute_system_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        11)
            echo "Clearing WillowCMS Cache..."
            docker compose exec willowcms bin/cake cache clear_all
            ;;
        12)
            echo "Opening an interactive shell to Willow CMS container (/bin/sh)..."
            docker compose exec -it willowcms /bin/sh
            ;;
        13)
            perform_host_system_update
            ;;
        0)
            echo "Exiting..."
            exit 0
            ;;
        *)
            echo "Error: Invalid system option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Function to perform host system update and Docker cleanup
perform_host_system_update() {
    echo "Host System Update & Docker Cleanup..."
    echo "This will attempt to update host OS packages and clean unused Docker resources."
    echo "Note: Running containers including WillowCMS and MySQL will NOT be affected."
    read -r -p "Do you want to continue? (y/N): " confirm

    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        local OS_TYPE
        OS_TYPE=$(uname -s)
        local SUDO_CMD=""
        if [ "$(id -u)" -ne 0 ]; then
            if command -v sudo >/dev/null; then 
                SUDO_CMD="sudo "
                echo "Using sudo for package management and Docker commands."
            else 
                echo "Warning: Running as non-root and 'sudo' not found. Privileged commands might fail."
            fi
        fi

        echo "Attempting to update host system packages..."
        if command -v apt-get > /dev/null; then ${SUDO_CMD}apt-get update && ${SUDO_CMD}apt-get upgrade -y
        elif command -v yum > /dev/null && ! command -v dnf > /dev/null; then ${SUDO_CMD}yum update -y
        elif command -v dnf > /dev/null; then ${SUDO_CMD}dnf upgrade -y --refresh
        elif [ "$OS_TYPE" = "Linux" ] && command -v apk > /dev/null; then ${SUDO_CMD}apk update && ${SUDO_CMD}apk upgrade
        elif [ "$OS_TYPE" = "Darwin" ] && command -v brew > /dev/null; then brew update && brew upgrade
        else echo "Could not detect package manager or sudo issue. Skipping OS update."; fi

        echo
        echo "Cleaning unused Docker resources (running containers will NOT be affected)..."
        
        # Safely prune only stopped containers
        echo "Removing stopped containers (if any)..."
        ${SUDO_CMD}docker container prune -f
        
        # Safe pruning of unused networks, volumes, and images
        echo "Cleaning unused Docker networks..."
        ${SUDO_CMD}docker network prune -f
        
        echo "Cleaning unused Docker volumes..."
        ${SUDO_CMD}docker volume prune -f
        
        echo "Cleaning unused Docker images..."
        ${SUDO_CMD}docker image prune -f  # Only removes dangling images by default
        
        echo "Cleaning Docker build cache..."
        ${SUDO_CMD}docker builder prune -f
        
        echo
        echo "Host system update and Docker cleanup completed successfully."
        echo "Note: Running containers (including WillowCMS and MySQL) were preserved."
    else 
        echo "Operation cancelled."
    fi
    
    return 0
}