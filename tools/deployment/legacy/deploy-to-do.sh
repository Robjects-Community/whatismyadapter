#!/bin/bash

# WillowCMS Digital Ocean Deployment Script
# This script deploys your WillowCMS to a Digital Ocean droplet

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 WillowCMS Digital Ocean Deployment Script${NC}"
echo "============================================="

# Get droplet IP
echo -e "${YELLOW}📡 Getting droplet information...${NC}"
DROPLET_IP=$(doctl compute droplet get willowcms-prod --format PublicIPv4 --no-header)

if [ -z "$DROPLET_IP" ]; then
    echo -e "${RED}❌ Could not find droplet IP. Make sure willowcms-prod droplet exists.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Found droplet IP: $DROPLET_IP${NC}"

# Update production env file with actual IP
echo -e "${YELLOW}🔧 Updating production environment file...${NC}"
sed -i.bak "s/YOUR_DROPLET_IP/$DROPLET_IP/g" .env.production
echo -e "${GREEN}✅ Updated .env.production with droplet IP${NC}"

# Test SSH connection
echo -e "${YELLOW}🔐 Testing SSH connection to droplet...${NC}"
if ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet -o ConnectTimeout=10 -o StrictHostKeyChecking=no root@$DROPLET_IP "echo 'SSH connection successful'" 2>/dev/null; then
    echo -e "${GREEN}✅ SSH connection successful${NC}"
else
    echo -e "${RED}❌ Cannot connect to droplet via SSH${NC}"
    echo "Please check:"
    echo "1. Droplet is running"
    echo "2. SSH key is correct"
    echo "3. Firewall allows SSH (port 22)"
    exit 1
fi

# Create deployment directory on droplet
echo -e "${YELLOW}📁 Creating deployment directory on droplet...${NC}"
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "mkdir -p /opt/willowcms"

# Copy files to droplet
echo -e "${YELLOW}📤 Uploading files to droplet...${NC}"
rsync -avz -e "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='*.log' \
    --exclude='.DS_Store' \
    ./ root@$DROPLET_IP:/opt/willowcms/

# Copy production env file
echo -e "${YELLOW}⚙️ Setting up production environment...${NC}"
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    cp .env.production .env
    chmod 600 .env
"

# Install and start services
echo -e "${YELLOW}🐳 Starting Docker services on droplet...${NC}"
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    cd /opt/willowcms
    
    # Stop any existing services
    docker-compose -f docker-compose.prod.yml down 2>/dev/null || true
    
    # Start services (basic services only, no debug tools)
    docker-compose -f docker-compose.prod.yml up -d
    
    # Wait for services to start
    echo 'Waiting for services to start...'
    sleep 30
    
    # Check service status
    docker-compose -f docker-compose.prod.yml ps
"

# Configure firewall
echo -e "${YELLOW}🔥 Configuring firewall...${NC}"
ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP "
    # Install ufw if not present
    apt-get update -qq && apt-get install -y ufw
    
    # Reset firewall rules
    ufw --force reset
    
    # Allow SSH (port 22)
    ufw allow ssh
    
    # Allow WillowCMS (port 8080)
    ufw allow 8080
    
    # Allow MySQL external access (port 3310) - optional
    ufw allow 3310
    
    # Allow PHPMyAdmin (port 8082) - optional for debugging
    ufw allow 8082
    
    # Enable firewall
    ufw --force enable
    
    # Show status
    ufw status
"

# Check deployment
echo -e "${YELLOW}🔍 Checking deployment status...${NC}"
sleep 10

# Test if WillowCMS is responding
if curl -f -s -o /dev/null "http://$DROPLET_IP:8080"; then
    echo -e "${GREEN}✅ WillowCMS is responding!${NC}"
    echo ""
    echo -e "${BLUE}🎉 Deployment completed successfully!${NC}"
    echo "============================================="
    echo -e "${GREEN}Your WillowCMS is now running at:${NC}"
    echo -e "${BLUE}http://$DROPLET_IP:8080${NC}"
    echo ""
    echo -e "${YELLOW}Additional services:${NC}"
    echo -e "PHPMyAdmin: ${BLUE}http://$DROPLET_IP:8082${NC}"
    echo -e "Mailpit: ${BLUE}http://$DROPLET_IP:8025${NC}"
    echo ""
    echo -e "${YELLOW}Admin credentials:${NC}"
    echo -e "Username: ${GREEN}admin${NC}"
    echo -e "Password: ${GREEN}AdminProd2024!Secure#91${NC}"
    echo ""
    echo -e "${YELLOW}SSH Access:${NC}"
    echo -e "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP"
    echo ""
    echo -e "${YELLOW}Monitor services:${NC}"
    echo -e "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP 'cd /opt/willowcms && docker-compose -f docker-compose.prod.yml logs -f'"
else
    echo -e "${RED}❌ WillowCMS is not responding yet${NC}"
    echo -e "${YELLOW}This might be normal - the containers may still be starting up.${NC}"
    echo ""
    echo -e "${YELLOW}Check logs with:${NC}"
    echo "ssh -i ~/.ssh/id_ed25519_digital_ocean_droplet root@$DROPLET_IP 'cd /opt/willowcms && docker-compose -f docker-compose.prod.yml logs'"
    echo ""
    echo -e "${YELLOW}Try accessing:${NC}"
    echo -e "${BLUE}http://$DROPLET_IP:8080${NC}"
    echo ""
    echo -e "${YELLOW}Wait a few more minutes and try again.${NC}"
fi

echo ""
echo -e "${GREEN}✅ Deployment script completed!${NC}"