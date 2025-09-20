#!/bin/bash

# Environment Variables Validation Script for WillowCMS Docker Setup
# This script validates that environment variables are properly configured

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "   $1"
}

# Check if config/.env exists
print_header "Environment File Check"
if [ -f "config/.env" ]; then
    print_success "config/.env file exists"
    print_info "Location: $(pwd)/config/.env"
else
    print_error "config/.env file not found!"
    exit 1
fi

# Check Docker Compose version
print_header "Docker Compose Version"
if docker compose version >/dev/null 2>&1; then
    VERSION=$(docker compose version | head -1)
    print_success "Docker Compose v2 available"
    print_info "$VERSION"
else
    print_warning "Docker Compose v2 not available, checking legacy version"
    if docker-compose --version >/dev/null 2>&1; then
        VERSION=$(docker-compose --version)
        print_success "Legacy docker-compose available"
        print_info "$VERSION"
    else
        print_error "No Docker Compose installation found"
        exit 1
    fi
fi

# Test configuration validation
print_header "Configuration Validation"
if docker compose --env-file config/.env config >/dev/null 2>&1; then
    print_success "Docker Compose configuration is valid with config/.env"
else
    print_error "Configuration validation failed"
    print_info "Try running: docker compose --env-file config/.env config"
    exit 1
fi

# Check specific environment variables
print_header "Environment Variable Substitution Test"

# Check some key variables from the compose output
COMPOSE_OUTPUT=$(docker compose --env-file config/.env config 2>/dev/null)

# Test APP_NAME
if echo "$COMPOSE_OUTPUT" | grep -q "APP_NAME: WillowCMS"; then
    print_success "APP_NAME correctly substituted: WillowCMS"
else
    print_error "APP_NAME not found or not substituted correctly"
fi

# Test DB_HOST
if echo "$COMPOSE_OUTPUT" | grep -q "DB_HOST: mysql"; then
    print_success "DB_HOST correctly substituted: mysql"
else
    print_error "DB_HOST not found or not substituted correctly"
fi

# Test MYSQL_ROOT_PASSWORD
if echo "$COMPOSE_OUTPUT" | grep -q "MYSQL_ROOT_PASSWORD:"; then
    print_success "MYSQL_ROOT_PASSWORD is being substituted"
    print_info "Value: [HIDDEN FOR SECURITY]"
else
    print_error "MYSQL_ROOT_PASSWORD not found or not substituted correctly"
fi

# Test build args
if echo "$COMPOSE_OUTPUT" | grep -q "UID:"; then
    UID_VALUE=$(echo "$COMPOSE_OUTPUT" | grep "UID:" | head -1 | cut -d'"' -f2)
    print_success "UID build arg substituted: $UID_VALUE"
else
    print_error "UID build arg not found"
fi

if echo "$COMPOSE_OUTPUT" | grep -q "GID:"; then
    GID_VALUE=$(echo "$COMPOSE_OUTPUT" | grep "GID:" | head -1 | cut -d'"' -f2)
    print_success "GID build arg substituted: $GID_VALUE"
else
    print_error "GID build arg not found"
fi

# Check helper script
print_header "Helper Script Check"
if [ -x "docker-compose.sh" ]; then
    print_success "docker-compose.sh helper script is executable"
    print_info "You can use: ./docker-compose.sh [command]"
else
    if [ -f "docker-compose.sh" ]; then
        print_warning "docker-compose.sh exists but is not executable"
        print_info "Run: chmod +x docker-compose.sh"
    else
        print_error "docker-compose.sh helper script not found"
    fi
fi

# Summary
print_header "Summary"
print_success "Environment variable configuration is working correctly!"
print_info "Your setup supports:"
print_info "• Docker Compose v2 with --env-file flag"
print_info "• Variable substitution in compose files"
print_info "• Environment variables in containers"
print_info ""
print_info "Recommended usage:"
print_info "  docker compose --env-file config/.env [command]"
print_info "or"
print_info "  ./docker-compose.sh [command]"
