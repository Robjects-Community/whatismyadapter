#!/bin/bash
# GitHub Remote Selector for WillowCMS Deployments
# Usage: ./tools/github/select-remote.sh <origin|garzarobm|matthewdeaves>

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
WillowCMS GitHub Remote Selector

USAGE:
    $0 <remote>

REMOTES:
    origin         - https://github.com/Robjects-Community/WhatIsMyAdaptor.git
    garzarobm      - https://github.com/garzarobm/willow.git
    matthewdeaves  - https://github.com/matthewdeaves/willow.git

DESCRIPTION:
    Updates the active environment configuration to use the specified
    GitHub remote repository. This allows easy switching between different
    forks and versions of the WillowCMS project.

EXAMPLES:
    $0 origin       # Use the main Robjects-Community repository
    $0 garzarobm    # Use garzarobm's fork (often used for production)
    $0 matthewdeaves # Use matthewdeaves' fork (original maintainer)

NOTES:
    - Updates the active .env file with the new Git URL and reference
    - Automatically tests the new URL to verify accessibility
    - Use './tools/env/switch-env.sh <local|remote>' to switch environments first

EOF
}

# Remote configurations (using simple variables for macOS compatibility)
get_remote_url() {
    case "$1" in
        origin) echo "https://github.com/Robjects-Community/WhatIsMyAdaptor.git" ;;
        garzarobm) echo "https://github.com/garzarobm/willow.git" ;;
        matthewdeaves) echo "https://github.com/matthewdeaves/willow.git" ;;
        *) return 1 ;;
    esac
}

get_remote_ref() {
    case "$1" in
        origin) echo "main" ;;
        garzarobm) echo "main-clean" ;;
        matthewdeaves) echo "main" ;;
        *) return 1 ;;
    esac
}

get_remote_description() {
    case "$1" in
        origin) echo "Robjects-Community/WhatIsMyAdaptor - Main repository" ;;
        garzarobm) echo "garzarobm/willow - Production fork with main-clean branch" ;;
        matthewdeaves) echo "matthewdeaves/willow - Original maintainer's fork" ;;
        *) return 1 ;;
    esac
}

get_valid_remotes() {
    echo "origin garzarobm matthewdeaves"
}

# Validate arguments
if [[ $# -ne 1 ]]; then
    log_error "Invalid number of arguments"
    usage
    exit 1
fi

SELECTED_REMOTE="$1"

# Validate remote selection
if ! SELECTED_URL=$(get_remote_url "$SELECTED_REMOTE" 2>/dev/null); then
    log_error "Invalid remote: $SELECTED_REMOTE"
    log_error "Valid options are: $(get_valid_remotes)"
    usage
    exit 1
fi

# Check if environment is set up
if [[ ! -L "$ENV_LINK" ]]; then
    log_error "No active environment found"
    log_error "Run './tools/env/switch-env.sh <local|remote>' first to set up environment"
    exit 1
fi

# Determine current environment type
ENV_TYPE="unknown"
ENV_TARGET="$(readlink "$ENV_LINK")"
case "$ENV_TARGET" in
    *local.env)
        ENV_TYPE="local"
        ;;
    *remote.env)
        ENV_TYPE="remote"
        ;;
    *)
        log_warn "Cannot determine environment type from symlink: $ENV_TARGET"
        ;;
esac

# Get selected remote info (already got URL above)
SELECTED_REF=$(get_remote_ref "$SELECTED_REMOTE")
SELECTED_DESC=$(get_remote_description "$SELECTED_REMOTE")

# Display selection
echo
log_info "GitHub Remote Selection"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Selected Remote:      $SELECTED_REMOTE"
echo "Description:          $SELECTED_DESC"
echo "Git URL:              $SELECTED_URL"
echo "Git Reference:        $SELECTED_REF"
echo "Environment Type:     $ENV_TYPE"
echo "Environment File:     $ENV_TARGET"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Function to update environment file
update_env_file() {
    local env_file="$1"
    local temp_file="${env_file}.tmp"
    
    # Create a temporary file with updated values
    if [[ -f "$env_file" ]]; then
        # Update existing values
        sed -e "s|^GIT_REMOTE_NAME=.*|GIT_REMOTE_NAME=$SELECTED_REMOTE|" \
            -e "s|^GIT_URL=.*|GIT_URL=$SELECTED_URL|" \
            -e "s|^GIT_REF=.*|GIT_REF=$SELECTED_REF|" \
            "$env_file" > "$temp_file"
        
        # Replace original file
        mv "$temp_file" "$env_file"
        return 0
    else
        log_error "Environment file not found: $env_file"
        return 1
    fi
}

# Update the environment file
log_info "Updating environment configuration..."

# Get the actual file path (resolve symlink)
ACTUAL_ENV_FILE="$(readlink -f "$ENV_LINK")"

if update_env_file "$ACTUAL_ENV_FILE"; then
    log_success "Environment configuration updated"
    echo "  Updated: $ACTUAL_ENV_FILE"
else
    log_error "Failed to update environment configuration"
    exit 1
fi

# Validate the update by sourcing the updated environment
log_info "Validating configuration update..."

set +u
source "$ENV_LINK"
set -u

# Check if the update worked
if [[ "${GIT_REMOTE_NAME:-}" != "$SELECTED_REMOTE" ]]; then
    log_error "Configuration update verification failed"
    log_error "Expected GIT_REMOTE_NAME=$SELECTED_REMOTE, got: ${GIT_REMOTE_NAME:-not-set}"
    exit 1
fi

if [[ "${GIT_URL:-}" != "$SELECTED_URL" ]]; then
    log_error "Configuration update verification failed"
    log_error "Expected GIT_URL=$SELECTED_URL, got: ${GIT_URL:-not-set}"
    exit 1
fi

log_success "Configuration validation passed"

# Test the GitHub URL
log_info "Testing GitHub URL accessibility..."

if [[ -x "${SCRIPT_DIR}/test-url.sh" ]]; then
    echo
    log_info "Running GitHub URL test..."
    
    # Run the test script
    if "${SCRIPT_DIR}/test-url.sh"; then
        echo
        log_success "GitHub URL test passed! Remote is ready for deployment."
        
        # Display next steps based on environment
        echo
        log_info "Next steps:"
        if [[ "$ENV_TYPE" == "local" ]]; then
            echo "  1. Validate compose file:   ./tools/deploy/compose-lint.sh"
            echo "  2. Deploy locally:          ./tools/deploy/compose-local.sh up"
            echo "  3. Check deployment health: ./tools/health/health-check.sh"
        elif [[ "$ENV_TYPE" == "remote" ]]; then
            echo "  1. Deploy to Portainer:     ./tools/portainer/deploy-stack.sh"
            echo "  2. Check deployment health: ./tools/health/health-check.sh"
        fi
    else
        echo
        log_error "GitHub URL test failed. Check the output above for details."
        log_info "The remote selection was updated but the repository may not be accessible."
        echo
        log_info "You can:"
        echo "  1. Try a different remote:    ./tools/github/select-remote.sh <remote>"
        echo "  2. Manually test the URL:     git ls-remote $SELECTED_URL"
        echo "  3. Check network connectivity"
        exit 1
    fi
else
    log_warn "GitHub URL test script not found or not executable"
    log_warn "Manually verify the URL: git ls-remote $SELECTED_URL"
fi

echo
log_info "Current environment summary:"
"${PROJECT_ROOT}/tools/env/print-env.sh" | head -20

echo
log_success "Remote selection completed successfully!"
log_info "Active remote: $SELECTED_REMOTE ($SELECTED_DESC)"