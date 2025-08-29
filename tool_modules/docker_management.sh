#!/bin/bash

# Execute Docker management commands (options 16-19)
execute_docker_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        16)
            view_docker_restart_documentation
            ;;
        17)
            restart_docker_environment_standard
            ;;
        18)
            restart_docker_environment_soft
            ;;
        19)
            restart_docker_environment_hard
            ;;
        *)
            echo "Error: Invalid Docker management option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Function to view Docker restart documentation
view_docker_restart_documentation() {
    echo "Viewing Docker Restart Documentation..."
    echo "=============================================="
    echo
    
    local doc_path="docs/docker-restart-guide.md"
    
    if [ -f "$doc_path" ]; then
        # Check if we have a pager available
        if command -v less >/dev/null 2>&1; then
            echo "Opening documentation with 'less' (press 'q' to exit)..."
            echo "Press [Enter] to continue..."
            read -r
            less "$doc_path"
        elif command -v more >/dev/null 2>&1; then
            echo "Opening documentation with 'more' (press 'q' to exit)..."
            echo "Press [Enter] to continue..."
            read -r
            more "$doc_path"
        else
            # Fallback to head if no pager is available
            echo "No pager available. Showing first 50 lines of documentation:"
            echo "For the full guide, please view: $doc_path"
            echo
            head -n 50 "$doc_path"
            echo
            echo "... (truncated)"
            echo
            echo "For the complete guide, please view: $doc_path"
            echo "Or install a pager like 'less' or 'more' for better viewing experience."
        fi
    else
        echo "Docker restart documentation not found at: $doc_path"
        echo 
        echo "Expected location: $doc_path"
        echo "Please ensure the documentation file exists or check the repository structure."
        return 1
    fi
}

# Function to restart Docker environment (standard)
restart_docker_environment_standard() {
    echo "Restarting Docker Environment (Standard)..."
    echo "=============================================="
    echo
    echo "This will perform a standard restart of all Docker services."
    echo "No data will be lost, containers will be restarted gracefully."
    echo
    read -r -p "Do you want to continue? (y/N): " confirm
    
    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        echo "Performing standard restart..."
        
        # Check if restart script exists
        if [ -f "scripts/restart-environment.sh" ]; then
            echo "Using automated restart script..."
            ./scripts/restart-environment.sh --force --no-verify
        else
            # Fallback to manual restart
            echo "Using manual restart commands..."
            echo "Stopping services..."
            docker compose down --timeout 30
            
            echo "Starting services..."
            docker compose up -d
            
            echo "Waiting for services to be ready..."
            sleep 10
            
            echo "Checking service status..."
            docker compose ps
        fi
        
        echo
        echo "Standard restart completed successfully."
        echo "Services should be available at:"
        echo "  - Main application: http://localhost:8080"
        echo "  - Admin panel: http://localhost:8080/admin"
        echo "  - phpMyAdmin: http://localhost:8082"
        echo "  - Mailpit: http://localhost:8025"
    else
        echo "Operation cancelled."
    fi
}

# Function to restart Docker environment (soft reset)
restart_docker_environment_soft() {
    echo "Restarting Docker Environment (Soft Reset)..."
    echo "=============================================="
    echo
    echo "This will perform a soft reset:"
    echo "- Stops and removes containers"
    echo "- Preserves data volumes (database, uploads, etc.)"
    echo "- Clears application cache"
    echo "- Rebuilds and starts fresh containers"
    echo
    echo "Your data will be preserved, but containers will be recreated."
    echo
    read -r -p "Do you want to continue? (y/N): " confirm
    
    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        echo "Performing soft reset..."
        
        # Check if restart script exists
        if [ -f "scripts/restart-environment.sh" ]; then
            echo "Using automated restart script..."
            ./scripts/restart-environment.sh --soft --force
        else
            # Fallback to manual soft reset
            echo "Using manual soft reset commands..."
            
            echo "Stopping and removing containers..."
            docker compose down --remove-orphans --timeout 30
            
            echo "Clearing application cache..."
            if [ -d "tmp/cache" ]; then
                rm -rf tmp/cache/* 2>/dev/null || true
                echo "Application cache cleared."
            fi
            
            echo "Starting services with rebuild..."
            docker compose up -d --build
            
            echo "Waiting for services to be ready..."
            sleep 15
            
            echo "Checking service status..."
            docker compose ps
        fi
        
        echo
        echo "Soft reset completed successfully."
        echo "All data has been preserved, containers have been recreated."
    else
        echo "Operation cancelled."
    fi
}

# Function to restart Docker environment (hard reset)
restart_docker_environment_hard() {
    echo "Restarting Docker Environment (Hard Reset)..."
    echo "=============================================="
    echo
    echo "⚠️  WARNING: DESTRUCTIVE OPERATION ⚠️"
    echo
    echo "This will perform a HARD RESET:"
    echo "- Stops and removes ALL containers"
    echo "- Removes ALL volumes (database data, uploads, etc.)"
    echo "- Removes networks and unused images"
    echo "- Completely reinitializes the environment"
    echo
    echo "🚨 ALL DATA WILL BE LOST! 🚨"
    echo "This includes:"
    echo "  - Database content"
    echo "  - Uploaded files"
    echo "  - Application cache and logs"
    echo "  - Any custom configuration changes"
    echo
    echo "Only proceed if you want to start completely fresh!"
    echo
    read -r -p "Are you ABSOLUTELY SURE you want to continue? Type 'DESTROY' to confirm: " confirm
    
    if [ "$confirm" = "DESTROY" ]; then
        echo
        echo "Final confirmation: This will DELETE ALL DATA!"
        read -r -p "Type 'YES DELETE EVERYTHING' to proceed: " final_confirm
        
        if [ "$final_confirm" = "YES DELETE EVERYTHING" ]; then
            echo "Performing hard reset... This cannot be undone!"
            
            # Check if restart script exists
            if [ -f "scripts/restart-environment.sh" ]; then
                echo "Using automated restart script..."
                ./scripts/restart-environment.sh --hard --force
            else
                # Fallback to manual hard reset
                echo "Using manual hard reset commands..."
                
                echo "Stopping all services..."
                docker compose down --volumes --remove-orphans --timeout 30
                
                echo "Pruning unused Docker resources..."
                docker volume prune -f
                docker network prune -f
                docker image prune -f
                docker container prune -f
                
                echo "Clearing application files..."
                if [ -d "tmp" ]; then
                    rm -rf tmp/* 2>/dev/null || true
                fi
                if [ -d "logs" ]; then
                    rm -rf logs/* 2>/dev/null || true
                fi
                
                echo "Starting fresh environment..."
                docker compose up -d --build
                
                echo "Waiting for services to initialize..."
                sleep 30
                
                echo "Running database migrations..."
                docker compose exec willowcms bin/cake migrations migrate || echo "Migration may have failed - check manually"
                
                echo "Checking service status..."
                docker compose ps
            fi
            
            echo
            echo "Hard reset completed."
            echo "Environment has been completely reinitialized."
            echo "You may need to:"
            echo "  - Import default data"
            echo "  - Reconfigure settings"
            echo "  - Set up user accounts"
        else
            echo "Operation cancelled - incorrect confirmation."
        fi
    else
        echo "Operation cancelled - hard reset not confirmed."
    fi
}
