#!/bin/bash

# Deploy WillowCMS to DigitalOcean Droplet - Enhanced Version
# Works correctly from any directory in the repository
# Usage: ./tools/deployment/deploy-to-droplet-enhanced.sh [options]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Determine script location and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

# Validate we're in the willow project
if [[ ! -f "${PROJECT_ROOT}/docker-compose.yml" ]] || [[ ! -d "${PROJECT_ROOT}/app" ]]; then
    echo -e "${RED}Error: This script must be run from the willow repository${NC}"
    echo -e "${RED}Expected project root: ${PROJECT_ROOT}${NC}"
    exit 1
fi

echo -e "${BLUE}🚀 WillowCMS Deployment Script (Enhanced)${NC}"
echo -e "${CYAN}Project root: ${PROJECT_ROOT}${NC}"
echo -e "${CYAN}Script location: ${SCRIPT_DIR}${NC}"
echo ""

# Configuration files
ENV_FILE="${SCRIPT_DIR}/.env"
DEPLOY_IGNORE_FILE="${PROJECT_ROOT}/.deployignore"
COMPOSE_FILE="${SCRIPT_DIR}/docker-compose-prod.yml"

# Create default .deployignore if not exists
if [[ ! -f "${DEPLOY_IGNORE_FILE}" ]]; then
    echo -e "${YELLOW}Creating default .deployignore file...${NC}"
    cat > "${DEPLOY_IGNORE_FILE}" << 'EOF'
# VCS and IDE
.git/
.github/
.gitignore
.gitattributes
.vscode/
.idea/
.DS_Store
*.swp
*.swo

# Development dependencies
node_modules/
vendor/
coverage/
dist/
build/

# CakePHP runtime (recreated on server)
app/tmp/*
app/logs/*
!app/tmp/.gitkeep
!app/logs/.gitkeep

# Local development files
docs/
scripts/
tools/
tests/
storage/backups/
helper-files*/
archive-files/
docker-backups/
assets/presentations/

# Docker compose files (we copy specific ones)
docker-compose*.yml
docker-compose-prod.yml

# Environment files (we handle separately)
.env
.env.*
!.env.example
stack.env

# Logs and temporary files
*.log
*.cache
*.pid
*.seed
*.sql
*.sqlite

# Backup files
*.backup
*.bak
*.orig
*~
EOF
    echo -e "${GREEN}✓ Created .deployignore file${NC}"
fi

# Load environment variables
if [[ -f "${ENV_FILE}" ]]; then
    set -a
    source "${ENV_FILE}"
    set +a
    echo -e "${GREEN}✓ Loaded environment from ${ENV_FILE}${NC}"
elif [[ -f "${PROJECT_ROOT}/.env" ]]; then
    set -a
    source "${PROJECT_ROOT}/.env"
    set +a
    echo -e "${YELLOW}⚠ Using project .env file${NC}"
else
    echo -e "${RED}Error: No .env file found${NC}"
    echo -e "${YELLOW}Please create ${ENV_FILE} from .env.example${NC}"
    exit 1
fi

# Set defaults and validate
DROPLET_IP="${DROPLET_IP:-}"
SSH_USER="${SSH_USER:-deploy}"
SSH_PORT="${SSH_PORT:-22}"
SSH_KEY_PATH="${SSH_KEY_PATH:-${HOME}/.ssh/id_ed25519}"
REMOTE_DST="${REMOTE_DST:-/Volumes/1TB_DAVINCI/docker}"

# Expand tilde in SSH_KEY_PATH
SSH_KEY_PATH="${SSH_KEY_PATH/#\~/$HOME}"

# Validate required variables
if [[ -z "${DROPLET_IP}" ]]; then
    echo -e "${RED}Error: DROPLET_IP is not set in .env${NC}"
    exit 1
fi

# Check SSH key
if [[ ! -f "${SSH_KEY_PATH}" ]]; then
    echo -e "${RED}Error: SSH key not found at ${SSH_KEY_PATH}${NC}"
    echo -e "${YELLOW}To create a new key:${NC}"
    echo -e "  ssh-keygen -t ed25519 -f ~/.ssh/willow_deploy_key -N ''"
    echo -e "  Then update SSH_KEY_PATH in ${ENV_FILE}"
    exit 1
fi

echo -e "${BLUE}Deployment Configuration:${NC}"
echo -e "  Target: ${SSH_USER}@${DROPLET_IP}:${SSH_PORT}"
echo -e "  SSH Key: ${SSH_KEY_PATH}"
echo -e "  Remote Path: ${REMOTE_DST}"
echo -e "  Environment: ${APP_ENV:-production}"
echo ""

# Build SSH command
SSH_CMD="ssh -p ${SSH_PORT} -i ${SSH_KEY_PATH} -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"
SCP_CMD="scp -P ${SSH_PORT} -i ${SSH_KEY_PATH} -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"

# Function to execute commands on remote server
ssh_exec() {
    ${SSH_CMD} "${SSH_USER}@${DROPLET_IP}" "$@"
}

# Function to check rsync availability
check_rsync() {
    if ! command -v rsync &> /dev/null; then
        echo -e "${YELLOW}Warning: rsync not found locally${NC}"
        if [[ "$(uname)" == "Darwin" ]]; then
            echo -e "${CYAN}On macOS, rsync should be pre-installed. Checking alternative...${NC}"
            if command -v /usr/bin/rsync &> /dev/null; then
                RSYNC_CMD="/usr/bin/rsync"
            else
                echo -e "${RED}Error: rsync is required but not found${NC}"
                echo -e "${YELLOW}Install with: brew install rsync${NC}"
                exit 1
            fi
        else
            echo -e "${RED}Error: rsync is required but not found${NC}"
            exit 1
        fi
    else
        RSYNC_CMD="rsync"
    fi
    
    # Check remote rsync
    if ! ssh_exec "command -v rsync &> /dev/null"; then
        echo -e "${YELLOW}Installing rsync on remote server...${NC}"
        ssh_exec "sudo apt-get update && sudo apt-get install -y rsync" || {
            echo -e "${RED}Failed to install rsync on remote${NC}"
            exit 1
        }
    fi
}

# Test SSH connection
echo -e "${YELLOW}Testing SSH connection...${NC}"
if ! ssh_exec "echo 'Connection successful'"; then
    echo -e "${RED}Error: Cannot connect to droplet${NC}"
    echo -e "${YELLOW}Debug info:${NC}"
    echo "  SSH command: ${SSH_CMD} ${SSH_USER}@${DROPLET_IP}"
    echo -e "${YELLOW}Troubleshooting:${NC}"
    echo "  1. Ensure SSH key is added to remote ~/.ssh/authorized_keys"
    echo "  2. Check firewall allows port ${SSH_PORT}"
    echo "  3. Verify droplet IP: ${DROPLET_IP}"
    echo "  4. Try manually: ssh -v -p ${SSH_PORT} -i ${SSH_KEY_PATH} ${SSH_USER}@${DROPLET_IP}"
    exit 1
fi
echo -e "${GREEN}✓ SSH connection successful${NC}"

# Check for rsync
check_rsync
echo -e "${GREEN}✓ rsync available on both systems${NC}"

# Parse command line options
DRY_RUN=false
FORCE_SYNC=false
SKIP_CONFIRM=false
USE_DELETE=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --force)
            FORCE_SYNC=true
            SKIP_CONFIRM=true
            shift
            ;;
        --yes|-y)
            SKIP_CONFIRM=true
            shift
            ;;
        --delete)
            USE_DELETE=true
            shift
            ;;
        --help|-h)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --dry-run    Show what would be synced without making changes"
            echo "  --force      Skip confirmation and sync immediately"
            echo "  --yes, -y    Skip confirmation prompts"
            echo "  --delete     Remove files on remote that don't exist locally"
            echo "  --help, -h   Show this help message"
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            exit 1
            ;;
    esac
done

# Build rsync command
RSYNC_OPTS="-azP --stats --human-readable"
RSYNC_OPTS="${RSYNC_OPTS} --exclude-from=${DEPLOY_IGNORE_FILE}"

if [[ "${USE_DELETE}" == "true" ]]; then
    RSYNC_OPTS="${RSYNC_OPTS} --delete --delete-after"
    echo -e "${YELLOW}⚠ Warning: --delete flag enabled, remote files will be removed${NC}"
fi

# Dry run to show what will be synced
if [[ "${DRY_RUN}" == "true" ]] || [[ "${SKIP_CONFIRM}" != "true" ]]; then
    echo -e "${CYAN}Performing dry run to show changes...${NC}"
    echo ""
    
    TEMP_LOG=$(mktemp)
    ${RSYNC_CMD} ${RSYNC_OPTS} --dry-run --itemize-changes \
        -e "${SSH_CMD}" \
        "${PROJECT_ROOT}/" \
        "${SSH_USER}@${DROPLET_IP}:${REMOTE_DST}/" 2>&1 | tee "${TEMP_LOG}"
    
    # Parse statistics
    echo ""
    echo -e "${CYAN}=== Sync Summary ===${NC}"
    grep -E "Number of|Total file size|Total transferred" "${TEMP_LOG}" || true
    
    # Count changes
    CREATED=$(grep -c ">f+++++++++" "${TEMP_LOG}" 2>/dev/null | tr -d '\n' || echo "0")
    TOTAL_SYNCED=$(grep -c ">f" "${TEMP_LOG}" 2>/dev/null | tr -d '\n' || echo "0")
    # Ensure variables are numeric for arithmetic
    CREATED=${CREATED:-0}
    TOTAL_SYNCED=${TOTAL_SYNCED:-0}
    UPDATED=$((TOTAL_SYNCED - CREATED))
    DELETED=$(grep -c "*deleting" "${TEMP_LOG}" 2>/dev/null || echo "0")
    
    echo -e "${BLUE}Files to create: ${CREATED}${NC}"
    echo -e "${BLUE}Files to update: ${UPDATED}${NC}"
    if [[ "${USE_DELETE}" == "true" ]]; then
        echo -e "${YELLOW}Files to delete: ${DELETED}${NC}"
    fi
    rm -f "${TEMP_LOG}"
    
    if [[ "${DRY_RUN}" == "true" ]]; then
        echo ""
        echo -e "${GREEN}Dry run completed. No changes were made.${NC}"
        exit 0
    fi
    
    if [[ "${SKIP_CONFIRM}" != "true" ]]; then
        echo ""
        read -p "Proceed with deployment? (y/N) " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo -e "${YELLOW}Deployment cancelled${NC}"
            exit 0
        fi
    fi
fi

# Create remote directory structure
echo -e "${YELLOW}Ensuring remote directory structure...${NC}"
ssh_exec "mkdir -p ${REMOTE_DST}/{app,logs,config,backups,html,infrastructure,storage,tmp}"
echo -e "${GREEN}✓ Remote directories ready${NC}"

# Perform the sync
echo -e "${BLUE}Starting file synchronization...${NC}"
echo ""

LOG_FILE="${SCRIPT_DIR}/logs/deploy-$(date +%Y%m%d-%H%M%S).log"
mkdir -p "${SCRIPT_DIR}/logs"

${RSYNC_CMD} ${RSYNC_OPTS} \
    -e "${SSH_CMD}" \
    "${PROJECT_ROOT}/" \
    "${SSH_USER}@${DROPLET_IP}:${REMOTE_DST}/" 2>&1 | tee "${LOG_FILE}"

echo ""
echo -e "${GREEN}✓ File synchronization completed${NC}"

# Generate checksum for log
if command -v shasum &> /dev/null; then
    shasum -a 256 "${LOG_FILE}" > "${LOG_FILE}.sha256"
    echo -e "${CYAN}Log saved: ${LOG_FILE}${NC}"
    echo -e "${CYAN}Checksum: $(cat ${LOG_FILE}.sha256 | awk '{print $1}')${NC}"
fi

# Copy Docker Compose production file
echo -e "${YELLOW}Copying Docker Compose production configuration...${NC}"
if [[ -f "${COMPOSE_FILE}" ]]; then
    ${SCP_CMD} "${COMPOSE_FILE}" "${SSH_USER}@${DROPLET_IP}:${REMOTE_DST}/docker-compose.yml"
    echo -e "${GREEN}✓ Docker Compose configuration copied${NC}"
else
    echo -e "${YELLOW}⚠ Production compose file not found at ${COMPOSE_FILE}${NC}"
fi

# Copy environment configuration
echo -e "${YELLOW}Copying environment configuration...${NC}"
TEMP_ENV=$(mktemp)
# Filter out local-specific variables
grep -v -E "(SSH_KEY_PATH|BACKUP_PATH|LOCAL_|HOME|PWD)" "${ENV_FILE}" > "${TEMP_ENV}"
${SCP_CMD} "${TEMP_ENV}" "${SSH_USER}@${DROPLET_IP}:${REMOTE_DST}/.env"
rm -f "${TEMP_ENV}"
echo -e "${GREEN}✓ Environment configuration copied${NC}"

# Set ownership and permissions
echo -e "${YELLOW}Setting file ownership and permissions...${NC}"
ssh_exec "
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
    chmod 644 .env 2>/dev/null || true
    
    # Make scripts executable
    find . -name '*.sh' -exec chmod +x {} \; 2>/dev/null || true
"
echo -e "${GREEN}✓ File permissions set${NC}"

# Deploy with Docker Compose
echo -e "${YELLOW}Deploying application with Docker Compose...${NC}"
ssh_exec "
    cd ${REMOTE_DST}
    
    # Prefer modern docker compose syntax
    if docker compose version &> /dev/null; then
        COMPOSE_CMD='docker compose'
    else
        COMPOSE_CMD='docker-compose'
    fi
    
    echo 'Stopping existing services...'
    \$COMPOSE_CMD down --remove-orphans 2>/dev/null || true
    
    echo 'Starting services...'
    \$COMPOSE_CMD up -d --remove-orphans
    
    echo 'Waiting for services to initialize...'
    sleep 10
    
    # Install composer dependencies if vendor is missing
    if [[ ! -d app/vendor ]]; then
        echo 'Installing composer dependencies...'
        \$COMPOSE_CMD exec -T willowcms composer install --no-dev --optimize-autoloader || true
    fi
    
    # Clear caches
    echo 'Clearing application caches...'
    \$COMPOSE_CMD exec -T willowcms bin/cake cache clear_all || true
    
    echo 'Service status:'
    \$COMPOSE_CMD ps
"
echo -e "${GREEN}✓ Application deployed${NC}"

# Verify deployment
echo -e "${YELLOW}Verifying deployment...${NC}"
if ssh_exec "curl -f -s http://localhost/ > /dev/null 2>&1"; then
    echo -e "${GREEN}✓ Application is responding${NC}"
else
    echo -e "${YELLOW}⚠ Application may still be starting up${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}Access your application at: http://${DROPLET_IP}${NC}"
echo -e "${BLUE}Remote path: ${REMOTE_DST}${NC}"
echo ""
echo -e "${CYAN}Useful commands:${NC}"
echo "  View logs: ssh ${SSH_USER}@${DROPLET_IP} 'cd ${REMOTE_DST} && docker compose logs -f'"
echo "  Check status: ssh ${SSH_USER}@${DROPLET_IP} 'cd ${REMOTE_DST} && docker compose ps'"
echo "  Enter container: ssh ${SSH_USER}@${DROPLET_IP} 'cd ${REMOTE_DST} && docker compose exec willowcms bash'"
echo ""
