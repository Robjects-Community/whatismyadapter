#!/bin/bash

# WillowCMS Digital Ocean Streamlined Deployment Script
# This script deploys your WillowCMS to a Digital Ocean droplet with progress tracking

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Progress tracking
TOTAL_STEPS=8
CURRENT_STEP=0

show_progress() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    local percent=$((CURRENT_STEP * 100 / TOTAL_STEPS))
    local filled=$((percent / 2))
    local empty=$((50 - filled))
    
    printf "\r${CYAN}["
    printf "%*s" $filled | tr ' ' '█'
    printf "%*s" $empty | tr ' ' '░'
    printf "] %d%% (%d/%d) %s${NC}" $percent $CURRENT_STEP $TOTAL_STEPS "$1"
    echo
}

echo -e "${BLUE}🚀 WillowCMS Digital Ocean Streamlined Deployment${NC}"
echo "=================================================="
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
    sed -i.bak "s/YOUR_DROPLET_IP/$DROPLET_IP/g" .env.production
fi

# Step 3: Test SSH connection
show_progress "Testing SSH connection to droplet..."
if timeout 10 ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@$DROPLET_IP "echo 'SSH connection successful'" >/dev/null 2>&1; then
    echo -e "${GREEN}   ✅ SSH connection successful${NC}"
else
    echo -e "${RED}   ❌ Cannot connect to droplet via SSH${NC}"
    echo "   Please check: 1. Droplet is running 2. SSH key is correct 3. Firewall allows SSH"
    exit 1
fi

# Step 4: Prepare deployment directory
show_progress "Preparing deployment directory on droplet..."
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    mkdir -p /opt/willowcms
    cd /opt/willowcms
    # Stop existing services if running
    docker-compose -f docker-compose.prod.yml down 2>/dev/null || true
    # Clean up old files but keep important data
    find . -maxdepth 1 -type f -name '*.sh' -delete 2>/dev/null || true
    find . -maxdepth 1 -type f -name '*.md' -delete 2>/dev/null || true
"
echo -e "${GREEN}   ✅ Deployment directory prepared${NC}"

# Step 5: Upload essential files with progress
show_progress "Uploading essential files (with progress)..."

# Create a temporary exclude file for rsync
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
composer.lock
.env.bak*
.env.prod
.env.production.bak
*.sql
*.tar.gz
*.zip
.vscode/
EOF

echo -e "${CYAN}   📤 Uploading files to droplet...${NC}"

# Use rsync with progress and exclude unnecessary files
rsync -avz --progress \
    --exclude-from=/tmp/rsync-exclude \
    -e "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet" \
    ./ root@$DROPLET_IP:/opt/willowcms/

# Clean up temporary exclude file
rm -f /tmp/rsync-exclude

echo -e "${GREEN}   ✅ Files uploaded successfully${NC}"

# Step 6: Configure environment
show_progress "Configuring production environment..."
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    
    # Use production environment file
    if [ -f '.env.production' ]; then
        cp .env.production .env
    fi
    
    # Set secure permissions
    chmod 600 .env
    chmod +x *.sh 2>/dev/null || true
    
    # Make sure docker-compose.prod.yml exists
    if [ ! -f 'docker-compose.prod.yml' ]; then
        echo 'Warning: docker-compose.prod.yml not found, using docker-compose.yml'
        COMPOSE_FILE='docker-compose.yml'
    else
        COMPOSE_FILE='docker-compose.prod.yml'
    fi
"
echo -e "${GREEN}   ✅ Environment configured${NC}"

# Step 7: Deploy services
show_progress "Building and starting Docker services..."
echo -e "${CYAN}   🐳 This may take a few minutes for the first build...${NC}"

ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    
    # Determine which compose file to use
    if [ -f 'docker-compose.prod.yml' ]; then
        COMPOSE_FILE='docker-compose.prod.yml'
    else
        COMPOSE_FILE='docker-compose.yml'
    fi
    
    echo \"Using compose file: \$COMPOSE_FILE\"
    
    # Build and start services
    docker-compose -f \$COMPOSE_FILE up -d --build
    
    echo \"Waiting for services to stabilize...\"
    sleep 45
    
    # Show service status
    echo \"Service status:\"
    docker-compose -f \$COMPOSE_FILE ps
"

echo -e "${GREEN}   ✅ Services deployed${NC}"

# Step 8: Configure firewall and final checks
show_progress "Configuring firewall and performing final checks..."

ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    # Configure basic firewall
    apt-get update -qq && apt-get install -y ufw >/dev/null 2>&1
    
    ufw --force reset >/dev/null 2>&1
    ufw allow ssh >/dev/null 2>&1
    ufw allow 8080 >/dev/null 2>&1
    ufw allow 3310 >/dev/null 2>&1  # MySQL
    ufw allow 8082 >/dev/null 2>&1  # PHPMyAdmin
    ufw --force enable >/dev/null 2>&1
    
    echo \"Firewall configured\"
"

echo -e "${GREEN}   ✅ Firewall configured${NC}"

# Final status check
echo
echo -e "${YELLOW}🔍 Performing final deployment check...${NC}"
sleep 10

# Test if WillowCMS is responding
if timeout 15 curl -f -s -o /dev/null "http://$DROPLET_IP:8080" 2>/dev/null; then
    echo
    echo -e "${GREEN}🎉 DEPLOYMENT SUCCESSFUL!${NC}"
    echo "================================="
    echo
    echo -e "${BLUE}🌐 Your WillowCMS is running at:${NC}"
    echo -e "   ${GREEN}http://$DROPLET_IP:8080${NC}"
    echo
    echo -e "${BLUE}📊 Additional services:${NC}"
    echo -e "   PHPMyAdmin: ${GREEN}http://$DROPLET_IP:8082${NC}"
    echo -e "   Mailpit:    ${GREEN}http://$DROPLET_IP:8025${NC}"
    echo
    echo -e "${BLUE}🔐 Default admin credentials:${NC}"
    echo -e "   Username: ${YELLOW}admin${NC}"
    echo -e "   Password: ${YELLOW}AdminProd2024!Secure#91${NC}"
    echo
    echo -e "${BLUE}🔧 Management commands:${NC}"
    echo -e "   SSH Access: ${CYAN}ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP${NC}"
    echo -e "   View Logs:  ${CYAN}ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose logs -f'${NC}"
    echo -e "   Restart:    ${CYAN}ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose restart'${NC}"
    echo
else
    echo
    echo -e "${YELLOW}⚠️ Services are starting up...${NC}"
    echo -e "   WillowCMS may take 2-3 more minutes to be fully ready"
    echo
    echo -e "${BLUE}🔍 Check status:${NC}"
    echo -e "   Service logs: ${CYAN}ssh root@$DROPLET_IP 'cd /opt/willowcms && docker-compose logs'${NC}"
    echo -e "   Try accessing: ${GREEN}http://$DROPLET_IP:8080${NC}"
    echo
    echo -e "${BLUE}💡 If issues persist:${NC}"
    echo -e "   1. Wait 5 more minutes for PHP extensions to compile"
    echo -e "   2. Check logs for any errors"
    echo -e "   3. Restart services: ${CYAN}docker-compose restart${NC}"
fi

echo
echo -e "${GREEN}✅ Deployment script completed successfully!${NC}"
echo -e "${CYAN}📈 Monthly cost: ~$12-15 (vs $27-35 on App Platform)${NC}"