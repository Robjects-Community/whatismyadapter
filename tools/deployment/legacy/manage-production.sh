#!/bin/bash

# WillowCMS Production Management Script for Digital Ocean
# This script provides common management tasks for your deployed WillowCMS

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
DROPLET_NAME="willowcms-prod"
SSH_KEY="~/.ssh/id_ed25519_digital_ocean_droplet"
DEPLOY_DIR="/opt/willowcms"

# Get droplet IP
get_droplet_ip() {
    doctl compute droplet get $DROPLET_NAME --format PublicIPv4 --no-header
}

# SSH to droplet
ssh_to_droplet() {
    local DROPLET_IP=$(get_droplet_ip)
    ssh -i $SSH_KEY root@$DROPLET_IP "$@"
}

# Show usage
show_usage() {
    echo -e "${BLUE}WillowCMS Production Management${NC}"
    echo "================================="
    echo ""
    echo "Usage: $0 <command>"
    echo ""
    echo "Commands:"
    echo -e "  ${GREEN}status${NC}      - Check service status"
    echo -e "  ${GREEN}logs${NC}        - View application logs"
    echo -e "  ${GREEN}restart${NC}     - Restart all services"
    echo -e "  ${GREEN}shell${NC}       - SSH into the droplet"
    echo -e "  ${GREEN}backup${NC}      - Create manual backup"
    echo -e "  ${GREEN}update${NC}      - Deploy updated code"
    echo -e "  ${GREEN}monitor${NC}     - Show resource usage"
    echo -e "  ${GREEN}health${NC}      - Health check"
    echo -e "  ${GREEN}info${NC}        - Show droplet info"
    echo ""
}

# Check service status
check_status() {
    echo -e "${BLUE}Checking WillowCMS service status...${NC}"
    
    ssh_to_droplet "
        cd $DEPLOY_DIR
        echo 'Docker Services:'
        docker compose ps
        echo ''
        echo 'System Resources:'
        free -h
        df -h / | tail -1
        echo ''
        echo 'Recent logs (last 10 lines):'
        docker compose logs --tail=10
    "
}

# View logs
view_logs() {
    echo -e "${BLUE}Viewing WillowCMS logs...${NC}"
    echo -e "${YELLOW}Press Ctrl+C to exit${NC}"
    
    ssh_to_droplet "
        cd $DEPLOY_DIR
        docker compose logs -f
    "
}

# Restart services
restart_services() {
    echo -e "${BLUE}Restarting WillowCMS services...${NC}"
    
    ssh_to_droplet "
        cd $DEPLOY_DIR
        docker compose restart
        echo 'Services restarted. Waiting for startup...'
        sleep 30
        docker compose ps
    "
    
    echo -e "${GREEN}Services restarted successfully${NC}"
}

# SSH into droplet
ssh_shell() {
    local DROPLET_IP=$(get_droplet_ip)
    echo -e "${BLUE}Connecting to droplet...${NC}"
    ssh -i $SSH_KEY root@$DROPLET_IP
}

# Create manual backup
create_backup() {
    echo -e "${BLUE}Creating manual backup...${NC}"
    
    ssh_to_droplet "
        cd $DEPLOY_DIR
        BACKUP_NAME=\"willowcms-manual-\$(date +%Y%m%d-%H%M%S)\"
        
        echo 'Creating database backup...'
        docker compose exec -T mysql mysqldump -u root -p\$MYSQL_ROOT_PASSWORD \$MYSQL_DATABASE > \$BACKUP_NAME-db.sql
        
        echo 'Creating file backup...'
        tar -czf \$BACKUP_NAME-files.tar.gz --exclude='*.log' --exclude='tmp/*' ./
        
        echo 'Backup files created:'
        ls -lh \$BACKUP_NAME*
        
        echo 'To download backups:'
        echo \"scp -i $SSH_KEY root@\$(hostname -I | awk '{print \$1}'):$DEPLOY_DIR/\$BACKUP_NAME* ./\"
    "
}

# Update deployment
update_deployment() {
    echo -e "${BLUE}Updating WillowCMS deployment...${NC}"
    echo -e "${YELLOW}This will run the deployment script again${NC}"
    
    read -p "Are you sure you want to update? (y/N): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        ./deploy-final.sh
    else
        echo "Update cancelled."
    fi
}

# Monitor resources
monitor_resources() {
    echo -e "${BLUE}Resource monitoring...${NC}"
    
    ssh_to_droplet "
        echo '=== System Resources ==='
        free -h
        echo ''
        echo '=== Disk Usage ==='
        df -h
        echo ''
        echo '=== Docker Resources ==='
        docker stats --no-stream
        echo ''
        echo '=== Network Connections ==='
        netstat -tuln | grep -E ':(80|8080|3306|6379|22)'
        echo ''
        echo '=== Load Average ==='
        uptime
    "
}

# Health check
health_check() {
    echo -e "${BLUE}Performing health check...${NC}"
    
    local DROPLET_IP=$(get_droplet_ip)
    
    echo "1. Checking WillowCMS web response..."
    if curl -f -s -o /dev/null "http://$DROPLET_IP:8080" --max-time 10; then
        echo -e "${GREEN}✅ WillowCMS is responding${NC}"
    else
        echo -e "${RED}❌ WillowCMS is not responding${NC}"
    fi
    
    echo ""
    echo "2. Checking service health..."
    ssh_to_droplet "
        cd $DEPLOY_DIR
        
        echo 'Docker service status:'
        if docker compose ps | grep -q 'Up'; then
            echo '✅ Docker services are running'
        else
            echo '❌ Some Docker services are down'
        fi
        
        echo ''
        echo 'Database connectivity:'
        if docker compose exec -T mysql mysql -u root -p\$MYSQL_ROOT_PASSWORD -e 'SELECT 1;' &>/dev/null; then
            echo '✅ Database is accessible'
        else
            echo '❌ Database connection failed'
        fi
        
        echo ''
        echo 'Redis connectivity:'
        if docker compose exec -T redis redis-cli -a \$REDIS_PASSWORD ping &>/dev/null; then
            echo '✅ Redis is accessible'
        else
            echo '❌ Redis connection failed'
        fi
    "
}

# Show droplet info
show_info() {
    echo -e "${BLUE}WillowCMS Droplet Information${NC}"
    echo "==============================="
    
    local DROPLET_IP=$(get_droplet_ip)
    doctl compute droplet get $DROPLET_NAME --format Name,Status,PublicIPv4,PrivateIPv4,Memory,VCPUs,Disk,Region,Features
    
    echo ""
    echo -e "${BLUE}Access URLs:${NC}"
    echo -e "WillowCMS:   ${GREEN}http://$DROPLET_IP:8080${NC}"
    echo -e "PHPMyAdmin:  ${GREEN}http://$DROPLET_IP:8082${NC}"
    echo -e "Mailpit:     ${GREEN}http://$DROPLET_IP:8025${NC}"
    
    echo ""
    echo -e "${BLUE}SSH Access:${NC}"
    echo -e "Command: ${CYAN}ssh -i $SSH_KEY root@$DROPLET_IP${NC}"
    
    echo ""
    echo -e "${BLUE}Admin Credentials:${NC}"
    echo -e "Username: ${YELLOW}admin${NC}"
    echo -e "Password: ${YELLOW}AdminProd2024!Secure#91${NC}"
}

# Main command handling
case "${1:-}" in
    status)
        check_status
        ;;
    logs)
        view_logs
        ;;
    restart)
        restart_services
        ;;
    shell)
        ssh_shell
        ;;
    backup)
        create_backup
        ;;
    update)
        update_deployment
        ;;
    monitor)
        monitor_resources
        ;;
    health)
        health_check
        ;;
    info)
        show_info
        ;;
    "")
        show_usage
        ;;
    *)
        echo -e "${RED}Unknown command: $1${NC}"
        show_usage
        exit 1
        ;;
esac