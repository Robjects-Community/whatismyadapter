#!/bin/bash

# Deployment Management Module for WillowCMS
# Provides deployment commands to remote droplet

# Get the directory of this script
DEPLOYMENT_MODULE_DIR="$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")"
PROJECT_ROOT="$(dirname "$DEPLOYMENT_MODULE_DIR")"
DEPLOY_SCRIPT="${PROJECT_ROOT}/tools/deployment/deploy-to-droplet-enhanced.sh"

# Check if deployment script exists
check_deployment_script() {
    if [ ! -f "$DEPLOY_SCRIPT" ]; then
        echo "Error: Deployment script not found at: $DEPLOY_SCRIPT"
        return 1
    fi
    
    if [ ! -x "$DEPLOY_SCRIPT" ]; then
        echo "Making deployment script executable..."
        chmod +x "$DEPLOY_SCRIPT"
    fi
    
    return 0
}

# Deploy: Check SSH connection
deploy_check_ssh() {
    show_header "Deploy: Check SSH Connection"
    
    if ! check_deployment_script; then
        press_any_key
        return 1
    fi
    
    echo "Testing SSH connection to droplet..."
    echo ""
    
    # Just run the --help which also tests SSH
    "$DEPLOY_SCRIPT" --help
    
    local exit_code=$?
    if [ $exit_code -eq 0 ]; then
        echo ""
        echo -e "${GREEN}✓ SSH connection is working${NC}"
    else
        echo ""
        echo -e "${RED}✗ SSH connection failed${NC}"
        echo "Please check your SSH configuration in tools/deployment/.env"
    fi
    
    press_any_key
}

# Deploy: Dry run (show what will be deployed)
deploy_dry_run() {
    show_header "Deploy: Dry Run"
    
    if ! check_deployment_script; then
        press_any_key
        return 1
    fi
    
    echo "This will show what changes would be deployed without actually deploying."
    echo ""
    read -p "Proceed with dry run? (y/N): " confirm
    
    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        echo "Dry run cancelled."
        press_any_key
        return 0
    fi
    
    echo ""
    echo "Running dry run..."
    echo ""
    
    "$DEPLOY_SCRIPT" --dry-run
    
    press_any_key
}

# Deploy: Sync files to droplet
deploy_sync() {
    show_header "Deploy: Sync to Droplet"
    
    if ! check_deployment_script; then
        press_any_key
        return 1
    fi
    
    echo "This will deploy WillowCMS to the droplet."
    echo "Changed files will be synced using rsync."
    echo ""
    echo -e "${YELLOW}WARNING: This will modify files on the production server.${NC}"
    echo ""
    read -p "Proceed with deployment? (y/N): " confirm
    
    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        echo "Deployment cancelled."
        press_any_key
        return 0
    fi
    
    echo ""
    echo "Deploying to droplet..."
    echo ""
    
    "$DEPLOY_SCRIPT" --yes
    
    local exit_code=$?
    echo ""
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✓ Deployment completed successfully${NC}"
    else
        echo -e "${RED}✗ Deployment failed${NC}"
    fi
    
    press_any_key
}

# Deploy: Sync with delete (purge remote files not in local)
deploy_purge() {
    show_header "Deploy: Sync with Purge"
    
    if ! check_deployment_script; then
        press_any_key
        return 1
    fi
    
    echo "This will deploy WillowCMS to the droplet AND remove files"
    echo "on the remote server that don't exist locally."
    echo ""
    echo -e "${RED}⚠️  DANGER: This is a destructive operation!${NC}"
    echo -e "${RED}    Files on the server will be deleted if not present locally.${NC}"
    echo ""
    echo "It's recommended to:"
    echo "1. Run a dry run first: deploy:dry-run"
    echo "2. Ensure you have a backup of the remote server"
    echo ""
    read -p "Type 'DELETE' to confirm purge deployment: " confirm
    
    if [[ "$confirm" != "DELETE" ]]; then
        echo "Purge deployment cancelled."
        press_any_key
        return 0
    fi
    
    echo ""
    echo "Deploying to droplet with purge..."
    echo ""
    
    "$DEPLOY_SCRIPT" --delete --yes
    
    local exit_code=$?
    echo ""
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✓ Purge deployment completed${NC}"
    else
        echo -e "${RED}✗ Purge deployment failed${NC}"
    fi
    
    press_any_key
}

# Deploy: Set ownership on remote files
deploy_chown() {
    show_header "Deploy: Set Remote Ownership"
    
    if ! check_deployment_script; then
        press_any_key
        return 1
    fi
    
    # Load deployment environment
    ENV_FILE="${PROJECT_ROOT}/tools/deployment/.env"
    if [ -f "$ENV_FILE" ]; then
        set -a
        source "$ENV_FILE"
        set +a
    else
        echo "Error: Environment file not found: $ENV_FILE"
        press_any_key
        return 1
    fi
    
    # Expand tilde
    SSH_KEY_PATH="${SSH_KEY_PATH/#\~/$HOME}"
    DROPLET_IP="${DROPLET_IP:-}"
    SSH_USER="${SSH_USER:-deploy}"
    SSH_PORT="${SSH_PORT:-22}"
    REMOTE_DST="${REMOTE_DST:-/volume1/docker/whatismyadapter}"
    
    if [ -z "$DROPLET_IP" ]; then
        echo "Error: DROPLET_IP not set in .env"
        press_any_key
        return 1
    fi
    
    echo "This will set proper ownership on remote files."
    echo ""
    echo "Target: $SSH_USER@$DROPLET_IP:$REMOTE_DST"
    echo ""
    read -p "Proceed with setting ownership? (y/N): " confirm
    
    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        echo "Operation cancelled."
        press_any_key
        return 0
    fi
    
    echo ""
    echo "Setting ownership on remote files..."
    
    SSH_CMD="ssh -p ${SSH_PORT} -i ${SSH_KEY_PATH} -o BatchMode=yes -o IdentitiesOnly=yes"
    
    $SSH_CMD "${SSH_USER}@${DROPLET_IP}" "
        cd ${REMOTE_DST}
        
        # Check if whatismyadapter user exists
        if id -u whatismyadapter &>/dev/null; then
            echo 'Setting ownership to whatismyadapter:100'
            sudo chown -R whatismyadapter:100 .
        else
            echo 'Setting ownership to 1034:100'
            sudo chown -R 1034:100 .
        fi
        
        # Set proper permissions for CakePHP
        chmod -R 755 app/ 2>/dev/null || true
        chmod -R 777 app/tmp/ 2>/dev/null || true
        chmod -R 777 app/logs/ 2>/dev/null || true
        
        echo 'Ownership and permissions set successfully'
    "
    
    local exit_code=$?
    echo ""
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✓ Ownership set successfully${NC}"
    else
        echo -e "${RED}✗ Failed to set ownership${NC}"
    fi
    
    press_any_key
}

# View deployment logs
deploy_view_logs() {
    show_header "Deploy: View Logs"
    
    LOGS_DIR="${PROJECT_ROOT}/tools/deployment/logs"
    
    if [ ! -d "$LOGS_DIR" ]; then
        echo "No deployment logs found."
        echo "Logs directory: $LOGS_DIR"
        press_any_key
        return 0
    fi
    
    # Find recent log files
    LOG_FILES=$(find "$LOGS_DIR" -name "deploy-*.log" -type f 2>/dev/null | sort -r | head -10)
    
    if [ -z "$LOG_FILES" ]; then
        echo "No deployment logs found in: $LOGS_DIR"
        press_any_key
        return 0
    fi
    
    echo "Recent deployment logs:"
    echo ""
    
    local index=1
    while IFS= read -r log_file; do
        local basename=$(basename "$log_file")
        local size=$(ls -lh "$log_file" | awk '{print $5}')
        local date=$(echo "$basename" | sed 's/deploy-\(.*\)\.log/\1/')
        
        echo "$index) $date ($size)"
        index=$((index + 1))
    done <<< "$LOG_FILES"
    
    echo ""
    echo "0) Back to menu"
    echo ""
    read -p "Select log to view (0-$((index-1))): " selection
    
    if [ "$selection" = "0" ]; then
        return 0
    fi
    
    if [ "$selection" -ge 1 ] && [ "$selection" -lt "$index" ]; then
        local selected_log=$(echo "$LOG_FILES" | sed -n "${selection}p")
        clear
        echo "Viewing: $(basename "$selected_log")"
        echo "=================================="
        echo ""
        less "$selected_log"
    else
        echo "Invalid selection."
    fi
    
    press_any_key
}

# Main deployment menu
deployment_menu() {
    while true; do
        show_header "Deployment Management"
        
        echo "1) Check SSH Connection"
        echo "2) Dry Run (Preview Changes)"
        echo "3) Deploy to Droplet"
        echo "4) Deploy with Purge (⚠️  Dangerous)"
        echo "5) Set Remote Ownership"
        echo "6) View Deployment Logs"
        echo ""
        echo "0) Back to Main Menu"
        echo ""
        read -p "Select an option: " choice
        
        case $choice in
            1) deploy_check_ssh ;;
            2) deploy_dry_run ;;
            3) deploy_sync ;;
            4) deploy_purge ;;
            5) deploy_chown ;;
            6) deploy_view_logs ;;
            0) return 0 ;;
            *) 
                echo "Invalid option. Please try again."
                sleep 1
                ;;
        esac
    done
}

# Export functions for use in main menu
export -f check_deployment_script
export -f deploy_check_ssh
export -f deploy_dry_run
export -f deploy_sync
export -f deploy_purge
export -f deploy_chown
export -f deploy_view_logs
export -f deployment_menu
