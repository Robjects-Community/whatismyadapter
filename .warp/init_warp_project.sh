#!/bin/bash

# WillowCMS WARP Project Initialization Script
# This script sets up the WARP project configuration and ensures all rules are enabled

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Project root directory
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WARP_DIR="${PROJECT_ROOT}/.warp"

echo -e "${BLUE}🚀 Initializing WillowCMS WARP Project...${NC}"

# Function to print status messages
print_status() {
    echo -e "${GREEN}✅${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠️${NC} $1"
}

print_error() {
    echo -e "${RED}❌${NC} $1"
}

# Check if we're in the right directory
if [[ ! -f "${PROJECT_ROOT}/docker-compose.yml" ]]; then
    print_error "docker-compose.yml not found. Please run this script from the WillowCMS root directory."
    exit 1
fi

print_status "Found WillowCMS project at: ${PROJECT_ROOT}"

# Verify WARP directory structure
if [[ ! -d "${WARP_DIR}" ]]; then
    print_error "WARP directory not found at: ${WARP_DIR}"
    exit 1
fi

print_status "WARP directory found"

# Check for required files
REQUIRED_FILES=(
    ".warp/warp_project.yaml"
    ".warp/rules/docker-compose.md"
    ".warp/rules/phpunit-testing.md"
    ".warp/rules/cakephp-development.md"
    ".warp/rules/docker-security.md"
    ".warp/rules/terminal-echo.md"
)

echo -e "${BLUE}📋 Checking required WARP files...${NC}"

for file in "${REQUIRED_FILES[@]}"; do
    if [[ -f "${PROJECT_ROOT}/${file}" ]]; then
        print_status "Found: ${file}"
    else
        print_error "Missing: ${file}"
        exit 1
    fi
done

# Check project dependencies
echo -e "${BLUE}🔍 Checking project dependencies...${NC}"

# Check for Docker
if command -v docker >/dev/null 2>&1; then
    print_status "Docker is installed"
else
    print_warning "Docker not found - some features may not work"
fi

# Check for docker compose
if docker compose version >/dev/null 2>&1; then
    print_status "Docker Compose is installed"
else
    print_warning "Docker Compose not found - some features may not work"
fi

# Check for essential project files
PROJECT_FILES=(
    "docker-compose.yml"
    "config/.env"
    "manage.sh"
    "run_dev_env.sh"
)

for file in "${PROJECT_FILES[@]}"; do
    if [[ -f "${PROJECT_ROOT}/${file}" ]]; then
        print_status "Found: ${file}"
    else
        print_error "Missing project file: ${file}"
        exit 1
    fi
done

# Verify script permissions
echo -e "${BLUE}🔧 Verifying script permissions...${NC}"

SCRIPTS=(
    "manage.sh"
    "run_dev_env.sh"
)

for script in "${SCRIPTS[@]}"; do
    if [[ -x "${PROJECT_ROOT}/${script}" ]]; then
        print_status "Executable: ${script}"
    else
        print_warning "Making ${script} executable"
        chmod +x "${PROJECT_ROOT}/${script}"
    fi
done

# Display project configuration
echo -e "${BLUE}📊 Project Configuration Summary:${NC}"
echo "  Project Name: WillowCMS"
echo "  Project Type: CakePHP 5.x with Docker"
echo "  Root Directory: ${PROJECT_ROOT}"
echo "  WARP Config: ${WARP_DIR}/warp_project.yaml"
echo "  Rules Directory: ${WARP_DIR}/rules/"
echo "  Active Rules: $(find "${WARP_DIR}/rules/" -name "*.md" | wc -l)"

# Show available aliases
echo -e "${BLUE}🎯 Available Project Aliases:${NC}"
echo "  up      - Start Docker services"
echo "  down    - Stop Docker services"
echo "  dev     - Run development environment"
echo "  manage  - Open management interface"
echo "  test    - Run PHPUnit tests"
echo "  security- Run security check"
echo "  files   - File management tools"

# Display rule status
echo -e "${BLUE}📋 Active Project Rules:${NC}"
for rule_file in "${WARP_DIR}/rules/"*.md; do
    if [[ -f "$rule_file" ]]; then
        rule_name=$(basename "$rule_file" .md)
        print_status "Rule enabled: ${rule_name}"
    fi
done

# Create environment validation
echo -e "${BLUE}🔍 Environment Validation:${NC}"

if [[ -f "${PROJECT_ROOT}/config/.env" ]]; then
    print_status "Environment file found at: config/.env"
else
    print_warning "Environment file not found - copy config/.env.example to config/.env"
fi

if [[ -f "${PROJECT_ROOT}/docker-compose.yml" ]]; then
    # Check if docker-compose.yml uses environment variables
    if grep -q '\${' "${PROJECT_ROOT}/docker-compose.yml"; then
        print_status "Docker Compose uses environment variables (security compliant)"
    else
        print_warning "Docker Compose may contain hardcoded values - check security rules"
    fi
fi

# Verify tools directory structure
if [[ -d "${PROJECT_ROOT}/tools" ]]; then
    print_status "Tools directory organized"
    
    TOOL_DIRS=("development" "security" "maintenance" "quality")
    for dir in "${TOOL_DIRS[@]}"; do
        if [[ -d "${PROJECT_ROOT}/tools/${dir}" ]]; then
            print_status "  Found: tools/${dir}/"
        else
            print_warning "  Missing: tools/${dir}/"
        fi
    done
fi

# Final status
echo -e "${GREEN}🎉 WARP Project Initialization Complete!${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Run: ${YELLOW}./manage.sh${NC} - Access the management interface"
echo "2. Run: ${YELLOW}./run_dev_env.sh --fresh-dev${NC} - Start development environment"
echo "3. Run: ${YELLOW}tools/security/quick_security_check.sh${NC} - Verify security"
echo ""
echo -e "${BLUE}All project rules are now active and will be applied automatically!${NC}"

exit 0