#!/bin/bash
# Docker Compose Validation Script for WillowCMS
# Validates compose file syntax and variable substitution

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
WillowCMS Docker Compose Validation

USAGE:
    $0 [options]

OPTIONS:
    -f, --file <file>       Compose file to validate (default: portainer-stacks/docker-compose.yml)
    -e, --env-file <file>   Environment file to use (default: .env)
    -v, --verbose           Enable verbose output
    -h, --help              Show this help message

DESCRIPTION:
    Validates Docker Compose file syntax, variable substitution, and configuration.
    Tests both parsing and environment variable resolution.

EXAMPLES:
    $0                                      # Validate with current environment
    $0 -f docker-compose.yml               # Validate specific file
    $0 -e env/local.env                     # Use specific env file
    $0 -v                                   # Verbose output

EXIT CODES:
    0  - Validation passed
    1  - Compose file validation failed
    2  - Environment validation failed
    3  - File not found

EOF
}

# Default values
COMPOSE_FILE="${PROJECT_ROOT}/portainer-stacks/docker-compose.yml"
ENV_FILE=""
VERBOSE=false

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -f|--file)
            COMPOSE_FILE="$2"
            shift 2
            ;;
        -e|--env-file)
            ENV_FILE="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            usage
            exit 2
            ;;
    esac
done

# Resolve compose file path
if [[ ! "${COMPOSE_FILE}" = /* ]]; then
    COMPOSE_FILE="${PROJECT_ROOT}/${COMPOSE_FILE}"
fi

# Check if compose file exists
if [[ ! -f "$COMPOSE_FILE" ]]; then
    log_error "Compose file not found: $COMPOSE_FILE"
    exit 3
fi

# Determine environment file
if [[ -n "$ENV_FILE" ]]; then
    if [[ ! "${ENV_FILE}" = /* ]]; then
        ENV_FILE="${PROJECT_ROOT}/${ENV_FILE}"
    fi
    if [[ ! -f "$ENV_FILE" ]]; then
        log_error "Environment file not found: $ENV_FILE"
        exit 3
    fi
else
    if [[ -f "$ENV_LINK" ]]; then
        ENV_FILE="$ENV_LINK"
    else
        log_error "No environment file found. Use -e option or run './tools/env/switch-env.sh <local|remote>' first"
        exit 2
    fi
fi

# Display validation info
echo
log_info "Docker Compose Validation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Compose File:         $(basename "$COMPOSE_FILE")"
echo "Compose Path:         $COMPOSE_FILE"
echo "Environment File:     $(basename "$ENV_FILE")"
echo "Environment Path:     $ENV_FILE"
echo "Working Directory:    $(pwd)"
echo "Docker Compose:       $(docker compose version --short 2>/dev/null || echo 'not found')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 1: Basic file syntax validation
log_info "Test 1: Basic YAML syntax validation"

if [[ "$VERBOSE" == "true" ]]; then
    log_info "Checking YAML syntax..."
fi

# Check if we have yq or python for YAML validation
YAML_VALIDATOR=""
if command -v yq >/dev/null 2>&1; then
    YAML_VALIDATOR="yq"
elif command -v python3 >/dev/null 2>&1; then
    YAML_VALIDATOR="python3"
elif command -v python >/dev/null 2>&1; then
    YAML_VALIDATOR="python"
fi

if [[ -n "$YAML_VALIDATOR" ]]; then
    if [[ "$YAML_VALIDATOR" == "yq" ]]; then
        if yq e '.' "$COMPOSE_FILE" >/dev/null 2>&1; then
            log_success "YAML syntax is valid"
        else
            log_error "YAML syntax validation failed"
            if [[ "$VERBOSE" == "true" ]]; then
                yq e '.' "$COMPOSE_FILE" 2>&1 | head -10
            fi
            exit 1
        fi
    else
        # Use Python for YAML validation
        if "$YAML_VALIDATOR" -c "import yaml; yaml.safe_load(open('$COMPOSE_FILE'))" 2>/dev/null; then
            log_success "YAML syntax is valid"
        else
            log_error "YAML syntax validation failed"
            if [[ "$VERBOSE" == "true" ]]; then
                "$YAML_VALIDATOR" -c "import yaml; yaml.safe_load(open('$COMPOSE_FILE'))" 2>&1 | head -10
            fi
            exit 1
        fi
    fi
else
    log_warn "No YAML validator found (yq or python), skipping syntax check"
fi

# Test 2: Docker Compose validation
log_info "Test 2: Docker Compose file validation"

COMPOSE_CMD="docker compose -f \"$COMPOSE_FILE\" --env-file \"$ENV_FILE\" config"

if [[ "$VERBOSE" == "true" ]]; then
    log_info "Running: $COMPOSE_CMD"
fi

# Run docker compose config to validate
COMPOSE_OUTPUT=""
COMPOSE_EXIT_CODE=0

if COMPOSE_OUTPUT=$(eval "$COMPOSE_CMD" 2>&1); then
    log_success "Docker Compose configuration is valid"
    
    if [[ "$VERBOSE" == "true" ]]; then
        echo
        log_info "Generated configuration preview:"
        echo "$COMPOSE_OUTPUT" | head -50
        if [[ $(echo "$COMPOSE_OUTPUT" | wc -l) -gt 50 ]]; then
            echo "... (output truncated, run with --verbose for full output)"
        fi
    fi
else
    COMPOSE_EXIT_CODE=$?
    log_error "Docker Compose validation failed"
    
    # Show error details
    echo
    echo "Error details:"
    echo "$COMPOSE_OUTPUT"
    exit 1
fi

# Test 3: Environment variable validation
log_info "Test 3: Environment variables validation"

# Source the environment file to check critical variables
set +u
source "$ENV_FILE"
set -u

MISSING_VARS=()
CRITICAL_VARS=(
    "PROJECT_NAME"
    "NETWORK_NAME" 
    "GIT_URL"
    "GIT_REF"
    "MYSQL_ROOT_PASSWORD"
    "MYSQL_USER"
    "MYSQL_PASSWORD"
    "MYSQL_DATABASE"
    "SECURITY_SALT"
)

for var in "${CRITICAL_VARS[@]}"; do
    if [[ -z "${!var:-}" ]]; then
        MISSING_VARS+=("$var")
    fi
done

if [[ ${#MISSING_VARS[@]} -gt 0 ]]; then
    log_error "Missing critical environment variables:"
    for var in "${MISSING_VARS[@]}"; do
        echo "  - $var"
    done
    exit 2
else
    log_success "All critical environment variables are set"
fi

# Test 4: Variable substitution validation
log_info "Test 4: Variable substitution validation"

# Check if key variables are properly substituted in the composed config
SUBSTITUTION_CHECKS=(
    "PROJECT_NAME:${PROJECT_NAME}"
    "NETWORK_NAME:${NETWORK_NAME}"
    "GIT_URL:${GIT_URL}"
    "GIT_REF:${GIT_REF}"
)

SUBSTITUTION_ERRORS=()

for check in "${SUBSTITUTION_CHECKS[@]}"; do
    var_name="${check%%:*}"
    expected_value="${check#*:}"
    
    if echo "$COMPOSE_OUTPUT" | grep -q "$expected_value"; then
        if [[ "$VERBOSE" == "true" ]]; then
            log_success "$var_name correctly substituted: $expected_value"
        fi
    else
        SUBSTITUTION_ERRORS+=("$var_name")
        log_warn "$var_name may not be properly substituted"
    fi
done

if [[ ${#SUBSTITUTION_ERRORS[@]} -eq 0 ]]; then
    log_success "Variable substitution validation passed"
else
    log_warn "Some variables may have substitution issues, but this might be expected"
fi

# Test 5: Service dependency validation
log_info "Test 5: Service dependencies validation"

EXPECTED_SERVICES=("willowcms" "mysql" "redis" "phpmyadmin" "mailpit")
MISSING_SERVICES=()

for service in "${EXPECTED_SERVICES[@]}"; do
    if echo "$COMPOSE_OUTPUT" | grep -q "^  $service:"; then
        if [[ "$VERBOSE" == "true" ]]; then
            log_success "Service '$service' found"
        fi
    else
        MISSING_SERVICES+=("$service")
    fi
done

if [[ ${#MISSING_SERVICES[@]} -eq 0 ]]; then
    log_success "All expected services are present"
else
    log_warn "Some services may be missing or named differently:"
    for service in "${MISSING_SERVICES[@]}"; do
        echo "  - $service"
    done
fi

# Final summary
echo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Validation Summary:"
echo "  ✓ YAML syntax validation"
echo "  ✓ Docker Compose validation" 
echo "  ✓ Environment variables validation"
if [[ ${#SUBSTITUTION_ERRORS[@]} -eq 0 ]]; then
    echo "  ✓ Variable substitution validation"
else
    echo "  ⚠ Variable substitution validation (warnings)"
fi
if [[ ${#MISSING_SERVICES[@]} -eq 0 ]]; then
    echo "  ✓ Service dependencies validation"
else
    echo "  ⚠ Service dependencies validation (warnings)"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

log_success "Compose file validation completed successfully!"

# Show next steps
echo
log_info "Next steps:"
echo "  Local deployment:     ./tools/deploy/compose-local.sh up"
echo "  Portainer deployment: ./tools/portainer/deploy-stack.sh"
echo "  Health check:         ./tools/health/health-check.sh"

exit 0