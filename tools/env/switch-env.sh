#!/bin/bash
# Environment Switcher for WillowCMS Deployments
# Usage: ./tools/env/switch-env.sh <local|remote>

set -euo pipefail

# Script directory and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
ENV_DIR="${PROJECT_ROOT}/env"
ENV_LINK="${PROJECT_ROOT}/.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
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

# Usage information
usage() {
    cat << EOF
WillowCMS Environment Switcher

USAGE:
    $0 <environment>

ENVIRONMENTS:
    local   - Use env/local.env for local Docker deployment
    remote  - Use env/remote.env for remote Portainer deployment

DESCRIPTION:
    Creates/updates a symlink at project root (.env) pointing to the
    specified environment configuration file. Scripts will automatically
    use the active environment settings.

EXAMPLES:
    $0 local   # Switch to local development environment
    $0 remote  # Switch to remote production environment

FILES:
    .env            - Symlink to active environment (created/updated)
    env/local.env   - Local Docker deployment configuration
    env/remote.env  - Remote Portainer deployment configuration
    env/stack.env.example - Template for all environment variables

EOF
}

# Validate arguments
if [[ $# -ne 1 ]]; then
    log_error "Invalid number of arguments"
    usage
    exit 1
fi

ENV_TYPE="$1"

# Validate environment type
case "$ENV_TYPE" in
    local|remote)
        ;;
    *)
        log_error "Invalid environment type: $ENV_TYPE"
        log_error "Valid options are: local, remote"
        usage
        exit 1
        ;;
esac

# Check if environment file exists
ENV_FILE="${ENV_DIR}/${ENV_TYPE}.env"
if [[ ! -f "$ENV_FILE" ]]; then
    log_error "Environment file not found: $ENV_FILE"
    log_error "Expected files:"
    log_error "  - ${ENV_DIR}/local.env"
    log_error "  - ${ENV_DIR}/remote.env"
    exit 1
fi

# Create/update the symlink
log_info "Switching to $ENV_TYPE environment..."

# Remove existing symlink or file
if [[ -L "$ENV_LINK" ]]; then
    rm "$ENV_LINK"
    log_info "Removed existing symlink: .env"
elif [[ -f "$ENV_LINK" ]]; then
    log_warn "Found regular file at .env, backing up to .env.backup"
    mv "$ENV_LINK" "${ENV_LINK}.backup"
fi

# Create new symlink (relative path for portability)
cd "$PROJECT_ROOT"
ln -s "env/${ENV_TYPE}.env" .env
log_success "Created symlink: .env -> env/${ENV_TYPE}.env"

# Source the environment file to get values
set +u  # Allow unset variables temporarily
source "$ENV_FILE"
set -u

# Display environment summary (with masked secrets)
echo
log_info "Active Environment Summary:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Environment Type:     $ENV_TYPE"
echo "Project Name:         ${PROJECT_NAME:-not-set}"
echo "App Environment:      ${APP_ENV:-not-set}"
echo "Debug Mode:           ${DEBUG:-not-set}"
echo "Network Name:         ${NETWORK_NAME:-not-set}"
echo "Git Remote:           ${GIT_REMOTE_NAME:-not-set}"
echo "Git URL:              ${GIT_URL:-not-set}"
echo "Git Reference:        ${GIT_REF:-not-set}"
echo "Willow HTTP Port:     ${WILLOW_HTTP_PORT:-not-set}"
echo "Volume Mode:          ${VOLUME_MODE:-not-set}"

# Show different settings based on environment
if [[ "$ENV_TYPE" == "local" ]]; then
    echo "Local Code Dir:       ${WILLOW_CODE_DIR:-not-set}"
    echo "MySQL Port:           ${MYSQL_PORT:-not-set}"
    echo "PhpMyAdmin Port:      ${PMA_HTTP_PORT:-not-set}"
elif [[ "$ENV_TYPE" == "remote" ]]; then
    echo "Portainer URL:        ${PORTAINER_URL:-not-set}"
    echo "Portainer Endpoint:   ${PORTAINER_ENDPOINT_ID:-not-set}"
    echo "Stack Name:           ${STACK_NAME:-not-set}"
    echo "Stack File Path:      ${STACK_FILE_PATH:-not-set}"
    echo "Auto Pull:            ${AUTO_PULL:-not-set}"
fi

# Security note
echo
echo "Health Check URLs:    ${HEALTHCHECK_URLS:-not-set}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo

# Export environment variable
export CURRENT_ENV="$ENV_TYPE"

# Provide next steps
log_success "Environment switched successfully!"
echo
log_info "Next steps:"
case "$ENV_TYPE" in
    local)
        echo "  1. Test GitHub URL:     ./tools/github/test-url.sh"
        echo "  2. Validate compose:    ./tools/deploy/compose-lint.sh"
        echo "  3. Deploy locally:      ./tools/deploy/compose-local.sh up"
        echo "  4. Check health:        ./tools/health/health-check.sh"
        ;;
    remote)
        echo "  1. Test GitHub URL:     ./tools/github/test-url.sh"
        echo "  2. Deploy to Portainer: ./tools/portainer/deploy-stack.sh"
        echo "  3. Check health:        ./tools/health/health-check.sh"
        ;;
esac
echo
log_info "View current environment: ./tools/env/print-env.sh"
log_info "Switch back anytime:      ./tools/env/switch-env.sh <local|remote>"