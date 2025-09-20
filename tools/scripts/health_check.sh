#!/usr/bin/env bash

# Willow CMS Health Check Script
# Comprehensive health check of all services with status reporting
# Usage: ./scripts/health-check.sh [--format=json|table] [--service=name] [--timeout=seconds]

set -euo pipefail

# Ensure we have bash 4+ for associative arrays
if [[ ${BASH_VERSION%%.*} -lt 4 ]]; then
    echo "Error: This script requires Bash 4.0 or higher. You have Bash $BASH_VERSION."
    echo "On macOS, you can install a newer Bash with: brew install bash"
    exit 1
fi

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "${SCRIPT_DIR}")"
LOG_FILE="${PROJECT_DIR}/logs/health-check.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Default options
OUTPUT_FORMAT="table"
SPECIFIC_SERVICE=""
TIMEOUT=30
VERBOSE=false

# Service configurations
declare -A SERVICES
SERVICES["willowcms"]="8080:http://localhost:8080:/var/www/html"
SERVICES["mysql"]="3310:mysql://localhost:3310:/var/lib/mysql"
SERVICES["redis"]="6379:redis://localhost:6379:/data"
SERVICES["phpmyadmin"]="8082:http://localhost:8082:/tmp"
SERVICES["mailpit"]="8025:http://localhost:8025:/data"
SERVICES["redis-commander"]="8084:http://localhost:8084:/tmp"
SERVICES["jenkins"]="8081:http://localhost:8081:/var/jenkins_home"

# Status tracking
declare -A SERVICE_STATUS=()
declare -A SERVICE_RESPONSE_TIME=()
declare -A SERVICE_DETAILS=()
OVERALL_STATUS="healthy"

# Logging function
log() {
    local level="$1"
    shift
    local message="$*"
    
    # Create logs directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    
    # Log to file
    echo "[$TIMESTAMP] [$level] $message" >> "$LOG_FILE"
    
    # Output to console with colors if verbose
    if [[ "$VERBOSE" == true ]] || [[ "$level" == "ERROR" ]]; then
        case "$level" in
            "INFO")
                echo -e "${BLUE}[INFO]${NC} $message" >&2
                ;;
            "WARN")
                echo -e "${YELLOW}[WARN]${NC} $message" >&2
                ;;
            "ERROR")
                echo -e "${RED}[ERROR]${NC} $message" >&2
                ;;
            "SUCCESS")
                echo -e "${GREEN}[SUCCESS]${NC} $message" >&2
                ;;
            *)
                echo "$message" >&2
                ;;
        esac
    fi
}

# Show help
show_help() {
    cat << EOF
Willow CMS Health Check Script

Usage: $0 [OPTIONS]

OPTIONS:
    --format FORMAT     Output format: table, json, or simple (default: table)
    --service SERVICE   Check specific service only
    --timeout SECONDS   Connection timeout in seconds (default: 30)
    --verbose          Enable verbose logging
    --help             Show this help message

SUPPORTED SERVICES:
    willowcms          Main Willow CMS application
    mysql              Database server
    redis              Redis cache server
    phpmyadmin         Database management interface
    mailpit            Email testing server
    redis-commander    Redis management interface
    jenkins            CI/CD server (if enabled)

OUTPUT FORMATS:
    table              Human-readable table format (default)
    json               JSON format for programmatic use
    simple             Simple status output for scripts

EXAMPLES:
    $0                           # Check all services with table output
    $0 --format=json             # JSON output for automation
    $0 --service=willowcms       # Check only the main application
    $0 --timeout=10 --verbose    # Quick check with detailed output

EXIT CODES:
    0    All services healthy
    1    Some services unhealthy
    2    Critical services down
    3    Cannot connect to Docker
    4    Invalid arguments

DESCRIPTION:
    This script performs comprehensive health checks on all Willow CMS services,
    including connectivity tests, response time measurements, and basic
    functionality verification. Designed for both human operators and CI/CD
    integration.

EOF
}

# Parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --format=*)
                OUTPUT_FORMAT="${1#*=}"
                shift
                ;;
            --service=*)
                SPECIFIC_SERVICE="${1#*=}"
                shift
                ;;
            --timeout=*)
                TIMEOUT="${1#*=}"
                shift
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --help)
                show_help
                exit 0
                ;;
            *)
                echo "Error: Unknown option: $1" >&2
                echo "Use --help for usage information." >&2
                exit 4
                ;;
        esac
    done

    # Validate arguments
    if [[ ! "$OUTPUT_FORMAT" =~ ^(table|json|simple)$ ]]; then
        echo "Error: Invalid format '$OUTPUT_FORMAT'. Use table, json, or simple." >&2
        exit 4
    fi

    if [[ ! "$TIMEOUT" =~ ^[0-9]+$ ]] || [[ "$TIMEOUT" -lt 1 ]] || [[ "$TIMEOUT" -gt 300 ]]; then
        echo "Error: Invalid timeout '$TIMEOUT'. Must be 1-300 seconds." >&2
        exit 4
    fi

    if [[ -n "$SPECIFIC_SERVICE" ]] && [[ ! "${SERVICES[$SPECIFIC_SERVICE]+exists}" ]]; then
        echo "Error: Unknown service '$SPECIFIC_SERVICE'." >&2
        echo "Supported services: ${!SERVICES[*]}" >&2
        exit 4
    fi
}

# Check prerequisites
check_prerequisites() {
    log "INFO" "Checking prerequisites..."
    
    if [[ ! -f "$PROJECT_DIR/docker-compose.yml" ]]; then
        log "ERROR" "docker-compose.yml not found in $PROJECT_DIR"
        exit 3
    fi

    if ! command -v docker >/dev/null 2>&1; then
        log "ERROR" "Docker command not found"
        exit 3
    fi

    if ! docker info >/dev/null 2>&1; then
        log "ERROR" "Cannot connect to Docker daemon. Is Docker running?"
        exit 3
    fi

    # Check if compose is available
    if ! command -v docker-compose >/dev/null 2>&1 && ! docker compose version >/dev/null 2>&1; then
        log "ERROR" "Neither docker-compose nor docker compose command found"
        exit 3
    fi

    log "SUCCESS" "Prerequisites check passed"
}

# Get docker compose command
get_docker_compose_cmd() {
    if command -v docker-compose >/dev/null 2>&1; then
        echo "docker-compose"
    else
        echo "docker compose"
    fi
}

# Check if container is running
check_container_status() {
    local service_name="$1"
    local container_id
    
    container_id=$(docker-compose ps -q "$service_name" 2>/dev/null || echo "")
    
    if [[ -z "$container_id" ]]; then
        return 1
    fi
    
    local status
    status=$(docker inspect -f '{{.State.Status}}' "$container_id" 2>/dev/null || echo "unknown")
    
    if [[ "$status" == "running" ]]; then
        return 0
    else
        return 1
    fi
}

# Test port connectivity
test_port_connectivity() {
    local host="localhost"
    local port="$1"
    local timeout="$2"
    
    if command -v nc >/dev/null 2>&1; then
        # Use netcat if available
        if timeout "$timeout" nc -z "$host" "$port" 2>/dev/null; then
            return 0
        fi
    elif command -v telnet >/dev/null 2>&1; then
        # Fallback to telnet
        if timeout "$timeout" bash -c "echo >/dev/tcp/$host/$port" 2>/dev/null; then
            return 0
        fi
    else
        # Last resort: use /dev/tcp
        if timeout "$timeout" bash -c "exec 3<>/dev/tcp/$host/$port && exec 3<&-" 2>/dev/null; then
            return 0
        fi
    fi
    
    return 1
}

# Test HTTP endpoint
test_http_endpoint() {
    local url="$1"
    local timeout="$2"
    local start_time end_time response_time http_code
    
    if ! command -v curl >/dev/null 2>&1; then
        log "WARN" "curl not available, skipping HTTP test"
        return 1
    fi
    
    start_time=$(date +%s.%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time "$timeout" --connect-timeout "$timeout" "$url" 2>/dev/null || echo "000")
    end_time=$(date +%s.%N)
    
    response_time=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "0")
    response_time_ms=$(echo "$response_time * 1000" | bc 2>/dev/null || echo "0")
    
    SERVICE_RESPONSE_TIME["${url}"]="$response_time_ms"
    
    if [[ "$http_code" =~ ^[2-3][0-9][0-9]$ ]]; then
        return 0
    else
        log "WARN" "HTTP endpoint $url returned status code: $http_code"
        return 1
    fi
}

# Test database connectivity
test_database_connectivity() {
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    cd "$PROJECT_DIR"
    
    # Try to connect to MySQL
    if $compose_cmd exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null; then
        return 0
    else
        return 1
    fi
}

# Test Redis connectivity
test_redis_connectivity() {
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    cd "$PROJECT_DIR"
    
    # Try to ping Redis
    if $compose_cmd exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
        return 0
    else
        return 1
    fi
}

# Get container resource usage
get_container_resources() {
    local service_name="$1"
    local container_id
    
    container_id=$(docker-compose ps -q "$service_name" 2>/dev/null || echo "")
    
    if [[ -z "$container_id" ]]; then
        echo "N/A"
        return
    fi
    
    local stats
    if command -v docker >/dev/null 2>&1; then
        stats=$(docker stats --no-stream --format "table {{.CPUPerc}}\t{{.MemUsage}}" "$container_id" 2>/dev/null | tail -n 1)
        echo "$stats"
    else
        echo "N/A"
    fi
}

# Check individual service
check_service() {
    local service_name="$1"
    local service_config="${SERVICES[$service_name]}"
    
    IFS=':' read -r port url data_path <<< "$service_config"
    
    log "INFO" "Checking service: $service_name"
    
    local status="unknown"
    local details=""
    local response_time="0"
    
    # Check if container is running
    if ! check_container_status "$service_name"; then
        status="down"
        details="Container not running"
        SERVICE_STATUS["$service_name"]="down"
        SERVICE_DETAILS["$service_name"]="$details"
        log "ERROR" "$service_name: $details"
        return 1
    fi
    
    # Test port connectivity
    if ! test_port_connectivity "$port" "$TIMEOUT"; then
        status="unhealthy"
        details="Port $port not responding"
        SERVICE_STATUS["$service_name"]="unhealthy"
        SERVICE_DETAILS["$service_name"]="$details"
        log "WARN" "$service_name: $details"
        return 1
    fi
    
    # Service-specific tests
    case "$service_name" in
        "willowcms"|"phpmyadmin"|"mailpit"|"redis-commander"|"jenkins")
            if test_http_endpoint "$url" "$TIMEOUT"; then
                status="healthy"
                details="HTTP OK (${SERVICE_RESPONSE_TIME[$url]:-0} ms)"
            else
                status="unhealthy"
                details="HTTP endpoint not responding"
            fi
            ;;
        "mysql")
            if test_database_connectivity; then
                status="healthy"
                details="Database responding"
            else
                status="unhealthy"
                details="Database not responding"
            fi
            ;;
        "redis")
            if test_redis_connectivity; then
                status="healthy"
                details="Redis responding"
            else
                status="unhealthy"
                details="Redis not responding"
            fi
            ;;
    esac
    
    # Get resource usage
    local resources
    resources=$(get_container_resources "$service_name")
    if [[ "$resources" != "N/A" ]]; then
        details="$details, Resources: $resources"
    fi
    
    SERVICE_STATUS["$service_name"]="$status"
    SERVICE_DETAILS["$service_name"]="$details"
    
    case "$status" in
        "healthy")
            log "SUCCESS" "$service_name: $details"
            return 0
            ;;
        "unhealthy")
            log "WARN" "$service_name: $details"
            return 1
            ;;
        *)
            log "ERROR" "$service_name: $details"
            return 1
            ;;
    esac
}

# Check all services
check_all_services() {
    local services_to_check=()
    local failed_services=()
    local critical_services=("willowcms" "mysql" "redis")
    local critical_failures=0
    
    if [[ -n "$SPECIFIC_SERVICE" ]]; then
        services_to_check=("$SPECIFIC_SERVICE")
    else
        # Get running services from docker-compose
        local compose_cmd
        compose_cmd=$(get_docker_compose_cmd)
        cd "$PROJECT_DIR"
        
        while IFS= read -r service; do
            if [[ -n "$service" ]] && [[ "${SERVICES[$service]+exists}" ]]; then
                services_to_check+=("$service")
            fi
        done < <($compose_cmd ps --services 2>/dev/null || echo "")
        
        # If no services found from compose, check all defined services
        if [[ ${#services_to_check[@]} -eq 0 ]]; then
            services_to_check=("${!SERVICES[@]}")
        fi
    fi
    
    log "INFO" "Checking ${#services_to_check[@]} services..."
    
    for service in "${services_to_check[@]}"; do
        if ! check_service "$service"; then
            failed_services+=("$service")
            
            # Check if it's a critical service
            for critical in "${critical_services[@]}"; do
                if [[ "$service" == "$critical" ]]; then
                    ((critical_failures++))
                    break
                fi
            done
        fi
    done
    
    # Determine overall status
    if [[ ${#failed_services[@]} -eq 0 ]]; then
        OVERALL_STATUS="healthy"
        log "SUCCESS" "All services are healthy"
        return 0
    elif [[ $critical_failures -gt 0 ]]; then
        OVERALL_STATUS="critical"
        log "ERROR" "Critical services are down: ${failed_services[*]}"
        return 2
    else
        OVERALL_STATUS="degraded"
        log "WARN" "Some services are unhealthy: ${failed_services[*]}"
        return 1
    fi
}

# Output results in table format
output_table() {
    echo
    echo "╔════════════════════════════════════════════════════════════════════════╗"
    echo "║                         Willow CMS Health Check                       ║"
    echo "╚════════════════════════════════════════════════════════════════════════╝"
    echo
    printf "%-20s %-12s %-40s\n" "SERVICE" "STATUS" "DETAILS"
    echo "────────────────────────────────────────────────────────────────────────"
    
    for service in $(echo "${!SERVICE_STATUS[@]}" | tr ' ' '\n' | sort); do
        local status="${SERVICE_STATUS[$service]}"
        local details="${SERVICE_DETAILS[$service]}"
        local status_color=""
        
        case "$status" in
            "healthy")
                status_color="${GREEN}✓ HEALTHY${NC}"
                ;;
            "unhealthy")
                status_color="${YELLOW}⚠ UNHEALTHY${NC}"
                ;;
            "down")
                status_color="${RED}✗ DOWN${NC}"
                ;;
            *)
                status_color="${CYAN}? UNKNOWN${NC}"
                ;;
        esac
        
        printf "%-20s " "$service"
        printf "%-12s" "$status_color"
        printf " %-40s\n" "$details"
    done
    
    echo "────────────────────────────────────────────────────────────────────────"
    
    local overall_color=""
    case "$OVERALL_STATUS" in
        "healthy")
            overall_color="${GREEN}✓ ALL SERVICES HEALTHY${NC}"
            ;;
        "degraded")
            overall_color="${YELLOW}⚠ SOME SERVICES DEGRADED${NC}"
            ;;
        "critical")
            overall_color="${RED}✗ CRITICAL SERVICES DOWN${NC}"
            ;;
    esac
    
    printf "%-20s %-12s\n" "OVERALL STATUS:" "$overall_color"
    echo
}

# Output results in JSON format
output_json() {
    local json_output="{"
    json_output+='"timestamp":"'$TIMESTAMP'",'
    json_output+='"overall_status":"'$OVERALL_STATUS'",'
    json_output+='"services":{'
    
    local first=true
    for service in $(echo "${!SERVICE_STATUS[@]}" | tr ' ' '\n' | sort); do
        if [[ "$first" != true ]]; then
            json_output+=","
        fi
        first=false
        
        local status="${SERVICE_STATUS[$service]}"
        local details="${SERVICE_DETAILS[$service]}"
        local response_time="${SERVICE_RESPONSE_TIME[${service}]:-0}"
        
        json_output+='"'$service'":{'
        json_output+='"status":"'$status'",'
        json_output+='"details":"'$details'",'
        json_output+='"response_time_ms":'$response_time
        json_output+='}'
    done
    
    json_output+='}}'
    
    echo "$json_output" | jq . 2>/dev/null || echo "$json_output"
}

# Output results in simple format
output_simple() {
    echo "OVERALL_STATUS=$OVERALL_STATUS"
    for service in $(echo "${!SERVICE_STATUS[@]}" | tr ' ' '\n' | sort); do
        local status="${SERVICE_STATUS[$service]}"
        echo "${service^^}_STATUS=$status"
    done
}

# Generate output based on format
generate_output() {
    case "$OUTPUT_FORMAT" in
        "table")
            output_table
            ;;
        "json")
            output_json
            ;;
        "simple")
            output_simple
            ;;
    esac
}

# Main execution
main() {
    parse_arguments "$@"
    check_prerequisites
    
    local exit_code=0
    check_all_services || exit_code=$?
    
    generate_output
    
    exit $exit_code
}

# Execute main function with all arguments
main "$@"
