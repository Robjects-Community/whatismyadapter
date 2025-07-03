#!/bin/bash

# SCRIPT BEHAVIOR
# Exit immediately if a command exits with a non-zero status.
# Treat unset variables as an error when substituting.
# Pipelines return the exit status of the last command to exit with a non-zero status,
# or zero if no command exited with a non-zero status.
set -e -u -o pipefail

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
OPERATION=""

# Set UID/GID for Docker containers (Apple Silicon compatibility)
if [[ "$(uname -m)" == "arm64" && "$(uname -s)" == "Darwin" ]]; then
    # On Apple Silicon Macs, create .env file with host user's UID/GID to avoid permission issues
    HOST_UID=$(id -u)
    HOST_GID=$(id -g)
    print_info "Detected Apple Silicon Mac - creating .env with UID:GID ${HOST_UID}:${HOST_GID}"
    
    # Create/update .env file for docker-compose
    {
        echo "UID=${HOST_UID}"
        echo "GID=${HOST_GID}"
    } > .env
    
    print_success "Created .env file for Apple Silicon compatibility"
else
    print_info "Using default UID:GID (1000:1000) for Docker containers"
    # Remove .env file if it exists to use defaults
    if [[ -f .env ]]; then
        rm .env
        print_info "Removed .env file to use defaults"
    fi
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
    
${BOLD}OPERATIONS:${RESET}
    ${YELLOW}-w, --wipe${RESET}              Wipe Docker containers and volumes
    ${YELLOW}-b, --rebuild${RESET}           Rebuild Docker containers from scratch
    ${YELLOW}-r, --restart${RESET}           Restart Docker containers
    ${YELLOW}-m, --migrate${RESET}           Run database migrations only
    ${YELLOW}-c, --continue${RESET}          Continue with normal startup (default)

${BOLD}EXAMPLES:${RESET}
    # Normal startup with prompts
    $PROGNAME
    
    # Start with Jenkins and i18n data
    $PROGNAME -j -i
    
    # Rebuild containers without prompts
    $PROGNAME --rebuild --no-interactive
    
    # Wipe and restart with Jenkins
    $PROGNAME --wipe -j
    
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
    TEMP=$(getopt -o hjinwbrmc -l help,jenkins,i18n,no-interactive,wipe,rebuild,restart,migrate,continue \
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
            if docker compose exec "$MAIN_APP_SERVICE" bin/cake migrations migrate; then
                print_success "Migrations completed successfully"
            else
                print_error "Failed to run migrations"
                exit 1
            fi
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

print_step "Creating required directories..."
# The script creates logs/nginx. If logs/ doesn't allow user write, this might fail or need sudo.
# If logs/nginx is created by this user, the chmod below shouldn't need sudo.
# However, if logs/nginx pre-exists with other ownership, sudo would be needed for chmod.
mkdir -p logs/nginx
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
# We assume bin/cake check_table_exists settings exits 0 if table exists, non-zero otherwise.
if docker compose exec "$MAIN_APP_SERVICE" bin/cake check_table_exists settings 2>/dev/null; then
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
        read -r -p "${CYAN}Do you want to [${YELLOW}W${CYAN}]ipe data, re[${YELLOW}B${CYAN}]uild, [${YELLOW}R${CYAN}]estart, run [${YELLOW}M${CYAN}]igrations or [${YELLOW}C${CYAN}]ontinue? (w/b/r/m/c): ${RESET}" user_choice
        case "${user_choice:0:1}" in
            w|W) handle_operation "wipe" ;;
            b|B) handle_operation "rebuild" ;;
            r|R) handle_operation "restart" ;;
            m|M) handle_operation "migrate" ;;
            c|C|*) handle_operation "continue" ;;
        esac
    else
        # Non-interactive mode without operation specified - continue
        handle_operation "continue"
    fi
fi

# Re-check if database has been set up, as it might have been wiped.
print_step "Re-checking if database has been set up..."
if docker compose exec "$MAIN_APP_SERVICE" bin/cake check_table_exists settings 2>/dev/null; then
    TABLE_EXISTS_FINAL=0
else
    TABLE_EXISTS_FINAL=1
fi

if [ "$TABLE_EXISTS_FINAL" -ne 0 ]; then # If table still does not exist (or command failed)
    print_info "Running initial application setup..."

    print_step "Setting permissions for logs, tmp, webroot (dev environment)..."
    # These directories are expected to be at the project root.
    # If they are not owned by the user running script, sudo will be invoked on Linux.
    # Ensure these directories exist before running this script or handle their creation.
    # For `logs/`, it's partially handled by `mkdir -p logs/nginx` earlier.
    # Consider creating tmp/ and webroot/ explicitly if they might not exist.
    for dir in logs tmp webroot; do
        if [ ! -d "$dir" ]; then
            print_warning "Directory '$dir' does not exist. Skipping chmod for it."
        else
            if chmod -R 777 "$dir/" 2>/dev/null; then
                print_success "Set permissions for $dir"
            else
                print_warning "Could not set permissions for $dir (may need sudo)"
            fi
        fi
    done


    print_step "Running database migrations..."
    if docker compose exec "$MAIN_APP_SERVICE" bin/cake migrations migrate; then
        print_success "Database migrations completed"
    else
        print_error "Failed to run database migrations"
        exit 1
    fi

    print_step "Creating default admin user (admin@test.com / password)..."
    if docker compose exec "$MAIN_APP_SERVICE" bin/cake create_user -u admin -p password -e admin@test.com -a 1; then
        print_success "Default admin user created"
        print_info "Login credentials: ${BOLD}admin@test.com${RESET} / ${BOLD}password${RESET}"
    else
        print_error "Failed to create default admin user"
        exit 1
    fi

    print_step "Importing default data (aiprompts, email_templates)..."
    
    if docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import aiprompts; then
        print_success "AI prompts imported"
    else
        print_warning "Failed to import AI prompts"
    fi
    
    if docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import email_templates; then
        print_success "Email templates imported"
    else
        print_warning "Failed to import email templates"
    fi

    if [ "$LOAD_I18N" -eq 1 ]; then
        print_step "Loading internationalisation data..."
        if docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import internationalisations; then
            print_success "Internationalisation data imported"
        else
            print_warning "Failed to import internationalisation data"
        fi
    fi

    print_success "Initial setup completed!"
fi

print_step "Clearing application cache..."
if docker compose exec "$MAIN_APP_SERVICE" bin/cake cache clear_all; then
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