#!/usr/bin/env bash

##################################################################
# WillowCMS Development Environment Setup Script
##################################################################
#
# 🎯 PURPOSE:
# This script ensures a clean, consistent development environment setup
# from scratch, regardless of any previous deployment state (prod, debug, etc.)
#
# 📁 DIRECTORY STRUCTURE SUPPORT:
# • Automatically detects and works with both:
#   - NEW: ./app/ (reorganized structure)
#   - LEGACY: ./cakephp/ (original structure)
# • Seamless transition support during repository reorganization
# • Handles environment files, cache directories, and configurations
#   for whichever structure is present
#
# 🔄 CLEAN SLATE GUARANTEE:
# • Detects and cleans any previous production/staging configurations
# • Removes conflicting environment variables and settings
# • Ensures development-specific configurations are properly set
# • Handles Docker state conflicts and container cleanup
# • Resets file permissions and directory structure
#
# 🛡️ DEPLOYMENT STATE HANDLING:
# • Production → Development: Safely transitions without data loss
# • Staging → Development: Cleans staging-specific configurations
# • Debug → Development: Resets debug flags and logging levels
# • Corrupted State → Development: Rebuilds from clean state
#
##################################################################

# SCRIPT BEHAVIOR
# Exit immediately if a command exits with a non-zero status.
# Treat unset variables as an error when substituting.
# Pipelines return the exit status of the last command to exit with a non-zero status,
# or zero if no command exited with a non-zero status.
set -euo pipefail

# --- Color Configuration ---
# Check if terminal supports colors
if [[ -t 1 ]] && [[ -n "${TERM:-}" ]] && command -v tput &>/dev/null && tput colors &>/dev/null; then
    COLORS=$(tput colors)
    if [[ $COLORS -ge 8 ]]; then
        # Define color codes
        RED=$(tput setaf 1)
        GREEN=$(tput setaf 2)
        YELLOW=$(tput setaf 3)
        BLUE=$(tput setaf 4)
        MAGENTA=$(tput setaf 5)
        CYAN=$(tput setaf 6)
        BOLD=$(tput bold)
        RESET=$(tput sgr0)
    else
        # No color support
        RED="" GREEN="" YELLOW="" BLUE="" MAGENTA="" CYAN="" BOLD="" RESET=""
    fi
else
    # No color support
    RED="" GREEN="" YELLOW="" BLUE="" MAGENTA="" CYAN="" BOLD="" RESET=""
fi

# --- Color Output Functions ---
print_error() {
    echo "${RED}${BOLD}ERROR:${RESET} ${RED}$*${RESET}" >&2
}

print_success() {
    echo "${GREEN}${BOLD}SUCCESS:${RESET} ${GREEN}$*${RESET}"
}

print_warning() {
    echo "${YELLOW}${BOLD}WARNING:${RESET} ${YELLOW}$*${RESET}"
}

print_info() {
    echo "${BLUE}${BOLD}INFO:${RESET} ${BLUE}$*${RESET}"
}

print_step() {
    echo "${CYAN}${BOLD}==>${RESET} ${CYAN}$*${RESET}"
}

# --- Configuration ---
# Jenkins container is optional
USE_JENKINS=0
# Internationalisation data loading is optional
LOAD_I18N=0
# Interactive mode (can be disabled with --no-interactive)
INTERACTIVE=1
# Operation mode
# Options are: wipe, rebuild, restart, migrate, continue, fresh-dev
OPERATION=""
# Force clean development setup
FORCE_CLEAN_DEV=0
# Skip deployment state cleanup
SKIP_DEPLOYMENT_CLEANUP=0

# --- Environment File Provisioning ---
COMPOSE_DIR="$(pwd)"
# Support both new structure (app/) and legacy (cakephp/)
if [[ -d "${COMPOSE_DIR}/app" ]]; then
    APP_ENV_FILE="${COMPOSE_DIR}/app/config/.env"
    APP_DIR="app"
else
    APP_ENV_FILE="${COMPOSE_DIR}/cakephp/config/.env"
    APP_DIR="cakephp"
fi
COMPOSE_ENV_FILE="${COMPOSE_DIR}/.env"

print_step "Setting up environment configuration..."

# Inform user which directory structure is being used
if [[ "${APP_DIR}" == "app" ]]; then
    print_info "Using NEW directory structure: ./app/"
else
    print_info "Using LEGACY directory structure: ./cakephp/"
fi

# Create project root .env from .env.example if it doesn't exist
if [[ ! -f "${COMPOSE_ENV_FILE}" ]]; then
    if [[ -f "${COMPOSE_DIR}/.env.example" ]]; then
        print_info "Creating project root .env from .env.example..."
        cp "${COMPOSE_DIR}/.env.example" "${COMPOSE_ENV_FILE}"
        print_success "Created ${COMPOSE_ENV_FILE}"
    else
        print_error "Missing .env.example file in project root!"
        exit 1
    fi
else
    print_info "Project root .env already exists, leaving it unchanged"
fi

# Set UID/GID for Docker containers (platform compatibility)
HOST_UID=$(id -u)
HOST_GID=$(id -g)
print_info "Setting UID:GID to ${HOST_UID}:${HOST_GID} for container file permissions"

# Update DOCKER_UID/DOCKER_GID in the .env file
if grep -q "^DOCKER_UID=" "${COMPOSE_ENV_FILE}"; then
    sed -i.bak "s/^DOCKER_UID=.*/DOCKER_UID=${HOST_UID}/" "${COMPOSE_ENV_FILE}"
else
    echo "DOCKER_UID=${HOST_UID}" >> "${COMPOSE_ENV_FILE}"
fi

if grep -q "^DOCKER_GID=" "${COMPOSE_ENV_FILE}"; then
    sed -i.bak "s/^DOCKER_GID=.*/DOCKER_GID=${HOST_GID}/" "${COMPOSE_ENV_FILE}"
else
    echo "DOCKER_GID=${HOST_GID}" >> "${COMPOSE_ENV_FILE}"
fi

# Create Application .env from .env.example if it doesn't exist
if [[ ! -f "${APP_ENV_FILE}" ]]; then
    if [[ -f "${COMPOSE_DIR}/${APP_DIR}/config/.env.example" ]]; then
        print_info "Creating Application .env from .env.example..."
        cp "${COMPOSE_DIR}/${APP_DIR}/config/.env.example" "${APP_ENV_FILE}"
        
        # Generate a secure SECURITY_SALT
        if command -v openssl &> /dev/null; then
            SECURITY_SALT=$(openssl rand -hex 32)
            print_info "Generating secure SECURITY_SALT..."
            sed -i.bak "s/change-me-in-setup/${SECURITY_SALT}/" "${APP_ENV_FILE}"
        else
            print_warning "OpenSSL not found. Please manually set SECURITY_SALT in ${APP_ENV_FILE}"
        fi
        
        print_success "Created ${APP_ENV_FILE}"
    else
        print_error "Missing .env.example file in ${APP_DIR}/config/!"
        exit 1
    fi
else
    print_info "Application .env already exists, leaving it unchanged"
fi

print_step "Loading Docker Compose environment variables..."
if [[ -f "${COMPOSE_ENV_FILE}" ]]; then
    # Export variables from project root .env to make them available to docker-compose
    set -a  # Automatically export all variables
    source "${COMPOSE_ENV_FILE}"
    set +a  # Stop automatically exporting
    print_success "Loaded environment variables from ${COMPOSE_ENV_FILE}"
else
    print_error "Docker Compose .env file not found at ${COMPOSE_ENV_FILE}!"
    exit 1
fi
# Service name for the main application container
MAIN_APP_SERVICE="willowcms"
# Path to the wait-for-it.sh script (used inside the main app container)
WAIT_FOR_IT_SCRIPT_URL="https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh"
WAIT_FOR_IT_FILENAME="wait-for-it.sh"

# --- Argument Parsing ---
PROGNAME="${0##*/}"

show_help() {
    cat << EOF
${BOLD}Willow CMS Development Environment Setup${RESET}

${BOLD}USAGE:${RESET}
    $PROGNAME [OPTIONS]

${BOLD}OPTIONS:${RESET}
    ${GREEN}-h, --help${RESET}              Show this help message and exit
    ${GREEN}-j, --jenkins${RESET}           Include Jenkins service
    ${GREEN}-i, --i18n${RESET}              Load internationalisation data
    ${GREEN}-n, --no-interactive${RESET}    Skip interactive prompts (use with operation flags)
    ${GREEN}--force-clean-dev${RESET}       Force clean development setup (removes all deployment configs)
    ${GREEN}--skip-cleanup${RESET}          Skip deployment state cleanup checks
    
${BOLD}OPERATIONS:${RESET}
    ${YELLOW}-w, --wipe${RESET}              Wipe Docker containers and volumes
    ${YELLOW}-b, --rebuild${RESET}           Rebuild Docker containers from scratch
    ${YELLOW}-r, --restart${RESET}           Restart Docker containers
    ${YELLOW}-m, --migrate${RESET}           Run database migrations only
    ${YELLOW}-c, --continue${RESET}          Continue with normal startup (default)
    ${YELLOW}--fresh-dev${RESET}             Complete fresh development setup (recommended)

${BOLD}EXAMPLES:${RESET}
    # Normal startup with prompts
    $PROGNAME
    
    # Start with Jenkins and i18n data
    $PROGNAME -j -i
    
    # Rebuild containers without prompts
    $PROGNAME --rebuild --no-interactive
    
    # Wipe and restart with Jenkins
    $PROGNAME --wipe -j
    
    # Fresh development setup (cleans any deployment state)
    $PROGNAME --fresh-dev -j -i
    
    # Force clean development (removes all deployment configs)
    $PROGNAME --force-clean-dev --no-interactive
    
    # Just run migrations
    $PROGNAME --migrate

${BOLD}NOTES:${RESET}
    - If no operation is specified, the script will run in normal mode
    - In normal mode with existing setup, you'll be prompted for an action
    - Use --no-interactive to skip all prompts (recommended for automation)

EOF
}

# Parse command line arguments
# Use different getopt approach for macOS compatibility
if [[ "$(uname -s)" == "Darwin" ]]; then
    # macOS doesn't have GNU getopt, use simpler parsing
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -j|--jenkins)
                USE_JENKINS=1
                shift
                ;;
            -i|--i18n)
                LOAD_I18N=1
                shift
                ;;
            -n|--no-interactive)
                INTERACTIVE=0
                shift
                ;;
            -w|--wipe)
                OPERATION="wipe"
                shift
                ;;
            -b|--rebuild)
                OPERATION="rebuild"
                shift
                ;;
            -r|--restart)
                OPERATION="restart"
                shift
                ;;
            -m|--migrate)
                OPERATION="migrate"
                shift
                ;;
            -c|--continue)
                OPERATION="continue"
                shift
                ;;
            --fresh-dev)
                OPERATION="fresh-dev"
                shift
                ;;
            --force-clean-dev)
                FORCE_CLEAN_DEV=1
                shift
                ;;
            --skip-cleanup)
                SKIP_DEPLOYMENT_CLEANUP=1
                shift
                ;;
            *)
                if [[ -n "$1" ]]; then
                    print_error "Unknown argument: $1"
                    show_help
                    exit 1
                fi
                shift
                ;;
        esac
    done
else
    # Use GNU getopt for Linux
    TEMP=$(getopt -o hjinwbrmc -l help,jenkins,i18n,no-interactive,wipe,rebuild,restart,migrate,continue,fresh-dev,force-clean-dev,skip-cleanup \
                  -n "$PROGNAME" -- "$@") || { show_help; exit 1; }
    
    eval set -- "$TEMP"
    
    while true; do
        case "$1" in
            -h|--help)
                show_help
                exit 0
                ;;
            -j|--jenkins)
                USE_JENKINS=1
                shift
                ;;
            -i|--i18n)
                LOAD_I18N=1
                shift
                ;;
            -n|--no-interactive)
                INTERACTIVE=0
                shift
                ;;
            -w|--wipe)
                OPERATION="wipe"
                shift
                ;;
            -b|--rebuild)
                OPERATION="rebuild"
                shift
                ;;
            -r|--restart)
                OPERATION="restart"
                shift
                ;;
            -m|--migrate)
                OPERATION="migrate"
                shift
                ;;
            -c|--continue)
                OPERATION="continue"
                shift
                ;;
            --fresh-dev)
                OPERATION="fresh-dev"
                shift
                ;;
            --force-clean-dev)
                FORCE_CLEAN_DEV=1
                shift
                ;;
            --skip-cleanup)
                SKIP_DEPLOYMENT_CLEANUP=1
                shift
                ;;
            --)
                shift
                break
                ;;
            *)
                print_error "Internal error!"
                exit 1
                ;;
        esac
    done
    
    # Check for any remaining arguments
    if [ "$#" -gt 0 ]; then
        print_error "Unknown arguments: $*"
        show_help
        exit 1
    fi
fi

# --- Helper Functions ---

# Function to detect previous deployment state
detect_deployment_state() {
    print_step "Detecting previous deployment state..."
    local deployment_indicators=()
    
    # Check for production indicators
    if [[ -f ".env" ]] && grep -q "APP_ENV.*=.*prod" ".env" 2>/dev/null; then
        deployment_indicators+=("Production environment detected in .env")
    fi
    
    if [[ -f "${APP_DIR}/config/.env" ]] && grep -q "DEBUG.*=.*false" "${APP_DIR}/config/.env" 2>/dev/null; then
        deployment_indicators+=("Production debug settings detected")
    fi
    
    if [[ -f "app/config/.env" ]] && grep -q "DEBUG.*=.*false" "app/config/.env" 2>/dev/null; then
        deployment_indicators+=("Production debug settings detected (reorganized structure)")
    fi
    
    # Check for staging indicators
    if [[ -f ".env" ]] && grep -q "APP_ENV.*=.*stag" ".env" 2>/dev/null; then
        deployment_indicators+=("Staging environment detected")
    fi
    
    # Check for production Docker configurations
    if docker compose config 2>/dev/null | grep -q "restart.*always" 2>/dev/null; then
        deployment_indicators+=("Production Docker restart policies detected")
    fi
    
    # Check for SSL/HTTPS configurations
    if [[ -f "docker-compose.yml" ]] && grep -q "SSL\|ssl\|HTTPS\|https" "docker-compose.yml" 2>/dev/null; then
        deployment_indicators+=("SSL/HTTPS configuration detected")
    fi
    
    # Check for production log levels
    if [[ -f "${APP_DIR}/config/.env" ]] && grep -q "LOG_LEVEL.*=.*error\|LOG_LEVEL.*=.*warning" "${APP_DIR}/config/.env" 2>/dev/null; then
        deployment_indicators+=("Production log levels detected")
    fi
    
    # Check for production cache settings
    if docker compose exec -T "$MAIN_APP_SERVICE" test -f "/var/www/html/config/app_local.php" 2>/dev/null; then
        deployment_indicators+=("Production cache configuration may be present")
    fi
    
    if [[ ${#deployment_indicators[@]} -gt 0 ]]; then
        print_warning "Previous deployment state detected:"
        for indicator in "${deployment_indicators[@]}"; do
            echo "   ⚠️  $indicator"
        done
        return 0  # Deployment state found
    else
        print_success "No previous deployment state detected"
        return 1  # Clean state
    fi
}

# Function to clean deployment state and ensure development configuration
clean_deployment_state() {
    print_step "Cleaning deployment state for development..."
    
    # Backup any existing .env files before modification
    local backup_dir="deployment-cleanup-backup-$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    
    # Backup environment files
    [[ -f ".env" ]] && cp ".env" "$backup_dir/root.env.backup"
    [[ -f "${APP_DIR}/config/.env" ]] && cp "${APP_DIR}/config/.env" "$backup_dir/${APP_DIR}.env.backup"
    # Support both structures during transition
    [[ -f "cakephp/config/.env" ]] && cp "cakephp/config/.env" "$backup_dir/cakephp.env.backup"
    [[ -f "app/config/.env" ]] && cp "app/config/.env" "$backup_dir/app.env.backup"
    [[ -f "docker-compose.yml" ]] && cp "docker-compose.yml" "$backup_dir/docker-compose.yml.backup"
    
    print_info "Configuration backup created in: $backup_dir"
    
    # Clean environment variables in .env files (prioritize current structure)
    local env_files=(".env" "${APP_DIR}/config/.env")
    # Add other structure if it exists during transition
    if [[ "${APP_DIR}" == "app" ]] && [[ -f "cakephp/config/.env" ]]; then
        env_files+=("cakephp/config/.env")
    elif [[ "${APP_DIR}" == "cakephp" ]] && [[ -f "app/config/.env" ]]; then
        env_files+=("app/config/.env")
    fi
    for env_file in "${env_files[@]}"; do
        if [[ -f "$env_file" ]]; then
            print_info "Cleaning deployment settings in $env_file..."
            
            # Set development environment
            if grep -q "^APP_ENV=" "$env_file"; then
                sed -i.tmp "s/^APP_ENV=.*/APP_ENV=development/" "$env_file"
            else
                echo "APP_ENV=development" >> "$env_file"
            fi
            
            # Enable debug mode
            if grep -q "^DEBUG=" "$env_file"; then
                sed -i.tmp "s/^DEBUG=.*/DEBUG=true/" "$env_file"
            else
                echo "DEBUG=true" >> "$env_file"
            fi
            
            # Set development log level
            if grep -q "^LOG_LEVEL=" "$env_file"; then
                sed -i.tmp "s/^LOG_LEVEL=.*/LOG_LEVEL=debug/" "$env_file"
            else
                echo "LOG_LEVEL=debug" >> "$env_file"
            fi
            
            # Ensure development database settings (if not using Docker defaults)
            if grep -q "^DB_HOST=" "$env_file" && ! grep -q "^DB_HOST=mysql" "$env_file"; then
                sed -i.tmp "s/^DB_HOST=.*/DB_HOST=mysql/" "$env_file"
                print_info "Reset database host to Docker service name (mysql)"
            fi
            
            # Clean up temporary files
            rm -f "$env_file.tmp"
        fi
    done
    
    # Stop any running containers to ensure clean state
    if docker compose ps --services --filter "status=running" | grep -q "."; then
        print_info "Stopping containers to ensure clean development state..."
        docker compose down --remove-orphans
    fi
    
    # Remove any production-specific Docker images that might conflict
    local prod_images=()
    # Use compatible alternative to mapfile for broader shell support
    while IFS= read -r line; do
        [[ -n "$line" ]] && prod_images+=("$line")
    done < <(docker images --format "table {{.Repository}}:{{.Tag}}" | grep -E "prod|production|staging|stage" | tail -n +2 || true)
    
    if [[ ${#prod_images[@]} -gt 0 ]]; then
        print_info "Found production/staging Docker images. Consider cleaning:"
        for image in "${prod_images[@]}"; do
            echo "   📊 $image"
        done
        
        if [[ "$FORCE_CLEAN_DEV" -eq 1 ]]; then
            print_info "Force clean enabled - removing production images..."
            for image in "${prod_images[@]}"; do
                docker rmi "$image" 2>/dev/null || print_warning "Could not remove image: $image"
            done
        fi
    fi
    
    # Clear any production caches
    local cache_dirs=("${APP_DIR}/tmp/cache" "storage/app/cache")
    # Support both structures during transition
    if [[ "${APP_DIR}" == "app" ]] && [[ -d "cakephp/tmp/cache" ]]; then
        cache_dirs+=("cakephp/tmp/cache")
    elif [[ "${APP_DIR}" == "cakephp" ]] && [[ -d "app/tmp/cache" ]]; then
        cache_dirs+=("app/tmp/cache")
    fi
    for cache_dir in "${cache_dirs[@]}"; do
        if [[ -d "$cache_dir" ]]; then
            print_info "Clearing cache directory: $cache_dir"
            rm -rf "$cache_dir"/* 2>/dev/null || true
        fi
    done
    
    print_success "Deployment state cleaned for development"
}

# Function to ensure development-specific configurations
ensure_development_config() {
    print_step "Ensuring development-specific configurations..."
    
    # Ensure debug mode is enabled in app config if it exists
    local app_config_files=("${APP_DIR}/config/app_local.php")
    # Support both structures during transition  
    if [[ "${APP_DIR}" == "app" ]] && [[ -f "cakephp/config/app_local.php" ]]; then
        app_config_files+=("cakephp/config/app_local.php")
    elif [[ "${APP_DIR}" == "cakephp" ]] && [[ -f "app/config/app_local.php" ]]; then
        app_config_files+=("app/config/app_local.php")
    fi
    for config_file in "${app_config_files[@]}"; do
        if [[ -f "$config_file" ]]; then
            # Check if debug is set to false and warn
            if grep -q "'debug'.*=>.*false" "$config_file" 2>/dev/null; then
                print_warning "Debug mode is disabled in $config_file"
                print_info "Consider enabling debug mode for development"
            fi
        fi
    done
    
    # Ensure development-friendly Docker Compose configuration
    if [[ -f "docker-compose.yml" ]]; then
        # Check for production restart policies
        if grep -q "restart.*always" "docker-compose.yml"; then
            print_warning "Production restart policies detected in docker-compose.yml"
            if [[ "$FORCE_CLEAN_DEV" -eq 1 ]]; then
                print_info "Removing production restart policies..."
                sed -i.tmp "s/restart:.*always/# restart: always # Disabled for development/g" "docker-compose.yml"
                rm -f "docker-compose.yml.tmp"
            fi
        fi
    fi
    
    print_success "Development configuration checks completed"
}

# Function to handle fresh development setup
handle_fresh_dev_setup() {
    print_step "Setting up fresh development environment..."
    
    # This combines several operations for a complete fresh setup
    print_info "This will: wipe containers, rebuild, clean deployment state, and setup development"
    
    if [[ "$INTERACTIVE" -eq 1 ]] && [[ "$FORCE_CLEAN_DEV" -eq 0 ]]; then
        read -r -p "${YELLOW}This will remove all containers and data. Continue? (y/N): ${RESET}" confirm
        if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
            print_info "Fresh development setup cancelled"
            return 0
        fi
    fi
    
    # Clean deployment state first
    if [[ "$SKIP_DEPLOYMENT_CLEANUP" -eq 0 ]]; then
        clean_deployment_state
    fi
    
    # Wipe and rebuild containers
    handle_operation "wipe"
    
    # Ensure development configuration
    ensure_development_config
    
    print_success "Fresh development environment setup completed"
}

# Function to check if Docker is installed and running
check_docker_requirements() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        print_error "Docker daemon is not running. Please start Docker first."
        exit 1
    fi
}

# Function to check if the main Docker container is running
check_docker_status() {
    # Check if the main app service is running with an exact name match
    if docker compose ps --services --filter "status=running" | grep -q "^${MAIN_APP_SERVICE}$"; then
        return 0  # Container is running
    else
        return 1  # Container is not running
    fi
}

# Function to start Docker containers
start_docker_containers() {
    print_step "Starting Docker containers..."
    local services="${MAIN_APP_SERVICE} mysql phpmyadmin mailpit redis-commander"
    if [ "$USE_JENKINS" -eq 1 ]; then
        print_info "Including Jenkins in startup..."
        services="$services jenkins"
    else
        print_info "Starting without Jenkins..."
    fi
    # SC2086: Double quote to prevent globbing and word splitting (services is intentionally unquoted here for splitting)
    # shellcheck disable=SC2086
    if docker compose up -d $services; then
        print_success "Docker containers started successfully"
    else
        print_error "Failed to start Docker containers"
        exit 1
    fi
}

# Function to wait for MySQL to be ready
wait_for_mysql() {
    print_step "Waiting for MySQL to be ready..."
    # Downloads wait-for-it.sh inside the container if it doesn't exist.
    # -f: fail silently on server errors. -s: silent mode. -S: show error on stderr. -L: follow redirects. -o: output to file.
    local wait_command_script
    wait_command_script=$(cat <<EOF
if [ ! -f "${WAIT_FOR_IT_FILENAME}" ]; then
    echo "Downloading ${WAIT_FOR_IT_FILENAME}..."
    if curl -fsSL -o "${WAIT_FOR_IT_FILENAME}" "${WAIT_FOR_IT_SCRIPT_URL}"; then
        chmod +x "${WAIT_FOR_IT_FILENAME}"
    else
        echo "Error: Failed to download ${WAIT_FOR_IT_FILENAME}. Cannot proceed with MySQL wait." >&2
        exit 1
    fi
elif [ ! -x "${WAIT_FOR_IT_FILENAME}" ]; then
    chmod +x "${WAIT_FOR_IT_FILENAME}"
fi
./"${WAIT_FOR_IT_FILENAME}" mysql:3306 -t 60 -- echo "MySQL is ready"
EOF
)
    if docker compose exec "$MAIN_APP_SERVICE" bash -c "$wait_command_script"; then
        print_success "MySQL is ready"
    else
        print_error "MySQL failed to become ready within timeout"
        exit 1
    fi
}

# Function to start/restart Docker containers and wait for MySQL
start_and_wait_services() {
    start_docker_containers
    wait_for_mysql
}

# Function to handle operations
handle_operation() {
    local op="$1"
    case "$op" in
        wipe)
            print_step "Wiping Docker containers and volumes..."
            if docker compose down -v --remove-orphans; then
                print_success "Docker containers and volumes wiped"
                start_and_wait_services
            else
                print_error "Failed to wipe Docker containers"
                exit 1
            fi
            ;;
        rebuild)
            print_step "Rebuilding Docker containers..."
            if docker compose down --remove-orphans && \
               docker compose rm -f && \
               docker compose build --no-cache; then
                print_success "Docker containers rebuilt"
                start_and_wait_services
            else
                print_error "Failed to rebuild Docker containers"
                exit 1
            fi
            ;;
        restart)
            print_step "Restarting Docker containers..."
            if docker compose down --remove-orphans; then
                start_and_wait_services
            else
                print_error "Failed to restart Docker containers"
                exit 1
            fi
            ;;
        migrate)
            print_step "Running database migrations..."
            if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake migrations migrate; then
                print_success "Migrations completed successfully"
            else
                print_error "Failed to run migrations"
                exit 1
            fi
            ;;
        fresh-dev)
            handle_fresh_dev_setup
            ;;
        continue|"")
            print_info "Continuing with normal startup..."
            ;;
        *)
            print_error "Unknown operation: $op"
            exit 1
            ;;
    esac
}

# --- Main Script Execution ---

# Check Docker requirements first
check_docker_requirements

# Handle force clean development setup
if [[ "$FORCE_CLEAN_DEV" -eq 1 ]]; then
    print_info "Force clean development mode enabled"
    clean_deployment_state
    ensure_development_config
fi

# Detect and optionally clean deployment state
if [[ "$SKIP_DEPLOYMENT_CLEANUP" -eq 0 ]] && [[ "$OPERATION" != "fresh-dev" ]] && [[ "$FORCE_CLEAN_DEV" -eq 0 ]]; then
    if detect_deployment_state; then
        if [[ "$INTERACTIVE" -eq 1 ]]; then
            read -r -p "${YELLOW}Clean deployment state for development? [Y/n]: ${RESET}" clean_choice
            if [[ ! "$clean_choice" =~ ^[Nn]$ ]]; then
                clean_deployment_state
                ensure_development_config
            fi
        else
            print_info "Non-interactive mode: skipping deployment cleanup (use --force-clean-dev to enable)"
        fi
    fi
fi

print_step "Creating required directories..."
# The script creates logs/nginx. If logs/ doesn't allow user write, this might fail or need sudo.
# If logs/nginx is created by this user, the chmod below shouldn't need sudo.
# However, if logs/nginx pre-exists with other ownership, sudo would be needed for chmod.
mkdir -p logs/nginx
mkdir -p ${APP_DIR}/logs ${APP_DIR}/tmp ${APP_DIR}/webroot/files
if chmod 777 logs/nginx 2>/dev/null; then
    print_success "Created logs/nginx directory"
else
    print_warning "Could not set permissions on logs/nginx (may need sudo)"
fi

print_step "Checking Docker container status..."
if ! check_docker_status; then
    start_and_wait_services
else
    print_info "Docker containers are already running."
    # Even if containers are running, MySQL might not be ready (e.g., after a host reboot)
    wait_for_mysql
fi

print_step "Installing/updating Composer dependencies..."
if docker compose exec "$MAIN_APP_SERVICE" composer install --no-interaction --prefer-dist --optimize-autoloader; then
    print_success "Composer dependencies installed"
else
    print_error "Failed to install Composer dependencies"
    exit 1
fi

print_step "Checking if database has been set up (looking for 'settings' table)..."
# docker compose exec exits with 0 if command succeeds, 1 if fails.
# We assume /var/www/html/bin/cake check_table_exists settings exits 0 if table exists, non-zero otherwise.
if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake check_table_exists settings 2>/dev/null; then
    TABLE_EXISTS_INITIAL=0 # True, table exists
else
    TABLE_EXISTS_INITIAL=1 # False, table does not exist / command failed
fi

if [ "$TABLE_EXISTS_INITIAL" -eq 0 ]; then
    print_info "Subsequent container startup detected (database appears to be initialized)."
    
    # If an operation was specified via command line, execute it
    if [ -n "$OPERATION" ]; then
        handle_operation "$OPERATION"
    elif [ "$INTERACTIVE" -eq 1 ]; then
        # Interactive mode - prompt for action
        read -r -p "${CYAN}Do you want to [${YELLOW}W${CYAN}]ipe data, re[${YELLOW}B${CYAN}]uild, [${YELLOW}R${CYAN}]estart, [${YELLOW}F${CYAN}]resh dev setup, run [${YELLOW}M${CYAN}]igrations or [${YELLOW}C${CYAN}]ontinue? (w/b/r/f/m/c): ${RESET}" user_choice
        case "${user_choice:0:1}" in
            w|W) handle_operation "wipe" ;;
            b|B) handle_operation "rebuild" ;;
            r|R) handle_operation "restart" ;;
            m|M) handle_operation "migrate" ;;
            f|F) handle_operation "fresh-dev" ;;
            c|C|*) handle_operation "continue" ;;
        esac
    else
        # Non-interactive mode without operation specified - continue
        handle_operation "continue"
    fi
fi

# Re-check if database has been set up, as it might have been wiped.
print_step "Re-checking if database has been set up..."
if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake check_table_exists settings 2>/dev/null; then
    TABLE_EXISTS_FINAL=0
else
    TABLE_EXISTS_FINAL=1
fi

if [ "$TABLE_EXISTS_FINAL" -ne 0 ]; then # If table still does not exist (or command failed)
    print_info "Running initial application setup..."

    print_step "Setting permissions for logs, tmp, webroot (dev environment)..."
    # These directories are expected to be in the app folder.
    # If they are not owned by the user running script, sudo will be invoked on Linux.
    # Ensure these directories exist before running this script or handle their creation.
    # For `logs/`, it's partially handled by `mkdir -p logs/nginx` earlier.
    # Consider creating tmp/ and webroot/ explicitly if they might not exist.
    for dir in ${APP_DIR}/logs ${APP_DIR}/tmp ${APP_DIR}/webroot; do
        if [ ! -d "$dir" ]; then
            print_warning "Directory '$dir' does not exist. Creating it..."
            mkdir -p "$dir"
        fi
        if chmod -R 777 "$dir/" 2>/dev/null; then
            print_success "Set permissions for $dir"
        else
            print_warning "Could not set permissions for $dir (may need sudo)"
        fi
    done


    print_step "Running database migrations..."
    if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake migrations migrate; then
        print_success "Database migrations completed"
    else
        print_error "Failed to run database migrations"
        exit 1
    fi

    print_step "Creating default admin user (admin@test.com / password)..."
    if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake create_user -u admin -p password -e admin@test.com -a 1; then
        print_success "Default admin user created"
        print_info "Login credentials: ${BOLD}admin@test.com${RESET} / ${BOLD}password${RESET}"
    else
        print_error "Failed to create default admin user"
        exit 1
    fi

    print_step "Importing default data (aiprompts, email_templates)..."
    
    if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake default_data_import aiprompts; then
        print_success "AI prompts imported"
    else
        print_warning "Failed to import AI prompts"
    fi
    
    if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake default_data_import email_templates; then
        print_success "Email templates imported"
    else
        print_warning "Failed to import email templates"
    fi

    if [ "$LOAD_I18N" -eq 1 ]; then
        print_step "Loading internationalisation data..."
        if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake default_data_import internationalisations; then
            print_success "Internationalisation data imported"
        else
            print_warning "Failed to import internationalisation data"
        fi
    fi

    print_success "Initial setup completed!"
fi

print_step "Clearing application cache..."
if docker compose exec "$MAIN_APP_SERVICE" /var/www/html/bin/cake cache clear_all; then
    print_success "Application cache cleared"
else
    print_warning "Failed to clear application cache"
fi

print_success "Development environment setup complete!"
print_info "You can access Willow CMS at: ${BOLD}http://localhost:8080${RESET}"
print_info "Admin area: ${BOLD}http://localhost:8080/admin${RESET}"

if [ "$USE_JENKINS" -eq 1 ]; then
    print_info "Jenkins: ${BOLD}http://localhost:8081${RESET}"
fi

print_info "PHPMyAdmin: ${BOLD}http://localhost:8082${RESET}"
print_info "Mailpit: ${BOLD}http://localhost:8025${RESET}"
print_info "Redis Commander: ${BOLD}http://localhost:8084${RESET}"

# Final deployment state summary
echo
if [[ "$FORCE_CLEAN_DEV" -eq 1 ]] || [[ "$OPERATION" == "fresh-dev" ]]; then
    print_success "✨ Clean development environment guaranteed!"
    print_info "   🧹 All deployment configurations cleaned"
    print_info "   🔧 Development settings enforced"
    print_info "   🐳 Fresh Docker containers"
else
    print_success "🚀 Development environment ready!"
    if [[ "$SKIP_DEPLOYMENT_CLEANUP" -eq 0 ]]; then
        print_info "   ✅ Deployment state checked and cleaned if needed"
    else
        print_info "   ⚠️  Deployment state cleanup was skipped"
    fi
fi
print_info "   📊 All services running and accessible"
print_info "   🛡️  Development-optimized configuration active"
