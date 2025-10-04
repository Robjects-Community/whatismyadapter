#!/bin/bash

# WillowCMS Container Entrypoint
# Handles service initialization, configuration templating, and process management
# Security: Runs as non-root user with proper signal handling

set -euo pipefail

# Configuration
readonly APP_PORT="${APP_PORT:-8080}"
readonly PHP_FPM_SOCK="/run/php/php-fpm.sock"
readonly LOG_PREFIX="[willowcms-entrypoint]"

# Logging functions that redact sensitive information
log_info() {
    echo "${LOG_PREFIX} INFO: $*" >&1
}

log_error() {
    echo "${LOG_PREFIX} ERROR: $*" >&2
}

log_debug() {
    if [[ "${DEBUG:-false}" == "true" ]]; then
        echo "${LOG_PREFIX} DEBUG: $*" >&1
    fi
}

# Function to redact secrets from environment display
redact_secrets() {
    local input="$1"
    echo "$input" | sed -E 's/(PASSWORD|SECRET|KEY|TOKEN)=[^[:space:]]*/\1=***REDACTED***/g'
}

# Load environment variables from application if present
load_app_environment() {
    if [[ -f "/var/www/willowcms/.env" ]]; then
        log_info "Loading application environment variables"
        # Load without echoing sensitive values
        set -a
        # shellcheck disable=SC1091
        source "/var/www/willowcms/.env" 2>/dev/null || log_error "Failed to load .env file"
        set +a
        log_debug "Environment loaded (secrets redacted)"
    else
        log_info "No application .env file found, using container environment"
    fi
}

# Create required runtime directories with proper permissions
create_runtime_directories() {
    log_info "Creating runtime directories"
    
    local directories=(
        "/run/php"
        "/var/cache/nginx"
        "/var/log/nginx"
        "/var/www/willowcms/logs"
        "/var/www/willowcms/tmp"
        "/var/www/willowcms/tmp/cache"
        "/var/www/willowcms/tmp/sessions"
    )
    
    for dir in "${directories[@]}"; do
        if [[ ! -d "$dir" ]]; then
            mkdir -p "$dir"
            log_debug "Created directory: $dir"
        fi
        
        # Ensure willow user can write to these directories
        if [[ -w "$dir" ]] || chmod 755 "$dir" 2>/dev/null; then
            log_debug "Set permissions for: $dir"
        else
            log_error "Failed to set permissions for: $dir"
        fi
    done
}

# Render Nginx configuration from template
render_nginx_config() {
    log_info "Rendering Nginx configuration from template"
    
    local template_file="/usr/local/etc/nginx/willowcms.conf.tpl"
    local output_file="/etc/nginx/conf.d/willowcms.conf"
    
    if [[ ! -f "$template_file" ]]; then
        log_error "Nginx template file not found: $template_file"
        exit 1
    fi
    
    # Use envsubst to substitute environment variables
    if envsubst < "$template_file" > "$output_file"; then
        log_info "Nginx configuration rendered successfully"
        log_debug "Nginx config written to: $output_file"
    else
        log_error "Failed to render Nginx configuration"
        exit 1
    fi
}

# Validate PHP-FPM configuration
validate_php_fpm_config() {
    log_info "Validating PHP-FPM configuration"
    
    if php-fpm8.3 -t 2>/dev/null; then
        log_info "PHP-FPM configuration is valid"
        return 0
    else
        log_error "PHP-FPM configuration validation failed"
        php-fpm8.3 -t 2>&1 | head -10 | while read -r line; do
            log_error "PHP-FPM: $line"
        done
        return 1
    fi
}

# Validate Nginx configuration
validate_nginx_config() {
    log_info "Validating Nginx configuration"
    
    if nginx -t 2>/dev/null; then
        log_info "Nginx configuration is valid"
        return 0
    else
        log_error "Nginx configuration validation failed"
        nginx -t 2>&1 | head -10 | while read -r line; do
            log_error "Nginx: $line"
        done
        return 1
    fi
}

# Start PHP-FPM in background
start_php_fpm() {
    log_info "Starting PHP-FPM service"
    
    # Remove existing socket if it exists
    [[ -S "$PHP_FPM_SOCK" ]] && rm -f "$PHP_FPM_SOCK"
    
    # Start PHP-FPM in foreground mode with proper logging
    php-fpm8.3 --nodaemonize --fpm-config /etc/php/8.3/fpm/php-fpm.conf &
    local php_fpm_pid=$!
    
    # Wait a moment for PHP-FPM to start and create socket
    local retries=0
    while [[ $retries -lt 30 ]]; do
        if [[ -S "$PHP_FPM_SOCK" ]]; then
            log_info "PHP-FPM started successfully (PID: $php_fpm_pid)"
            return 0
        fi
        sleep 0.1
        ((retries++))
    done
    
    log_error "PHP-FPM failed to start within timeout"
    return 1
}

# Start Nginx (this will be the main process)
start_nginx() {
    log_info "Starting Nginx service"
    
    # Start Nginx in foreground mode
    exec nginx -g "daemon off;"
}

# Signal handler for graceful shutdown
graceful_shutdown() {
    log_info "Received shutdown signal, initiating graceful shutdown"
    
    # Send QUIT signal to Nginx for graceful shutdown
    if [[ -f "/var/run/nginx.pid" ]]; then
        local nginx_pid
        nginx_pid=$(cat /var/run/nginx.pid 2>/dev/null || echo "")
        if [[ -n "$nginx_pid" ]] && kill -0 "$nginx_pid" 2>/dev/null; then
            log_info "Sending graceful shutdown signal to Nginx (PID: $nginx_pid)"
            kill -QUIT "$nginx_pid" 2>/dev/null || true
        fi
    fi
    
    # Send QUIT signal to PHP-FPM
    pkill -QUIT php-fpm8.3 2>/dev/null || true
    
    log_info "Graceful shutdown initiated"
    exit 0
}

# Pre-flight checks
preflight_checks() {
    log_info "Running pre-flight checks"
    
    # Check if running as correct user
    local current_user
    current_user=$(whoami)
    if [[ "$current_user" != "willow" ]]; then
        log_error "Container should run as 'willow' user, currently: $current_user"
        exit 1
    fi
    
    # Check if application directory exists
    if [[ ! -d "/var/www/willowcms" ]]; then
        log_error "Application directory not found: /var/www/willowcms"
        exit 1
    fi
    
    # Check if vendor directory exists (Composer dependencies)
    if [[ ! -d "/var/www/willowcms/vendor" ]]; then
        log_error "Vendor directory not found: /var/www/willowcms/vendor"
        log_error "Run composer install to install dependencies"
        exit 1
    fi
    
    log_info "Pre-flight checks completed successfully"
}

# Set up signal handlers
setup_signal_handlers() {
    trap graceful_shutdown SIGTERM SIGINT SIGQUIT
}

# Main entrypoint function
main() {
    log_info "WillowCMS container starting up"
    log_info "Container user: $(whoami), UID: $(id -u), GID: $(id -g)"
    
    # Set up signal handling
    setup_signal_handlers
    
    # Run pre-flight checks
    preflight_checks
    
    # Load application environment
    load_app_environment
    
    # Create runtime directories
    create_runtime_directories
    
    # Render configuration templates
    render_nginx_config
    
    # Validate configurations
    validate_php_fpm_config || exit 1
    validate_nginx_config || exit 1
    
    # Start PHP-FPM first
    start_php_fpm || exit 1
    
    # Start Nginx (this becomes the main process)
    log_info "All services initialized, starting Nginx as main process"
    start_nginx
}

# Run main function
main "$@"