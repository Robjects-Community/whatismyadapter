#!/usr/bin/env bash
# WillowCMS VSCode Development Deployment Script
# Path 4: VSCode development, testing, and secure deployment pipeline
# For creating new features with secure debug/development/testing → staging → production phases

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
ENV_FILE="${PROJECT_ROOT}/stack.env"
SECURE_ENV_FILE="${PROJECT_ROOT}/.env.secure"  # For local secrets only

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_dev() {
    echo -e "${PURPLE}[DEV]${NC} $1"
}

# Check development environment
check_dev_environment() {
    log "Checking VSCode development environment..."
    
    # Check if we're in the right directory
    if [ ! -f "${PROJECT_ROOT}/docker-compose.yml" ]; then
        log_error "Not in WillowCMS project directory"
        return 1
    fi
    
    # Check for main-clean branch hardened dockerfile
    if [ ! -f "${PROJECT_ROOT}/infrastructure/docker/willowcms/Dockerfile" ]; then
        log_warning "Hardened Dockerfile not found - you may need to checkout main-clean branch"
    fi
    
    log_success "Development environment check passed"
}

# Security check - ensure secrets are not in public files
security_check() {
    log "Performing security check..."
    
    # Check for hardcoded secrets in yml files
    local unsafe_files=()
    
    for file in *.yml docker-compose*.yml; do
        [ -f "$file" ] || continue
        
        if grep -E "(password|secret|key).*:" "$file" | grep -v "\${" >/dev/null 2>&1; then
            unsafe_files+=("$file")
        fi
    done
    
    if [ ${#unsafe_files[@]} -gt 0 ]; then
        log_error "Found potential hardcoded secrets in: ${unsafe_files[*]}"
        log_error "All secrets should use environment variables!"
        return 1
    fi
    
    log_success "Security check passed - no hardcoded secrets found"
}

# Create secure environment file
create_secure_env() {
    if [ ! -f "$SECURE_ENV_FILE" ]; then
        log "Creating secure environment file for local development..."
        
        cat > "$SECURE_ENV_FILE" << 'EOF'
# WillowCMS Secure Development Environment
# This file contains secrets for LOCAL DEVELOPMENT ONLY
# NEVER commit this file to version control!

# Development Database
MYSQL_ROOT_PASSWORD=dev-root-password-change-me
MYSQL_PASSWORD=dev-user-password-change-me

# Development Redis
REDIS_PASSWORD=dev-redis-password-change-me

# Development Security Salt
SECURITY_SALT=dev-salt-32-characters-change-me-now

# Development Admin
WILLOW_ADMIN_PASSWORD=dev-admin-password-change-me

# Development API Keys (leave empty for local dev)
OPENAI_API_KEY=
YOUTUBE_API_KEY=
TRANSLATE_API_KEY=

# Development Email
EMAIL_REPLY=dev@willowcms.local
EMAIL_NOREPLY=noreply-dev@willowcms.local

# Development Redis Commander
REDIS_COMMANDER_PASSWORD=dev-redis-commander-change-me
EOF

        log_success "Created $SECURE_ENV_FILE"
        log_warning "Remember to update passwords in $SECURE_ENV_FILE"
        
        # Add to gitignore if not already there
        if [ -f "${PROJECT_ROOT}/.gitignore" ]; then
            if ! grep -q "\.env\.secure" "${PROJECT_ROOT}/.gitignore"; then
                echo ".env.secure" >> "${PROJECT_ROOT}/.gitignore"
                log_success "Added .env.secure to .gitignore"
            fi
        fi
    else
        log "Secure environment file already exists"
    fi
}

# Development phase menu
show_dev_menu() {
    echo
    echo "=== WillowCMS VSCode Development Pipeline ==="
    echo
    echo "🔧 DEVELOPMENT PHASE:"
    echo "1. Start local development (with hardened image)"
    echo "2. Run tests (Controller/Model/View)"
    echo "3. Generate test data"
    echo "4. Access development shell"
    echo "5. View development logs"
    echo
    echo "🔍 TESTING PHASE:"
    echo "6. Run full test suite"
    echo "7. Performance testing"
    echo "8. Security scan"
    echo
    echo "🚀 DEPLOYMENT PREPARATION:"
    echo "9. Create deployment package (no secrets)"
    echo "10. Generate Portainer deployment script"
    echo "11. Validate production configuration"
    echo
    echo "📊 UTILITIES:"
    echo "12. Database backup/restore"
    echo "13. Clear all caches"
    echo "14. Show development status"
    echo
    echo "0. Exit"
    echo
}

# Start local development with hardened image
start_local_dev() {
    log_dev "Starting local development environment with hardened image..."
    
    cd "$PROJECT_ROOT"
    
    # Ensure we're using the main-clean configuration
    if [ -f "docker-compose.yml" ]; then
        # Merge secure environment variables
        log_dev "Merging environment configurations..."
        
        # Export secure variables for this session only
        if [ -f "$SECURE_ENV_FILE" ]; then
            set -a
            source "$SECURE_ENV_FILE"
            set +a
        fi
        
        # Start services with hardened configuration
        docker compose --env-file "$ENV_FILE" up -d --build
        
        log_success "Development environment started"
        
        # Show access information
        echo
        echo "🌐 Development URLs:"
        echo "  Application: http://localhost:${WILLOW_HTTP_PORT:-8080}"
        echo "  PHPMyAdmin:  http://localhost:${PMA_HTTP_PORT:-8082}"
        echo "  Mailpit:     http://localhost:${MAILPIT_HTTP_PORT:-8025}"
        echo "  Redis:       http://localhost:${REDIS_COMMANDER_HTTP_PORT:-8084}"
        
    else
        log_error "docker-compose.yml not found. Are you on the main-clean branch?"
        return 1
    fi
}

# Run targeted tests
run_tests() {
    cd "$PROJECT_ROOT"
    
    echo "Select test type:"
    echo "1. Controller tests"
    echo "2. Model tests" 
    echo "3. View tests"
    echo "4. All tests"
    
    read -p "Enter choice [1-4]: " test_choice
    
    case $test_choice in
        1) 
            log_dev "Running Controller tests..."
            docker compose --env-file "$ENV_FILE" exec willowcms php vendor/bin/phpunit --filter Controller
            ;;
        2) 
            log_dev "Running Model tests..."
            docker compose --env-file "$ENV_FILE" exec willowcms php vendor/bin/phpunit --filter Model
            ;;
        3) 
            log_dev "Running View tests..."
            docker compose --env-file "$ENV_FILE" exec willowcms php vendor/bin/phpunit --filter View
            ;;
        4) 
            log_dev "Running all tests..."
            docker compose --env-file "$ENV_FILE" exec willowcms php vendor/bin/phpunit
            ;;
        *) log_error "Invalid choice" ;;
    esac
}

# Create deployment package without secrets
create_deployment_package() {
    log_dev "Creating secure deployment package..."
    
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local package_dir="${PROJECT_ROOT}/tools/backup/deployment/${timestamp}"
    
    mkdir -p "$package_dir"
    
    # Copy docker-compose files (these should not contain secrets)
    cp "${PROJECT_ROOT}/docker-compose-port-cloud.yml" "$package_dir/"
    cp "${PROJECT_ROOT}/docker-compose-port-local-dev.yml" "$package_dir/"
    
    # Copy stack.env template (with placeholder values)
    sed 's/changeme-[^=]*/REPLACE_IN_PORTAINER/g' "$ENV_FILE" > "${package_dir}/stack.env.template"
    
    # Create deployment instructions
    cat > "${package_dir}/DEPLOYMENT_INSTRUCTIONS.md" << EOF
# WillowCMS Secure Deployment Package
Generated: $(date)

## Files in this package:
- \`docker-compose-port-cloud.yml\` - Production deployment
- \`docker-compose-port-local-dev.yml\` - Development/testing
- \`stack.env.template\` - Environment variables template

## Deployment Steps:

### 1. Portainer Setup
1. Go to Portainer → Stacks → Add Stack
2. Choose "Repository" method
3. Repository URL: \`https://github.com/garzarobm/willow.git\`
4. Reference: \`refs/heads/portainer-stack\`
5. Compose path: \`docker-compose-port-cloud.yml\`

### 2. Environment Variables
Replace ALL "REPLACE_IN_PORTAINER" values in Portainer UI with secure values:

**CRITICAL SECRETS TO SET:**
- SECURITY_SALT (32+ characters)
- MYSQL_ROOT_PASSWORD
- MYSQL_PASSWORD  
- REDIS_PASSWORD
- WILLOW_ADMIN_PASSWORD
- API keys (if needed)

### 3. Deploy
Click "Deploy the stack" with:
- ✅ Force redeployment
- ✅ Pull latest images

## Security Notes:
- This package contains NO secrets
- All sensitive values must be set in Portainer UI
- Uses hardened Docker images from main-clean branch
- Runs as non-root user (UID=1034, GID=100)
EOF

    log_success "Deployment package created at: $package_dir"
    log_warning "Remember: This package contains NO secrets - set them in Portainer UI!"
}

# Validate production configuration
validate_production_config() {
    log_dev "Validating production configuration..."
    
    cd "$PROJECT_ROOT"
    
    # Test docker-compose-port-cloud.yml
    if [ -f "docker-compose-port-cloud.yml" ]; then
        log "Validating cloud deployment configuration..."
        
        if docker compose -f docker-compose-port-cloud.yml config >/dev/null 2>&1; then
            log_success "Cloud deployment configuration is valid"
        else
            log_error "Cloud deployment configuration has errors"
            docker compose -f docker-compose-port-cloud.yml config
            return 1
        fi
    fi
    
    # Check for hardcoded secrets
    security_check
    
    log_success "Production configuration validation passed"
}

# Show development status
show_dev_status() {
    log_dev "Development environment status:"
    
    cd "$PROJECT_ROOT"
    
    echo
    echo "=== Docker Services ==="
    if docker compose --env-file "$ENV_FILE" ps 2>/dev/null; then
        echo "Services are running"
    else
        echo "No services running"
    fi
    
    echo
    echo "=== Git Status ==="
    git status --porcelain | head -10
    
    echo
    echo "=== Recent Commits ==="
    git log --oneline -5
    
    echo
    echo "=== Docker Images ==="
    docker images | grep willowcms
}

# Main execution
main() {
    log_dev "WillowCMS VSCode Development Pipeline"
    log_dev "Secure development → testing → staging → production"
    
    # Perform initial checks
    check_dev_environment
    create_secure_env
    
    # Main menu loop
    while true; do
        show_dev_menu
        read -p "Enter your choice [0-14]: " choice
        
        case $choice in
            1) start_local_dev ;;
            2) run_tests ;;
            3) 
                docker compose --env-file "$ENV_FILE" exec willowcms bin/cake bake seed
                ;;
            4) 
                docker compose --env-file "$ENV_FILE" exec willowcms bash
                ;;
            5) 
                docker compose --env-file "$ENV_FILE" logs -f willowcms
                ;;
            6) 
                docker compose --env-file "$ENV_FILE" exec willowcms php vendor/bin/phpunit
                ;;
            7) 
                log_dev "Performance testing not implemented yet"
                ;;
            8) 
                security_check
                ;;
            9) 
                create_deployment_package
                ;;
            10) 
                log_dev "Portainer deployment script generation not implemented yet"
                ;;
            11) 
                validate_production_config
                ;;
            12) 
                log_dev "Database backup/restore not implemented yet"
                ;;
            13) 
                docker compose --env-file "$ENV_FILE" exec willowcms bin/cake cache clear_all
                ;;
            14) 
                show_dev_status
                ;;
            0) 
                log_dev "Exiting development pipeline..."
                exit 0
                ;;
            *) log_error "Invalid choice. Please enter 0-14." ;;
        esac
        
        echo
        read -p "Press Enter to continue..."
    done
}

# Run main function
main "$@"