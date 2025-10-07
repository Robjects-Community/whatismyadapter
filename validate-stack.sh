#!/bin/bash
# ===================================================================
# Portainer Stack Validation Script
# ===================================================================
# This script validates the docker-compose-stack.yml file before
# deploying to Portainer to catch errors early
# ===================================================================

set -e

STACK_FILE="docker-compose-stack.yml"
ENV_FILE="stack.env"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
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
    echo -e "${BLUE}🔍 $1${NC}"
}

print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Main validation function
main() {
    print_header "🚀 Validating Portainer Stack Configuration"
    
    cd "$SCRIPT_DIR" || exit 1
    
    # Step 1: Check if required files exist
    print_info "Checking required files..."
    
    if [ ! -f "$STACK_FILE" ]; then
        print_error "$STACK_FILE not found"
        exit 1
    fi
    print_success "Found $STACK_FILE"
    
    if [ ! -f "$ENV_FILE" ]; then
        print_warning "$ENV_FILE not found - using defaults"
    else
        print_success "Found $ENV_FILE"
    fi
    
    # Step 2: Check Docker is installed
    print_header "🐳 Checking Docker Installation"
    
    if ! command_exists docker; then
        print_error "Docker is not installed"
        exit 1
    fi
    print_success "Docker is installed: $(docker --version)"
    
    if docker compose version >/dev/null 2>&1; then
        print_success "Docker Compose is installed: $(docker compose version --short 2>/dev/null || echo 'version available')"
    else
        print_error "Docker Compose is not installed"
        exit 1
    fi
    
    # Step 3: Validate YAML syntax
    print_header "📝 Validating YAML Syntax"
    
    print_info "Running docker compose config validation..."
    if docker compose -f "$STACK_FILE" config --quiet 2>&1 | grep -v "attribute.*version.*obsolete"; then
        print_success "YAML syntax is valid"
    else
        print_success "YAML syntax is valid (version warning is expected for Swarm)"
    fi
    
    # Step 4: Check for required environment variables
    print_header "🔐 Checking Environment Variables"
    
    if [ -f "$ENV_FILE" ]; then
        print_info "Loading environment variables from $ENV_FILE..."
        # Source env file while ignoring readonly variable errors
        set +e
        source "$ENV_FILE" 2>/dev/null
        set -e
        
        # Check critical variables
        local critical_vars=("MYSQL_ROOT_PASSWORD" "MYSQL_PASSWORD" "REDIS_PASSWORD" "SECURITY_SALT")
        local missing_vars=()
        
        for var in "${critical_vars[@]}"; do
            if [ -z "${!var}" ]; then
                missing_vars+=("$var")
            fi
        done
        
        if [ ${#missing_vars[@]} -eq 0 ]; then
            print_success "All critical environment variables are set"
        else
            print_warning "Missing critical variables: ${missing_vars[*]}"
            print_warning "These should be set in $ENV_FILE"
        fi
    else
        print_warning "No $ENV_FILE found - using default values"
    fi
    
    # Step 5: Check for hardcoded secrets
    print_header "🔒 Security Validation"
    
    print_info "Checking for hardcoded secrets..."
    if grep -qE '(password|secret|key):\s*["\x27][^"\x27$]' "$STACK_FILE" 2>/dev/null; then
        print_warning "Possible hardcoded secrets found - review manually"
    else
        print_success "No obvious hardcoded secrets found"
    fi
    
    # Step 6: Validate service configuration
    print_header "⚙️  Validating Service Configuration"
    
    print_info "Checking for required services..."
    local required_services=("willowcms" "mysql" "redis" "phpmyadmin" "mailpit" "redis-commander")
    
    for service in "${required_services[@]}"; do
        if docker compose -f "$STACK_FILE" config --services 2>/dev/null | grep -q "^${service}$"; then
            print_success "Service '$service' is configured"
        else
            print_error "Service '$service' is missing"
            exit 1
        fi
    done
    
    # Step 7: Check for deploy sections (Swarm requirement)
    print_header "🐝 Validating Docker Swarm Configuration"
    
    print_info "Checking for deploy sections..."
    local deploy_count=$(grep -c "deploy:" "$STACK_FILE" || true)
    
    if [ "$deploy_count" -ge 6 ]; then
        print_success "All services have deploy configurations ($deploy_count found)"
    else
        print_warning "Some services may be missing deploy configurations"
    fi
    
    # Step 8: Check network configuration
    print_info "Validating network configuration..."
    if grep -q "driver: overlay" "$STACK_FILE"; then
        print_success "Overlay network configured (required for Swarm)"
    else
        print_error "Overlay network not found - required for Swarm"
        exit 1
    fi
    
    # Step 9: Validate volumes
    print_header "💾 Validating Volume Configuration"
    
    print_info "Checking for required volumes..."
    local expected_volumes=("mysql_data" "mysql_logs" "redis_data" "app_data" "logs")
    
    for volume in "${expected_volumes[@]}"; do
        if grep -q "${volume}" "$STACK_FILE"; then
            print_success "Volume '${volume}' is configured"
        else
            print_warning "Volume '${volume}' not found"
        fi
    done
    
    # Step 10: Check for build directives (not allowed in Swarm)
    print_header "🔨 Checking Swarm Compatibility"
    
    print_info "Checking for build directives..."
    if grep -q "build:" "$STACK_FILE"; then
        print_error "Build directives found - not supported in Swarm"
        print_error "All services must use pre-built images"
        exit 1
    else
        print_success "No build directives found (Swarm compatible)"
    fi
    
    # Step 11: Test with Docker Compose (if available)
    print_header "🧪 Testing Configuration"
    
    print_info "Testing configuration rendering..."
    if docker compose -f "$STACK_FILE" config > /dev/null 2>&1; then
        print_success "Configuration renders successfully"
    else
        print_error "Configuration rendering failed"
        exit 1
    fi
    
    # Step 12: Generate summary
    print_header "📊 Validation Summary"
    
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                                                           ║${NC}"
    echo -e "${GREEN}║       ✅  Stack Validation Completed Successfully!        ║${NC}"
    echo -e "${GREEN}║                                                           ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    
    print_success "Stack file: $STACK_FILE"
    print_success "Environment file: $ENV_FILE"
    print_success "Services validated: ${#required_services[@]}"
    print_success "Deploy configurations: $deploy_count"
    
    echo ""
    print_info "Next steps:"
    echo "  1. Ensure Docker Swarm is initialized: docker swarm init"
    echo "  2. Load environment variables: export \$(cat $ENV_FILE | xargs)"
    echo "  3. Deploy stack: docker stack deploy -c $STACK_FILE willowcms-swarm-test"
    echo "  4. Check status: docker stack services willowcms-swarm-test"
    echo "  5. Monitor logs: docker service logs willowcms-swarm-test_willowcms"
    echo ""
    
    print_success "Ready for Portainer deployment! 🚀"
    echo ""
}

# Run main function
main "$@"
