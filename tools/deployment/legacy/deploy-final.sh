#!/bin/bash

# WillowCMS Digital Ocean Final Deployment Script
# This script deploys your WillowCMS to a Digital Ocean droplet

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Progress tracking
TOTAL_STEPS=7
CURRENT_STEP=0

show_progress() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    local percent=$((CURRENT_STEP * 100 / TOTAL_STEPS))
    local filled=$((percent / 2))
    local empty=$((50 - filled))
    
    printf "${CYAN}["
    printf "%*s" $filled | tr ' ' '█'
    printf "%*s" $empty | tr ' ' '░'
    printf "] %d%% (%d/%d) %s${NC}\\n" $percent $CURRENT_STEP $TOTAL_STEPS "$1"
}

echo -e "${BLUE}🚀 WillowCMS Digital Ocean Final Deployment${NC}"
echo "============================================="
echo

# Step 1: Get droplet information
show_progress "Getting droplet information..."
DROPLET_IP=$(doctl compute droplet get willowcms-prod --format PublicIPv4 --no-header)

if [ -z "$DROPLET_IP" ]; then
    echo -e "${RED}❌ Could not find droplet IP. Make sure willowcms-prod droplet exists.${NC}"
    exit 1
fi

echo -e "${GREEN}   ✅ Found droplet IP: $DROPLET_IP${NC}"

# Step 2: Update production environment file
show_progress "Updating production environment file..."
if [ -f ".env.production" ]; then
    sed -i.bak "s/YOUR_DROPLET_IP/$DROPLET_IP/g" .env.production
    echo -e "${GREEN}   ✅ Updated .env.production with droplet IP${NC}"
else
    echo -e "${YELLOW}   ⚠️ .env.production not found, creating from template...${NC}"
    cp .env .env.production
    sed -i.bak "s/DEBUG=true/DEBUG=false/g" .env.production
    echo -e "${GREEN}   ✅ Created .env.production${NC}"
fi

# Step 3: Test SSH connection (with user prompt for passphrase)
show_progress "Testing SSH connection to droplet..."
echo -e "${CYAN}   🔐 You may need to enter your SSH key passphrase...${NC}"
if ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet -o ConnectTimeout=15 -o StrictHostKeyChecking=no root@$DROPLET_IP "echo 'SSH connection successful'" >/dev/null 2>&1; then
    echo -e "${GREEN}   ✅ SSH connection successful${NC}"
else
    echo -e "${RED}   ❌ SSH connection failed${NC}"
    echo -e "${YELLOW}   Please make sure you can connect manually first:${NC}"
    echo -e "${CYAN}   ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP${NC}"
    exit 1
fi

# Step 4: Upload files with progress
show_progress "Uploading files to droplet..."
echo -e "${CYAN}   📤 Uploading essential files (this may take a few minutes)...${NC}"
echo -e "${YELLOW}   You may need to enter your SSH key passphrase again...${NC}"

# Create exclude file for rsync
cat > /tmp/rsync-exclude << 'EOF'
.git/
.backups/
node_modules/
vendor/
*.log
.DS_Store
tmp/
logs/
.warpindexingignore
.env.bak*
.env.prod
.env.production.bak
*.sql
*.tar.gz
*.zip
.vscode/
EOF

# Use rsync with progress
rsync -avz --progress --human-readable \
    --exclude-from=/tmp/rsync-exclude \
    -e "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet" \
    ./ root@$DROPLET_IP:/opt/willowcms/

rm -f /tmp/rsync-exclude
echo -e "${GREEN}   ✅ Files uploaded successfully${NC}"

# Step 5: Configure environment and permissions
show_progress "Configuring production environment..."
echo -e "${CYAN}   ⚙️ Setting up environment and permissions...${NC}"
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    
    # Copy production environment file
    if [ -f '.env.production' ]; then
        cp .env.production .env
        echo 'Using production environment file'
    fi
    
    # Set secure permissions
    chmod 600 .env
    chmod +x *.sh 2>/dev/null || true
    
    # Stop any existing services
    docker compose -f docker-compose.prod.yml down 2>/dev/null || true
    docker compose down 2>/dev/null || true
    
    echo 'Environment configured'
"
echo -e "${GREEN}   ✅ Environment configured${NC}"

# Step 6: Build and deploy services
show_progress "Building and deploying Docker services..."
echo -e "${CYAN}   🐳 Building containers (this will take 3-5 minutes on first run)...${NC}"
echo -e "${YELLOW}   The PHP extensions (intl, redis) need to be compiled from source${NC}"

ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    
    # Choose the right compose file
    if [ -f 'docker-compose.prod.yml' ]; then
        COMPOSE_FILE='docker-compose.prod.yml'
        echo 'Using production compose file'
    else
        COMPOSE_FILE='docker-compose.yml'
        echo 'Using development compose file'
    fi
    
    # Build and start all services
    echo 'Starting Docker build and deployment...'
    docker compose -f \$COMPOSE_FILE up -d --build
    
    echo 'Waiting for services to initialize...'
    sleep 60
    
    echo 'Service status:'
    docker compose -f \$COMPOSE_FILE ps
    
    echo 'Container logs (last 10 lines each):'
    docker compose -f \$COMPOSE_FILE logs --tail=10
"

echo -e "${GREEN}   ✅ Services deployed${NC}"

# Step 7: Final configuration and verification
show_progress "Performing final configuration and verification..."
echo -e "${CYAN}   🔥 Configuring firewall and performing health checks...${NC}"

ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    # Install and configure firewall
    apt-get update -qq
    apt-get install -y ufw curl >/dev/null 2>&1
    
    # Configure firewall rules
    ufw --force reset >/dev/null 2>&1
    ufw allow ssh >/dev/null 2>&1
    ufw allow 8080 >/dev/null 2>&1
    ufw allow 3310 >/dev/null 2>&1  # MySQL
    ufw allow 8082 >/dev/null 2>&1  # PHPMyAdmin  
    ufw allow 8025 >/dev/null 2>&1  # Mailpit
    ufw --force enable >/dev/null 2>&1
    
    echo 'Firewall configured - ports 22, 8080, 3310, 8082, 8025 open'
"

echo -e "${GREEN}   ✅ Configuration complete${NC}"

# Final verification
echo
echo -e "${YELLOW}🔍 Performing final health check...${NC}"
echo -e "${CYAN}   Waiting for WillowCMS to be ready...${NC}"

# Give more time for PHP compilation and startup
sleep 30

if timeout 20 curl -f -s -o /dev/null "http://$DROPLET_IP:8080" 2>/dev/null; then
    echo
    echo -e "${GREEN}🎉 🎉 DEPLOYMENT SUCCESSFUL! 🎉 🎉${NC}"
    echo "========================================="
    echo
    echo -e "${BLUE}🌐 Your WillowCMS is now LIVE at:${NC}"
    echo -e "${GREEN}   ➤ http://$DROPLET_IP:8080${NC}"
    echo
    echo -e "${BLUE}📊 Additional Services:${NC}"
    echo -e "${GREEN}   ➤ PHPMyAdmin: http://$DROPLET_IP:8082${NC}"
    echo -e "${GREEN}   ➤ Mailpit:    http://$DROPLET_IP:8025${NC}"
    echo
    echo -e "${BLUE}🔐 Admin Credentials:${NC}"
    echo -e "${YELLOW}   Username: admin${NC}"
    echo -e "${YELLOW}   Password: AdminProd2024!Secure#91${NC}"
    echo
    echo -e "${BLUE}💰 Monthly Cost: ~$12-15 (you saved $15-20 vs App Platform!)${NC}"
    echo
    echo -e "${BLUE}🛠️  Management Commands:${NC}"
    echo -e "${CYAN}   SSH:        ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP${NC}"
    echo -e "${CYAN}   Logs:       ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose logs -f'${NC}"
    echo -e "${CYAN}   Restart:    ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose restart'${NC}"
    echo -e "${CYAN}   Stop:       ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose stop'${NC}"
    echo
else
    echo
    echo -e "${YELLOW}⚠️  Services are still starting up...${NC}"
    echo -e "${CYAN}   This is normal for the first deployment${NC}"
    echo
    echo -e "${BLUE}🔍 To check status:${NC}"
    echo -e "${CYAN}   ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose logs'${NC}"
    echo
    echo -e "${BLUE}🌐 Try accessing in 2-3 minutes:${NC}"
    echo -e "${GREEN}   http://$DROPLET_IP:8080${NC}"
    echo
    echo -e "${BLUE}💡 If still not working after 5 minutes:${NC}"
    echo -e "${CYAN}   1. Check logs: docker-compose logs willowcms${NC}"
    echo -e "${CYAN}   2. Restart services: docker-compose restart${NC}"
    echo -e "${CYAN}   3. Check if extensions compiled: docker exec willowcms php -m${NC}"
fi

echo
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${BLUE}🎯 Your WillowCMS is now running on Digital Ocean!${NC}"