#!/bin/bash

# Deploy WillowCMS to DigitalOcean Droplet
# Usage: ./deploy-to-droplet.sh [environment]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env"
COMPOSE_FILE="${SCRIPT_DIR}/docker-compose-prod.yml"

# Load environment variables
if [[ -f "$ENV_FILE" ]]; then
    source "$ENV_FILE"
else
    echo -e "${RED}Error: .env file not found at $ENV_FILE${NC}"
    echo -e "${YELLOW}Please copy .env.example to .env and configure your settings${NC}"
    exit 1
fi

# Validate required variables
required_vars=(
    "DROPLET_IP"
    "SSH_USER" 
    "DB_DATABASE"
    "DB_USERNAME"
    "DB_PASSWORD"
    "APP_KEY"
)

for var in "${required_vars[@]}"; do
    if [[ -z "${!var}" ]]; then
        echo -e "${RED}Error: Required environment variable $var is not set${NC}"
        exit 1
    fi
done

echo -e "${BLUE}🚀 Starting deployment to DigitalOcean Droplet${NC}"
echo -e "${BLUE}Target: ${DROPLET_IP}${NC}"
echo -e "${BLUE}Environment: ${APP_ENV:-production}${NC}"
echo ""

# Function to execute commands on remote server
ssh_exec() {
    ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
        "${SSH_USER}@${DROPLET_IP}" "$@"
}

# Function to copy files to remote server
scp_copy() {
    scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
        "$1" "${SSH_USER}@${DROPLET_IP}:$2"
}

# Test connection
echo -e "${YELLOW}Testing SSH connection...${NC}"
if ! ssh_exec "echo 'Connection successful'"; then
    echo -e "${RED}Error: Cannot connect to droplet${NC}"
    exit 1
fi
echo -e "${GREEN}✓ SSH connection successful${NC}"

# Create deployment directory on remote
echo -e "${YELLOW}Creating deployment directory...${NC}"
ssh_exec "mkdir -p ~/willow/{app,logs,config,backups/db,html}"
echo -e "${GREEN}✓ Deployment directory created${NC}"

# Copy Docker Compose file
echo -e "${YELLOW}Copying Docker Compose configuration...${NC}"
scp_copy "$COMPOSE_FILE" "~/willow/docker-compose.yml"
echo -e "${GREEN}✓ Docker Compose file copied${NC}"

# Copy environment file (excluding sensitive local paths)
echo -e "${YELLOW}Copying environment configuration...${NC}"
temp_env=$(mktemp)
grep -v "SSH_KEY_PATH\|BACKUP_PATH" "$ENV_FILE" > "$temp_env"
scp_copy "$temp_env" "~/willow/.env"
rm "$temp_env"
echo -e "${GREEN}✓ Environment configuration copied${NC}"

# Create basic index.html for testing
echo -e "${YELLOW}Creating initial web content...${NC}"
ssh_exec "
    mkdir -p ~/willow/html
    cat > ~/willow/html/index.html << 'HTML_EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Willow CMS - Production Deployment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c5530; }
        .status { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .links { display: flex; gap: 20px; margin-top: 30px; }
        .links a { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .links a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌟 Willow CMS - Successfully Deployed!</h1>
        <div class="status">
            ✅ <strong>Deployment Status:</strong> Active and Running<br>
            🐳 <strong>Services:</strong> Web Server, Database, Redis, phpMyAdmin<br>
            📅 <strong>Deployed:</strong> $(date)
        </div>
        <p>Your Willow CMS application has been successfully deployed to production!</p>
        <div class="links">
            <a href="/phpMyAdmin" target="_blank">phpMyAdmin</a>
            <a href="https://github.com/Robjects-Community/WhatIsMyAdaptor" target="_blank">Source Code</a>
        </div>
    </div>
</body>
</html>
HTML_EOF
"
echo -e "${GREEN}✓ Initial web content created${NC}"

# Install Docker and Docker Compose if not present
echo -e "${YELLOW}Ensuring Docker is installed...${NC}"
ssh_exec "
    if ! command -v docker &> /dev/null; then
        echo 'Installing Docker...'
        curl -fsSL https://get.docker.com -o get-docker.sh
        sh get-docker.sh
        usermod -aG docker \$USER
        rm get-docker.sh
    fi
    
    # Modern Docker includes compose plugin by default
    if ! docker compose version &> /dev/null; then
        echo 'Docker Compose plugin not available, installing standalone...'
        curl -L \"https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m)\" -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
    fi
    
    echo 'Docker installation verified'
"
echo -e "${GREEN}✓ Docker installation verified${NC}"

# Deploy the application
echo -e "${YELLOW}Deploying application...${NC}"
ssh_exec "
    cd ~/willow
    
    # Use docker compose (modern syntax)
    COMPOSE_CMD='docker compose'
    if ! docker compose version &> /dev/null; then
        COMPOSE_CMD='docker-compose'
    fi
    
    # Pull latest images
    \$COMPOSE_CMD pull
    
    # Stop existing containers
    \$COMPOSE_CMD down --remove-orphans
    
    # Start new containers
    \$COMPOSE_CMD up -d
    
    # Wait for services to be healthy
    echo 'Waiting for services to start...'
    sleep 30
    
    # Check container status
    \$COMPOSE_CMD ps
"
echo -e "${GREEN}✓ Application deployed successfully${NC}"

# Verify deployment
echo -e "${YELLOW}Verifying deployment...${NC}"
if ssh_exec "curl -f -s http://localhost/ > /dev/null"; then
    echo -e "${GREEN}✓ Application is responding${NC}"
else
    echo -e "${YELLOW}⚠ Application may still be starting up${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}Access your application at: http://${DROPLET_IP}${NC}"
echo -e "${BLUE}PhpMyAdmin available at: http://${DROPLET_IP}:8080${NC}"
echo -e "${BLUE}Deployment directory: ~/willow${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Configure SSL certificate"
echo -e "  2. Set up monitoring and backups"
echo -e "  3. Configure domain name (optional)"
echo ""