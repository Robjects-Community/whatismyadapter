#!/bin/bash

# WillowCMS Portainer Stack Quick Deploy Helper
# This script helps generate secure environment variables and provides deployment commands

set -e

echo "🚀 WillowCMS Portainer Stack Quick Deploy Helper"
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to generate random password
generate_password() {
    local length=${1:-16}
    openssl rand -base64 $length | tr -d "=+/" | cut -c1-$length
}

# Function to generate security salt
generate_salt() {
    openssl rand -base64 48 | tr -d "=+/" | cut -c1-32
}

echo
echo -e "${BLUE}1. Generating Secure Passwords${NC}"
echo "================================"

SECURITY_SALT=$(generate_salt)
MYSQL_ROOT_PASSWORD=$(generate_password 20)
MYSQL_PASSWORD=$(generate_password 16)
REDIS_PASSWORD=$(generate_password 16)
ADMIN_PASSWORD=$(generate_password 12)
COMMANDER_PASSWORD=$(generate_password 12)

echo -e "${GREEN}✅ Generated secure passwords${NC}"
echo

echo -e "${BLUE}2. Environment Variables to Set in Portainer${NC}"
echo "=============================================="
echo
echo -e "${YELLOW}⚠️  CRITICAL: Copy these to your Portainer stack environment variables:${NC}"
echo
cat << EOF
# === REQUIRED SECURITY SETTINGS ===
SECURITY_SALT=${SECURITY_SALT}
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
MYSQL_PASSWORD=${MYSQL_PASSWORD}
REDIS_PASSWORD=${REDIS_PASSWORD}
WILLOW_ADMIN_PASSWORD=${ADMIN_PASSWORD}
REDIS_COMMANDER_PASSWORD=${COMMANDER_PASSWORD}

# === REQUIRED APPLICATION SETTINGS ===
APP_FULL_BASE_URL=https://whatisyouradapter.robjects.me
WILLOW_ADMIN_EMAIL=admin@your-domain.com

# === OPTIONAL SETTINGS (customize as needed) ===
APP_NAME=WillowCMS
MYSQL_DATABASE=willow_cms
MYSQL_USER=willow_user
REDIS_USERNAME=willow_redis
WILLOW_ADMIN_USERNAME=admin
REDIS_COMMANDER_USERNAME=admin
DEBUG=false
WILLOW_HTTP_PORT=8080
MYSQL_PORT=3310
PMA_HTTP_PORT=8082
MAILPIT_HTTP_PORT=8025
REDIS_COMMANDER_HTTP_PORT=8084
EOF

echo
echo -e "${BLUE}3. Portainer Deployment Instructions${NC}"
echo "====================================="
echo
echo -e "1. ${GREEN}Login to Portainer${NC} and navigate to 'Stacks'"
echo -e "2. ${GREEN}Click 'Add stack'${NC}"
echo -e "3. ${GREEN}Choose 'Repository'${NC} as the build method"
echo -e "4. ${GREEN}Configure repository:${NC}"
echo -e "   - Repository URL: ${YELLOW}https://github.com/garzarobm/willow.git${NC}"
echo -e "   - Repository reference: ${YELLOW}droplet-deploy${NC}"
echo -e "   - Compose path: ${YELLOW}portainer-stacks/docker-compose.yml${NC}"
echo -e "5. ${GREEN}Add environment variables${NC} (from step 2 above)"
echo -e "6. ${GREEN}Deploy the stack${NC}"

echo
echo -e "${BLUE}4. Post-Deployment Access${NC}"
echo "========================="
echo
echo -e "After deployment, access your services:"
echo -e "- ${GREEN}WillowCMS Application:${NC} http://your-server:8080"
echo -e "- ${GREEN}Admin Login:${NC} admin / ${ADMIN_PASSWORD}"
echo -e "- ${GREEN}phpMyAdmin:${NC} http://your-server:8082"
echo -e "- ${GREEN}Mailpit:${NC} http://your-server:8025"
echo -e "- ${GREEN}Redis Commander:${NC} http://your-server:8084 (admin / ${COMMANDER_PASSWORD})"

echo
echo -e "${BLUE}5. Portainer API Deployment (Alternative)${NC}"
echo "==========================================="
echo
echo -e "If you prefer to deploy via API, use this template:"
echo
cat << 'EOF'
curl -X POST \
  http://your-portainer-url:9000/api/stacks \
  -H 'Authorization: Bearer YOUR-API-TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "willowcms",
    "repositoryURL": "https://github.com/garzarobm/willow.git",
    "repositoryReferenceName": "droplet-deploy",
    "composeFilePathInRepository": "portainer-stacks/docker-compose.yml",
    "repositoryAuthentication": false,
    "env": [
EOF

echo "      {\"name\": \"SECURITY_SALT\", \"value\": \"${SECURITY_SALT}\"},"
echo "      {\"name\": \"MYSQL_ROOT_PASSWORD\", \"value\": \"${MYSQL_ROOT_PASSWORD}\"},"
echo "      {\"name\": \"MYSQL_PASSWORD\", \"value\": \"${MYSQL_PASSWORD}\"},"
echo "      {\"name\": \"REDIS_PASSWORD\", \"value\": \"${REDIS_PASSWORD}\"},"
echo "      {\"name\": \"WILLOW_ADMIN_PASSWORD\", \"value\": \"${ADMIN_PASSWORD}\"},"
echo "      {\"name\": \"APP_FULL_BASE_URL\", \"value\": \"https://your-domain.com\"},"
echo "      {\"name\": \"WILLOW_ADMIN_EMAIL\", \"value\": \"admin@your-domain.com\"}"

cat << 'EOF'
    ]
  }'
EOF

echo
echo -e "${BLUE}6. Security Reminders${NC}"
echo "===================="
echo
echo -e "${RED}🔐 IMPORTANT SECURITY NOTES:${NC}"
echo -e "- ${YELLOW}Change 'your-domain.com' to your actual domain${NC}"
echo -e "- ${YELLOW}Use HTTPS in production with proper SSL certificates${NC}"
echo -e "- ${YELLOW}Restrict port access using firewall rules${NC}"
echo -e "- ${YELLOW}Keep these passwords secure and private${NC}"
echo -e "- ${YELLOW}Consider using a reverse proxy (Nginx/Traefik) for SSL termination${NC}"

echo
echo -e "${GREEN}✅ Ready for deployment! Follow the instructions above.${NC}"
echo