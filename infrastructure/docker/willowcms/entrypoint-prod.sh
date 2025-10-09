#!/bin/bash
set -e

# CakePHP Production Entrypoint Script
# Handles initialization, permissions, migrations, and cache warming

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

# Function to wait for database
wait_for_database() {
    log "Waiting for database connection..."
    
    local max_tries=30
    local count=0
    
    while ! mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
        count=$((count + 1))
        if [ $count -ge $max_tries ]; then
            log_error "Database connection timeout after $max_tries attempts"
            exit 1
        fi
        log "Database not ready, waiting... (attempt $count/$max_tries)"
        sleep 2
    done
    
    log_success "Database connection established"
}

# Function to wait for Redis
wait_for_redis() {
    log "Waiting for Redis connection..."
    
    local max_tries=15
    local count=0
    
    while ! redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ping >/dev/null 2>&1; do
        count=$((count + 1))
        if [ $count -ge $max_tries ]; then
            log_error "Redis connection timeout after $max_tries attempts"
            exit 1
        fi
        log "Redis not ready, waiting... (attempt $count/$max_tries)"
        sleep 2
    done
    
    log_success "Redis connection established"
}

# Function to set proper permissions
set_permissions() {
    log "Setting proper file permissions..."
    
    # Ensure directories exist and have proper permissions
    mkdir -p /var/www/html/tmp/cache/models
    mkdir -p /var/www/html/tmp/cache/persistent
    mkdir -p /var/www/html/tmp/sessions
    mkdir -p /var/www/html/logs
    mkdir -p /var/www/html/webroot/files
    
    # Set ownership (only if running as root during setup)
    if [ "$(id -u)" = "0" ]; then
        chown -R ${UID}:${GID} /var/www/html/tmp /var/www/html/logs /var/www/html/webroot/files
    fi
    
    # Set permissions
    chmod -R 755 /var/www/html
    chmod -R 777 /var/www/html/tmp
    chmod -R 777 /var/www/html/logs
    chmod -R 755 /var/www/html/webroot
    
    log_success "File permissions set"
}

# Function to run Composer install if needed
ensure_dependencies() {
    log "Checking Composer dependencies..."
    
    if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then
        log "Installing Composer dependencies..."
        
        composer install \
            --no-dev \
            --optimize-autoloader \
            --no-interaction \
            --prefer-dist \
            --no-progress
        
        log_success "Composer dependencies installed"
    else
        log_success "Composer dependencies already installed"
    fi
}

# Function to run database migrations
run_migrations() {
    log "Running database migrations..."
    
    # Check if migrations table exists and run migrations
    if /var/www/html/bin/cake migrations status >/dev/null 2>&1; then
        /var/www/html/bin/cake migrations migrate --no-interaction
        log_success "Database migrations completed"
    else
        log_warning "No migrations found or migrations system not set up"
    fi
}

# Function to warm up caches
warm_caches() {
    log "Warming up application caches..."
    
    # Clear existing caches
    /var/www/html/bin/cake cache clear_all || log_warning "Cache clear failed"
    
    # Warm up OPcache by accessing some endpoints
    curl -s http://localhost:80/ >/dev/null 2>&1 || log_warning "Could not warm cache via HTTP request"
    
    log_success "Cache warming completed"
}

# Function to validate configuration
validate_config() {
    log "Validating application configuration..."
    
    # Check required environment variables
    local required_vars=("DB_HOST" "DB_USERNAME" "DB_PASSWORD" "DB_DATABASE" "SECURITY_SALT")
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            log_error "Required environment variable $var is not set"
            exit 1
        fi
    done
    
    # Test database connection
    if ! /var/www/html/bin/cake migrations status >/dev/null 2>&1; then
        log_error "Database connection test failed"
        exit 1
    fi
    
    log_success "Configuration validation passed"
}

# Main initialization function
initialize_application() {
    log "Starting CakePHP application initialization..."
    
    # Step 1: Set permissions
    set_permissions
    
    # Step 2: Wait for dependencies
    wait_for_database
    wait_for_redis
    
    # Step 3: Ensure composer dependencies
    ensure_dependencies
    
    # Step 4: Validate configuration
    validate_config
    
    # Step 5: Run migrations
    run_migrations
    
    # Step 6: Warm caches
    warm_caches
    
    log_success "Application initialization completed successfully"
}

# Handle different run modes
case "${1}" in
    "init-only")
        initialize_application
        exit 0
        ;;
    "supervisord"|"/usr/bin/supervisord")
        # Full initialization then start supervisord
        initialize_application
        log "Starting supervisord..."
        exec "$@"
        ;;
    "bash"|"sh"|"/bin/bash"|"/bin/sh")
        # Development mode - skip initialization
        log "Starting in development mode"
        exec "$@"
        ;;
    *)
        # Default: initialize then run the command
        initialize_application
        log "Starting application with command: $*"
        exec "$@"
        ;;
esac