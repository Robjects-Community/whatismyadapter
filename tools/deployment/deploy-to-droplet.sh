#!/bin/bash

# Deploy WillowCMS to DigitalOcean Droplet
# Usage: ./deploy-to-droplet.sh [environment]
# Can be run from any directory - paths are calculated dynamically

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Determine paths dynamically (works from any directory)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEPLOYMENT_DIR="$REPO_ROOT/tools/deployment"
APP_DIR="$REPO_ROOT/app"
INFRA_DIR="$REPO_ROOT/infrastructure"

# Configuration
ENV_FILE="$DEPLOYMENT_DIR/.env"
COMPOSE_FILE="$DEPLOYMENT_DIR/docker-compose-prod.yml"

# Validate required directories exist
for dir in "$APP_DIR" "$INFRA_DIR" "$DEPLOYMENT_DIR"; do
    if [[ ! -d "$dir" ]]; then
        echo -e "${RED}Error: Required directory not found: $dir${NC}"
        echo -e "${YELLOW}Please ensure you're running from the WillowCMS repository${NC}"
        exit 1
    fi
done

# Load environment variables
if [[ -f "$ENV_FILE" ]]; then
    source "$ENV_FILE"
else
    echo -e "${RED}Error: .env file not found at $ENV_FILE${NC}"
    echo -e "${YELLOW}Please copy .env.example to .env and configure your settings${NC}"
    exit 1
fi

# Defaults
SSH_PORT="${SSH_PORT:-22}"

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

# Resolve SSH identity (optional)
SSH_ID_FILE=""
if [[ -n "${SSH_KEY_PATH}" ]]; then
    # Expand ~ if present
    SSH_ID_FILE="${SSH_KEY_PATH/#~/$HOME}"
    if [[ ! -f "${SSH_ID_FILE}" ]]; then
        echo -e "${YELLOW}Warning: SSH_KEY_PATH is set to '${SSH_KEY_PATH}' but file not found after expansion ('${SSH_ID_FILE}'). Falling back to default SSH identity/agent.${NC}"
        SSH_ID_FILE=""
    fi
fi

# Build common SSH options
SSH_COMMON_OPTS=(
  -o StrictHostKeyChecking=no
  -o UserKnownHostsFile=/dev/null
  -p "${SSH_PORT}"
)
if [[ -n "${SSH_ID_FILE}" ]]; then
  SSH_COMMON_OPTS+=( -i "${SSH_ID_FILE}" )
fi

# Show which key/port will be used (non-sensitive)
if [[ -n "${SSH_ID_FILE}" ]]; then
  echo -e "${BLUE}Using SSH identity: ${SSH_ID_FILE}${NC}"
fi
if [[ "${SSH_PORT}" != "22" ]]; then
  echo -e "${BLUE}Using SSH port: ${SSH_PORT}${NC}"
fi

echo -e "${BLUE}🚀 Starting deployment to DigitalOcean Droplet${NC}"
echo -e "${BLUE}Target: ${DROPLET_IP}${NC}"
echo -e "${BLUE}Environment: ${APP_ENV:-production}${NC}"
echo ""

# Function to execute commands on remote server
ssh_exec() {
    ssh "${SSH_COMMON_OPTS[@]}" "${SSH_USER}@${DROPLET_IP}" "$@"
}

# Function to copy files to remote server
scp_copy() {
    # scp uses -P for port; reuse identity and -o options
    local SCP_OPTS=()
    # Extract identity (-i) from SSH_COMMON_OPTS if present
    if [[ -n "${SSH_ID_FILE}" ]]; then
      SCP_OPTS+=( -i "${SSH_ID_FILE}" )
    fi
    SCP_OPTS+=( -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -P "${SSH_PORT}" )
    scp "${SCP_OPTS[@]}" "$1" "${SSH_USER}@${DROPLET_IP}:$2"
}

# Test connection
echo -e "${YELLOW}Testing SSH connection...${NC}"
if ! ssh_exec "echo 'Connection successful'"; then
    echo -e "${RED}Error: Cannot connect to droplet${NC}"
    echo -e "${YELLOW}Hint: Ensure the public key corresponding to SSH_KEY_PATH is in ~${SSH_USER}/.ssh/authorized_keys on the droplet and file permissions are correct (700 ~/.ssh, 600 authorized_keys).${NC}"
    exit 1
fi
echo -e "${GREEN}✓ SSH connection successful${NC}"

# Create deployment directory on remote
echo -e "${YELLOW}Creating deployment directory...${NC}"
ssh_exec "mkdir -p ~/willow/{app,logs,config,backups/db,html,infrastructure}"
echo -e "${GREEN}✓ Deployment directory created${NC}"

# Copy Docker Compose file
echo -e "${YELLOW}Copying Docker Compose configuration...${NC}"
scp_copy "$COMPOSE_FILE" "~/willow/docker-compose.yml"
echo -e "${GREEN}✓ Docker Compose file copied${NC}"

# Copy CakePHP application files
echo -e "${YELLOW}Copying CakePHP application...${NC}"
scp -r -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
    "$APP_DIR" "${SSH_USER}@${DROPLET_IP}:~/willow/"
echo -e "${GREEN}✓ CakePHP application copied${NC}"

# Copy Docker infrastructure
echo -e "${YELLOW}Copying Docker infrastructure...${NC}"
scp -r -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
    "$INFRA_DIR" "${SSH_USER}@${DROPLET_IP}:~/willow/"
echo -e "${GREEN}✓ Docker infrastructure copied${NC}"

# Copy environment file (excluding sensitive local paths)
echo -e "${YELLOW}Copying environment configuration...${NC}"
temp_env=$(mktemp)
trap "rm -f '$temp_env'" EXIT
grep -v "SSH_KEY_PATH\|BACKUP_PATH" "$ENV_FILE" > "$temp_env"
scp_copy "$temp_env" "~/willow/.env"
rm -f "$temp_env"
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
    
    # Stop and remove all existing containers - more thorough approach
    echo 'Stopping and removing all existing containers...'
    \$COMPOSE_CMD down --remove-orphans --volumes || true
    
    # Force remove any containers with conflicting names (all possible variants)
    echo 'Force removing containers with conflicting names...'
    docker ps -a --format 'table {{.Names}}' | grep -E '(willow|nginx|mariadb|redis|phpmyadmin)' | xargs -r docker rm -f 2>/dev/null || true
    
    # Clean up any dangling containers from previous deployments
    docker container prune -f || true
    
    # Pull latest images
    \$COMPOSE_CMD pull
    
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