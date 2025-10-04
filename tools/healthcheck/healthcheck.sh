#!/bin/bash

# WillowCMS Container Healthcheck
# Tests HTTP endpoint and PHP-FPM liveness
# Returns 0 for healthy, 1 for unhealthy

set -euo pipefail

# Configuration
readonly APP_PORT="${APP_PORT:-8080}"
readonly TIMEOUT="${HEALTHCHECK_TIMEOUT:-3}"
readonly LOG_PREFIX="[healthcheck]"

# Logging functions
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

# Check HTTP health endpoint
check_http_health() {
    local url="http://localhost:${APP_PORT}/health"
    log_debug "Checking HTTP health endpoint: $url"
    
    if curl -f -s --connect-timeout "$TIMEOUT" --max-time "$TIMEOUT" "$url" >/dev/null 2>&1; then
        log_debug "HTTP health check passed"
        return 0
    else
        log_error "HTTP health check failed: $url"
        return 1
    fi
}

# Check PHP-FPM ping endpoint
check_php_fpm_ping() {
    local url="http://localhost:${APP_PORT}/ping"
    log_debug "Checking PHP-FPM ping endpoint: $url"
    
    local response
    if response=$(curl -f -s --connect-timeout "$TIMEOUT" --max-time "$TIMEOUT" "$url" 2>/dev/null); then
        # Check if response contains "pong" (typical PHP-FPM ping response)
        if [[ "$response" =~ pong|200|OK ]]; then
            log_debug "PHP-FPM ping check passed"
            return 0
        else
            log_error "PHP-FPM ping check failed: unexpected response '$response'"
            return 1
        fi
    else
        log_error "PHP-FPM ping check failed: $url"
        return 1
    fi
}

# Check if critical processes are running
check_processes() {
    log_debug "Checking critical processes"
    
    # Check if nginx is running
    if ! pgrep -f "nginx.*master process" >/dev/null 2>&1; then
        log_error "Nginx master process not found"
        return 1
    fi
    
    # Check if PHP-FPM is running
    if ! pgrep -f "php-fpm.*master process" >/dev/null 2>&1; then
        log_error "PHP-FPM master process not found"
        return 1
    fi
    
    log_debug "Critical processes check passed"
    return 0
}

# Check if PHP-FPM socket exists and is accessible
check_php_fpm_socket() {
    local socket_path="/run/php/php-fpm.sock"
    log_debug "Checking PHP-FPM socket: $socket_path"
    
    if [[ -S "$socket_path" ]]; then
        log_debug "PHP-FPM socket exists"
        return 0
    else
        log_error "PHP-FPM socket not found or not accessible: $socket_path"
        return 1
    fi
}

# Comprehensive health check
run_health_check() {
    local checks_passed=0
    local total_checks=0
    
    # Primary check: HTTP health endpoint
    total_checks=$((total_checks + 1))
    if check_http_health; then
        checks_passed=$((checks_passed + 1))
    fi
    
    # Secondary check: PHP-FPM ping (only if HTTP health failed)
    if [[ $checks_passed -eq 0 ]]; then
        total_checks=$((total_checks + 1))
        if check_php_fpm_ping; then
            checks_passed=$((checks_passed + 1))
        fi
    fi
    
    # Process check
    total_checks=$((total_checks + 1))
    if check_processes; then
        checks_passed=$((checks_passed + 1))
    fi
    
    # Socket check (informational, doesn't affect overall health)
    check_php_fpm_socket || true
    
    log_debug "Health check results: $checks_passed/$total_checks checks passed"
    
    # Return healthy if primary check passed or if we have at least some critical checks passing
    if [[ $checks_passed -ge 1 ]]; then
        log_info "Container health check: HEALTHY"
        return 0
    else
        log_error "Container health check: UNHEALTHY ($checks_passed/$total_checks checks passed)"
        return 1
    fi
}

# Main execution
main() {
    log_debug "Starting WillowCMS container health check"
    log_debug "Configuration: APP_PORT=$APP_PORT, TIMEOUT=$TIMEOUT"
    
    if run_health_check; then
        exit 0
    else
        exit 1
    fi
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi