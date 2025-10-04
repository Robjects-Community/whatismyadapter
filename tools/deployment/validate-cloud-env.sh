#!/usr/bin/env bash
# =============================================================================
# validate-cloud-env.sh
# Environment Variable Validation Script for WhatIsMyAdapter Cloud Deployment
# =============================================================================
# This script validates the stack.env.cloud file for security and configuration
# issues before deploying to production via Portainer.
#
# Usage:
#   ./tools/deployment/validate-cloud-env.sh [path-to-env-file]
#
# Exit Codes:
#   0 - All validation checks passed
#   1 - Validation errors found
# =============================================================================

set -euo pipefail

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
ERRORS=0
WARNINGS=0

# Default env file path
ENV_FILE="${1:-stack.env.cloud}"

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}WhatIsMyAdapter Cloud Environment Validation${NC}"
echo -e "${BLUE}==============================================================================${NC}"
echo ""

# Check if file exists
if [[ ! -f "$ENV_FILE" ]]; then
    echo -e "${RED}ERROR: Environment file not found: $ENV_FILE${NC}"
    exit 1
fi

echo -e "${BLUE}Validating: ${ENV_FILE}${NC}"
echo ""

# Function to check if a variable exists and is not empty
check_variable() {
    local var_name="$1"
    local var_value
    var_value=$(grep "^${var_name}=" "$ENV_FILE" | cut -d'=' -f2- | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
    
    if [[ -z "$var_value" ]]; then
        echo -e "${RED}✗ MISSING: ${var_name} is not set${NC}"
        ((ERRORS++))
        return 1
    fi
    echo "$var_value"
}

# Function to check for weak/placeholder passwords
check_password() {
    local var_name="$1"
    local var_value
    var_value=$(check_variable "$var_name" 2>/dev/null || echo "")
    
    if [[ -z "$var_value" ]]; then
        return 1
    fi
    
    # List of weak/placeholder patterns
    local weak_patterns=(
        "changeme"
        "password"
        "admin"
        "123456"
        "YOUR_"
        "REPLACE_ME"
        "your-"
        "example"
    )
    
    for pattern in "${weak_patterns[@]}"; do
        if [[ "$var_value" == *"$pattern"* ]]; then
            echo -e "${RED}✗ WEAK PASSWORD: ${var_name} contains placeholder or weak value${NC}"
            ((ERRORS++))
            return 1
        fi
    done
    
    # Check minimum length (should be at least 12 characters for passwords)
    if [[ ${#var_value} -lt 12 ]]; then
        echo -e "${YELLOW}⚠ WARNING: ${var_name} is shorter than 12 characters${NC}"
        ((WARNINGS++))
        return 1
    fi
    
    echo -e "${GREEN}✓ ${var_name} appears secure${NC}"
    return 0
}

# Function to validate domain format
validate_domain() {
    local var_name="$1"
    local expected_domain="$2"
    local var_value
    var_value=$(check_variable "$var_name" 2>/dev/null || echo "")
    
    if [[ -z "$var_value" ]]; then
        return 1
    fi
    
    if [[ "$var_value" == *"$expected_domain"* ]]; then
        echo -e "${GREEN}✓ ${var_name} uses correct domain${NC}"
        return 0
    else
        echo -e "${RED}✗ WRONG DOMAIN: ${var_name} = ${var_value}${NC}"
        echo -e "${RED}  Expected domain: ${expected_domain}${NC}"
        ((ERRORS++))
        return 1
    fi
}

# Function to validate email format
validate_email() {
    local var_name="$1"
    local expected_domain="$2"
    local var_value
    var_value=$(check_variable "$var_name" 2>/dev/null || echo "")
    
    if [[ -z "$var_value" ]]; then
        return 1
    fi
    
    # Basic email format check
    if [[ ! "$var_value" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
        echo -e "${RED}✗ INVALID EMAIL: ${var_name} = ${var_value}${NC}"
        ((ERRORS++))
        return 1
    fi
    
    # Check if it uses the correct domain
    if [[ "$var_value" == *"@${expected_domain}" ]]; then
        echo -e "${GREEN}✓ ${var_name} is valid and uses correct domain${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠ WARNING: ${var_name} does not use domain ${expected_domain}${NC}"
        ((WARNINGS++))
        return 1
    fi
}

# Function to validate git repository URL
validate_git_url() {
    local var_name="$1"
    local expected_url="$2"
    local var_value
    var_value=$(check_variable "$var_name" 2>/dev/null || echo "")
    
    if [[ -z "$var_value" ]]; then
        return 1
    fi
    
    if [[ "$var_value" == "$expected_url" ]]; then
        echo -e "${GREEN}✓ ${var_name} points to correct repository${NC}"
        return 0
    else
        echo -e "${RED}✗ WRONG REPOSITORY: ${var_name} = ${var_value}${NC}"
        echo -e "${RED}  Expected: ${expected_url}${NC}"
        ((ERRORS++))
        return 1
    fi
}

# =============================================================================
# VALIDATION CHECKS
# =============================================================================

echo -e "${BLUE}--- Security Checks ---${NC}"

# Critical security variables
check_password "SECURITY_SALT"
check_password "MYSQL_ROOT_PASSWORD"
check_password "MYSQL_PASSWORD"
check_password "REDIS_PASSWORD"
check_password "WILLOW_ADMIN_PASSWORD"
check_password "REDIS_COMMANDER_PASSWORD"

# Check PMA_PASSWORD matches MYSQL_ROOT_PASSWORD
mysql_root_pw=$(grep "^MYSQL_ROOT_PASSWORD=" "$ENV_FILE" | cut -d'=' -f2- | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
pma_pw=$(grep "^PMA_PASSWORD=" "$ENV_FILE" | cut -d'=' -f2- | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')

if [[ "$mysql_root_pw" != "$pma_pw" ]]; then
    echo -e "${RED}✗ PASSWORD MISMATCH: PMA_PASSWORD must match MYSQL_ROOT_PASSWORD${NC}"
    ((ERRORS++))
else
    echo -e "${GREEN}✓ PMA_PASSWORD matches MYSQL_ROOT_PASSWORD${NC}"
fi

echo ""
echo -e "${BLUE}--- Domain & Email Validation ---${NC}"

EXPECTED_DOMAIN="whatismyadapter.robjects.me"

validate_domain "APP_FULL_BASE_URL" "$EXPECTED_DOMAIN"
validate_email "EMAIL_REPLY" "$EXPECTED_DOMAIN"
validate_email "EMAIL_NOREPLY" "$EXPECTED_DOMAIN"
validate_email "WILLOW_ADMIN_EMAIL" "$EXPECTED_DOMAIN"

echo ""
echo -e "${BLUE}--- Repository Configuration ---${NC}"

validate_git_url "GIT_URL" "https://github.com/Robjects-Community/WhatIsMyAdaptor.git"

# Check GIT_REF
git_ref=$(check_variable "GIT_REF" 2>/dev/null || echo "")
if [[ "$git_ref" == "main-clean" ]] || [[ "$git_ref" == "main" ]]; then
    echo -e "${GREEN}✓ GIT_REF is set to valid branch: ${git_ref}${NC}"
else
    echo -e "${YELLOW}⚠ WARNING: GIT_REF is set to: ${git_ref}${NC}"
    ((WARNINGS++))
fi

echo ""
echo -e "${BLUE}--- Database Configuration ---${NC}"

# Check database name
db_name=$(check_variable "MYSQL_DATABASE" 2>/dev/null || echo "")
if [[ "$db_name" == "whatismyadapter_db" ]]; then
    echo -e "${GREEN}✓ MYSQL_DATABASE uses correct naming: ${db_name}${NC}"
else
    echo -e "${YELLOW}⚠ WARNING: MYSQL_DATABASE = ${db_name}${NC}"
    echo -e "${YELLOW}  Recommended: whatismyadapter_db${NC}"
    ((WARNINGS++))
fi

# Check database user
db_user=$(check_variable "MYSQL_USER" 2>/dev/null || echo "")
if [[ -n "$db_user" ]]; then
    echo -e "${GREEN}✓ MYSQL_USER is set: ${db_user}${NC}"
fi

echo ""
echo -e "${BLUE}--- Docker Configuration ---${NC}"

# Check UID/GID
uid=$(check_variable "DOCKER_UID" 2>/dev/null || echo "")
gid=$(check_variable "DOCKER_GID" 2>/dev/null || echo "")

if [[ "$uid" == "1034" ]] && [[ "$gid" == "100" ]]; then
    echo -e "${GREEN}✓ Docker UID/GID correctly set (1034:100)${NC}"
else
    echo -e "${YELLOW}⚠ WARNING: DOCKER_UID=${uid}, DOCKER_GID=${gid}${NC}"
    echo -e "${YELLOW}  Expected: UID=1034, GID=100 for whatismyadapter user${NC}"
    ((WARNINGS++))
fi

echo ""
echo -e "${BLUE}--- Production Settings ---${NC}"

# Check production mode
app_env=$(check_variable "APP_ENV" 2>/dev/null || echo "")
debug=$(check_variable "DEBUG" 2>/dev/null || echo "")

if [[ "$app_env" == "production" ]]; then
    echo -e "${GREEN}✓ APP_ENV is set to production${NC}"
else
    echo -e "${RED}✗ APP_ENV is not set to production: ${app_env}${NC}"
    ((ERRORS++))
fi

if [[ "$debug" == "false" ]]; then
    echo -e "${GREEN}✓ DEBUG is disabled${NC}"
else
    echo -e "${RED}✗ DEBUG should be false in production: ${debug}${NC}"
    ((ERRORS++))
fi

echo ""
echo -e "${BLUE}--- Required Variables Check ---${NC}"

# List of required variables
required_vars=(
    "APP_NAME"
    "PROJECT_NAME"
    "NETWORK_NAME"
    "WILLOW_HTTP_PORT"
    "PMA_HTTP_PORT"
    "REDIS_COMMANDER_HTTP_PORT"
    "MAILPIT_HTTP_PORT"
    "WILLOWCMS_IMAGE"
)

for var in "${required_vars[@]}"; do
    if check_variable "$var" >/dev/null 2>&1; then
        echo -e "${GREEN}✓ ${var} is set${NC}"
    fi
done

# =============================================================================
# SUMMARY
# =============================================================================

echo ""
echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}VALIDATION SUMMARY${NC}"
echo -e "${BLUE}==============================================================================${NC}"

if [[ $ERRORS -eq 0 ]] && [[ $WARNINGS -eq 0 ]]; then
    echo -e "${GREEN}✓ All checks passed! Environment file is ready for deployment.${NC}"
    exit 0
elif [[ $ERRORS -eq 0 ]]; then
    echo -e "${YELLOW}⚠ ${WARNINGS} warning(s) found. Review before deploying.${NC}"
    exit 0
else
    echo -e "${RED}✗ ${ERRORS} error(s) and ${WARNINGS} warning(s) found.${NC}"
    echo -e "${RED}Please fix all errors before deploying to production.${NC}"
    exit 1
fi
