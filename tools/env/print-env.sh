#!/bin/bash
# Environment Printer for WillowCMS Deployments
# Displays active environment configuration with masked secrets

set -euo pipefail

# Script directory and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
ENV_LINK="${PROJECT_ROOT}/.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to mask secrets
mask_secret() {
    local value="$1"
    local show_chars="${2:-3}"
    
    if [[ -z "$value" ]]; then
        echo "not-set"
        return
    fi
    
    local length=${#value}
    if [[ $length -le $show_chars ]]; then
        echo "***"
    else
        local visible_part="${value:0:$show_chars}"
        echo "${visible_part}***"
    fi
}

# Check if .env exists
if [[ ! -e "$ENV_LINK" ]]; then
    log_error "No active environment found"
    log_error "Run './tools/env/switch-env.sh <local|remote>' first"
    exit 1
fi

# Check if .env is a symlink
if [[ ! -L "$ENV_LINK" ]]; then
    log_warn ".env exists but is not a symlink"
    log_warn "Consider running './tools/env/switch-env.sh <local|remote>' to manage environments properly"
fi

# Determine environment type from symlink target
ENV_TYPE="unknown"
if [[ -L "$ENV_LINK" ]]; then
    ENV_TARGET="$(readlink "$ENV_LINK")"
    case "$ENV_TARGET" in
        *local.env)
            ENV_TYPE="local"
            ;;
        *remote.env)
            ENV_TYPE="remote"
            ;;
    esac
fi

# Source the environment file
set +u  # Allow unset variables temporarily
source "$ENV_LINK"
set -u

# Header
echo
echo -e "${CYAN}╔══════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║                    WillowCMS Environment Configuration              ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════════════════════╝${NC}"
echo

# Basic Information
log_info "Environment Type: ${ENV_TYPE}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Active File:          $(readlink -f "$ENV_LINK" 2>/dev/null || echo "$ENV_LINK")"
echo "Project Name:         ${PROJECT_NAME:-not-set}"
echo "App Environment:      ${APP_ENV:-not-set}"
echo "Debug Mode:           ${DEBUG:-not-set}"
echo "App Name:             ${APP_NAME:-not-set}"
echo "App Encoding:         ${APP_ENCODING:-not-set}"
echo "Default Locale:       ${APP_DEFAULT_LOCALE:-not-set}"
echo "Default Timezone:     ${APP_DEFAULT_TIMEZONE:-not-set}"
echo "Base URL:             ${APP_FULL_BASE_URL:-not-set}"
echo

# Security (masked)
echo "🔐 Security Settings:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Security Salt:        $(mask_secret "${SECURITY_SALT:-}" 6)"
echo "Admin Username:       ${WILLOW_ADMIN_USERNAME:-not-set}"
echo "Admin Password:       $(mask_secret "${WILLOW_ADMIN_PASSWORD:-}")"
echo "Admin Email:          ${WILLOW_ADMIN_EMAIL:-not-set}"
echo

# Database Configuration
echo "🗃️  Database Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Database URL:         $(mask_secret "${DATABASE_URL:-}" 20)"
echo "DB Host:              ${DB_HOST:-not-set}"
echo "DB Port:              ${DB_PORT:-not-set}"
echo "DB Username:          ${DB_USERNAME:-not-set}"
echo "DB Password:          $(mask_secret "${DB_PASSWORD:-}")"
echo "DB Database:          ${DB_DATABASE:-not-set}"
echo "MySQL Root Password:  $(mask_secret "${MYSQL_ROOT_PASSWORD:-}")"
echo "MySQL Image Tag:      ${MYSQL_IMAGE_TAG:-not-set}"
echo

# Docker & Compose
echo "🐳 Docker & Compose Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Network Name:         ${NETWORK_NAME:-not-set}"
echo "Volume Mode:          ${VOLUME_MODE:-not-set}"
echo "Volume Driver:        ${VOLUME_DRIVER:-not-set}"
echo "App Service:          ${WILLOW_APP_SERVICE:-not-set}"
echo "DB Service:           ${WILLOW_DB_SERVICE:-not-set}"
echo "Redis Service:        ${WILLOW_REDIS_SERVICE:-not-set}"
echo

# Port Mappings
echo "🌐 Port Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Willow HTTP Port:     ${WILLOW_HTTP_PORT:-not-set}"
echo "Willow HTTPS Port:    ${WILLOW_HTTPS_PORT:-not-set}"
echo "MySQL Port:           ${MYSQL_PORT:-not-set}"
echo "PhpMyAdmin Port:      ${PMA_HTTP_PORT:-not-set}"
echo "Redis Commander:      ${REDIS_COMMANDER_HTTP_PORT:-not-set}"
echo "Mailpit SMTP:         ${MAILPIT_SMTP_PORT:-not-set}"
echo "Mailpit HTTP:         ${MAILPIT_HTTP_PORT:-not-set}"
echo "Jenkins HTTP:         ${JENKINS_HTTP_PORT:-not-set}"
echo

# Git & Deployment
echo "🔄 Git & Deployment Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Git Remote Name:      ${GIT_REMOTE_NAME:-not-set}"
echo "Git URL:              ${GIT_URL:-not-set}"
echo "Git Reference:        ${GIT_REF:-not-set}"
echo "Stack File Path:      ${STACK_FILE_PATH:-not-set}"
echo "Auto Pull:            ${AUTO_PULL:-not-set}"
echo

# Environment-specific settings
if [[ "$ENV_TYPE" == "local" ]]; then
    echo "💻 Local Environment Settings:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Code Directory:       ${WILLOW_CODE_DIR:-not-set}"
    echo "Storage Directory:    ${WILLOW_STORAGE_DIR:-not-set}"
    echo "DB Directory:         ${WILLOW_DB_DIR:-not-set}"
    echo "Nginx Logs Dir:       ${WILLOW_NGINX_LOGS_DIR:-not-set}"
elif [[ "$ENV_TYPE" == "remote" ]]; then
    echo "☁️  Remote/Portainer Settings:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Portainer URL:        ${PORTAINER_URL:-not-set}"
    echo "Portainer Endpoint:   ${PORTAINER_ENDPOINT_ID:-not-set}"
    echo "Stack Name:           ${STACK_NAME:-not-set}"
    echo "TLS Verify:           ${PORTAINER_TLS_VERIFY:-not-set}"
    echo "API Token File:       ${PORTAINER_API_TOKEN_FILE:-not-set}"
fi
echo

# Redis Configuration  
echo "⚡ Redis Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Redis Host:           ${REDIS_HOST:-not-set}"
echo "Redis Port:           ${REDIS_PORT:-not-set}"
echo "Redis Username:       ${REDIS_USERNAME:-not-set}"
echo "Redis Password:       $(mask_secret "${REDIS_PASSWORD:-}")"
echo "Redis Database:       ${REDIS_DATABASE:-not-set}"
echo "Redis URL:            $(mask_secret "${REDIS_URL:-}" 15)"
echo "Redis Tag:            ${REDIS_TAG:-not-set}"
echo

# External API Keys (masked)
echo "🔑 External API Keys:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "OpenAI API Key:       $(mask_secret "${OPENAI_API_KEY:-}")"
echo "YouTube API Key:      $(mask_secret "${YOUTUBE_API_KEY:-}")"
echo "Translate API Key:    $(mask_secret "${TRANSLATE_API_KEY:-}")"
echo

# Health Check Configuration
echo "🏥 Health Check Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Health Check URLs:    ${HEALTHCHECK_URLS:-not-set}"
echo "Health Check Timeout: ${HEALTHCHECK_TIMEOUT:-not-set}"
echo "DB Health Check:      ${DB_HEALTHCHECK:-not-set}"
echo

# Logging & Backup
echo "📝 Logging & Backup Configuration:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Logs Directory:       ${LOGS_DIR:-not-set}"
echo "Backup Directory:     ${BACKUP_DIR:-not-set}"
echo "Backup Retention:     ${BACKUP_RETENTION:-not-set} days"
echo "Log Debug Path:       ${LOG_DEBUG_PATH:-not-set}"
echo "Log Error Path:       ${LOG_ERROR_PATH:-not-set}"
echo

# Footer with actions
echo
echo -e "${CYAN}╔══════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║                              Quick Actions                           ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════════════════════╝${NC}"
echo
echo "Switch environment:   ./tools/env/switch-env.sh <local|remote>"
echo "Test GitHub URL:      ./tools/github/test-url.sh"
echo "Validate compose:     ./tools/deploy/compose-lint.sh"
if [[ "$ENV_TYPE" == "local" ]]; then
    echo "Deploy locally:       ./tools/deploy/compose-local.sh up"
elif [[ "$ENV_TYPE" == "remote" ]]; then
    echo "Deploy to Portainer:  ./tools/portainer/deploy-stack.sh"
fi
echo "Check health:         ./tools/health/health-check.sh"
echo