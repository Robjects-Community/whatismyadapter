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

# Docker Swarm Stack configuration
SWARM_STACK_NAME="willowcms-swarm-test"
SWARM_COMPOSE_FILE="willow-swarm-stack.yml"
WILLOWCMS_IMAGE="willowcms:portainer"

# Service name for the main application container in the swarm
MAIN_APP_SERVICE="${SWARM_STACK_NAME}_willowcms"

# Path to the wait-for-it.sh script (used inside the main app container)
WAIT_FOR_IT_SCRIPT_URL="https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh"
WAIT_FOR_IT_FILENAME="wait-for-it.sh"

# --- Argument Parsing ---
PROGNAME="${0##*/}"

show_help() {
    cat << EOF
${BOLD}Willow CMS Docker Swarm Development Environment Setup${RESET}

${BOLD}USAGE:${RESET}
    $PROGNAME [OPTIONS]

${BOLD}OPTIONS:${RESET}
    ${GREEN}-h, --help${RESET}              Show this help message and exit
    ${GREEN}-j, --jenkins${RESET}           Include Jenkins service
    ${GREEN}-i, --i18n${RESET}              Load internationalisation data
    ${GREEN}-n, --no-interactive${RESET}    Skip interactive prompts (use with operation flags)

${BOLD}OPERATIONS:${RESET}
    ${YELLOW}-w, --wipe${RESET}              Remove Docker Swarm stack and recreate
    ${YELLOW}-b, --rebuild${RESET}           Rebuild Docker image and redeploy stack
    ${YELLOW}-r, --restart${RESET}           Remove and restart Docker Swarm stack
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
    - This script works with Docker Swarm instead of Docker Compose
    - The app directory ./app/ is baked into the Docker image
    - Stack name: ${SWARM_STACK_NAME}
    - Compose file: ${SWARM_COMPOSE_FILE}
    - If no operation is specified, the script will run in normal mode
    - In normal mode with existing setup, you'll be prompted for an action
    - Use --no-interactive to skip all prompts (recommended for automation)

${BOLD}SWARM SERVICES ACCESS:${RESET}
    - Main Site: ${BOLD}http://localhost:7770${RESET}
    - Admin Panel: ${BOLD}http://localhost:7770/en/users/login${RESET}
    - PHPMyAdmin: ${BOLD}http://localhost:7771${RESET}
    - Mailpit: ${BOLD}http://localhost:7773${RESET}
    - Redis Commander: ${BOLD}http://localhost:7774${RESET}
    - Jenkins: ${BOLD}http://localhost:7772${RESET} (if enabled)

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

    if ! docker info &> /dev/null; then
        print_error "Docker daemon is not running. Please start Docker first."
        exit 1
    fi

    # Check if swarm mode is enabled
    if ! docker info | grep -q "Swarm: active"; then
        print_warning "Docker Swarm mode is not active. Initializing swarm mode..."
        if docker swarm init --advertise-addr 127.0.0.1; then
            print_success "Docker Swarm mode initialized"
        else
            print_error "Failed to initialize Docker Swarm mode"
            exit 1
        fi
    else
        print_info "Docker Swarm mode is already active"
    fi
}

# Function to check if the Docker stack is running
check_stack_status() {
    if docker stack ls | grep -q "^${SWARM_STACK_NAME} "; then
        return 0  # Stack exists
    else
        return 1  # Stack does not exist
    fi
}

# Function to check if WillowCMS service is running
check_willowcms_service_status() {
    if docker stack services "${SWARM_STACK_NAME}" 2>/dev/null | grep -q "${MAIN_APP_SERVICE}"; then
        # Check if the service has running replicas
        local replicas
        replicas=$(docker stack services "${SWARM_STACK_NAME}" --format "table {{.Name}}\t{{.Replicas}}" | grep "${MAIN_APP_SERVICE}" | awk '{print $2}')
        if [[ "$replicas" =~ ^[1-9][0-9]*/[1-9][0-9]*$ ]]; then
            return 0  # Service is running with replicas
        fi
    fi
    return 1  # Service is not running properly
}

# Function to build WillowCMS Docker image
build_willowcms_image() {
    print_step "Building WillowCMS Docker image from ./app/ directory..."
    
    # Create a temporary Dockerfile that copies the app directory
    local temp_dockerfile=$(mktemp)
    cat > "$temp_dockerfile" << 'EOF'
# Use the same base as the original WillowCMS Dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    autoconf \
    bash \
    build-base \
    curl \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    nginx \
    oniguruma-dev \
    supervisor \
    tzdata \
    zip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        xml \
        zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create nginx user and group (if they don't exist)
RUN (addgroup -g 82 -S nginx 2>/dev/null || true) && \
    (adduser -u 82 -D -S -G nginx nginx 2>/dev/null || true)

# Copy application files
COPY app/ /var/www/html/
WORKDIR /var/www/html

# Set permissions
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/tmp /var/www/html/logs /var/www/html/webroot

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy nginx configuration
COPY <<'NGINX_CONF' /etc/nginx/nginx.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log notice;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log /var/log/nginx/access.log main;
    sendfile on;
    keepalive_timeout 65;
    
    server {
        listen 80;
        server_name _;
        root /var/www/html/webroot;
        index index.php;
        
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        
        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
        
        location ~ /\.ht {
            deny all;
        }
    }
}
NGINX_CONF

# Copy supervisor configuration
COPY <<'SUPERVISOR_CONF' /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.err.log
stdout_logfile=/var/log/supervisor/nginx.out.log

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.err.log
stdout_logfile=/var/log/supervisor/php-fpm.out.log

[program:redis]
command=redis-server --bind 0.0.0.0 --port 6379 --requirepass root
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/redis.err.log
stdout_logfile=/var/log/supervisor/redis.out.log
SUPERVISOR_CONF

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Expose port 80
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

    # Build the image using the temporary Dockerfile
    if docker build -f "$temp_dockerfile" -t "$WILLOWCMS_IMAGE" .; then
        print_success "WillowCMS Docker image built successfully"
        rm "$temp_dockerfile"
    else
        print_error "Failed to build WillowCMS Docker image"
        rm "$temp_dockerfile"
        exit 1
    fi
}

# Function to deploy Docker stack
deploy_stack() {
    print_step "Deploying Docker Swarm stack..."
    
    local stack_services=""
    if [ "$USE_JENKINS" -eq 1 ]; then
        print_info "Including Jenkins in stack deployment..."
        # Deploy all services including Jenkins
        if docker stack deploy -c "$SWARM_COMPOSE_FILE" "$SWARM_STACK_NAME"; then
            print_success "Docker stack deployed successfully with Jenkins"
        else
            print_error "Failed to deploy Docker stack"
            exit 1
        fi
    else
        print_info "Deploying without Jenkins..."
        # Create a temporary compose file without Jenkins
        local temp_compose=$(mktemp)
        # Remove jenkins service from compose file
        sed '/^  jenkins:/,/^  [^[:space:]]/{ /^  jenkins:/d; /^  [^[:space:]]/!d; }' "$SWARM_COMPOSE_FILE" > "$temp_compose"
        
        if docker stack deploy -c "$temp_compose" "$SWARM_STACK_NAME"; then
            print_success "Docker stack deployed successfully without Jenkins"
            rm "$temp_compose"
        else
            print_error "Failed to deploy Docker stack"
            rm "$temp_compose"
            exit 1
        fi
    fi
}

# Function to wait for MySQL to be ready
wait_for_mysql() {
    print_step "Waiting for MySQL service to be ready..."
    
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker exec $(docker ps -q --filter "label=com.docker.swarm.service.name=${SWARM_STACK_NAME}_mysql" | head -1) \
           mysqladmin ping -h localhost -u root -ppassword >/dev/null 2>&1; then
            print_success "MySQL is ready"
            return 0
        fi
        
        attempt=$((attempt + 1))
        print_info "Waiting for MySQL... (attempt $attempt/$max_attempts)"
        sleep 5
    done
    
    print_error "MySQL failed to become ready within timeout"
    exit 1
}

# Function to wait for WillowCMS service to be ready
wait_for_willowcms() {
    print_step "Waiting for WillowCMS service to be ready..."
    
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if check_willowcms_service_status; then
            # Additional check: try to get a container ID and test HTTP response
            local container_id
            container_id=$(docker ps -q --filter "label=com.docker.swarm.service.name=${MAIN_APP_SERVICE}" | head -1)
            if [[ -n "$container_id" ]]; then
                print_success "WillowCMS service is ready"
                return 0
            fi
        fi
        
        attempt=$((attempt + 1))
        print_info "Waiting for WillowCMS service... (attempt $attempt/$max_attempts)"
        sleep 10
    done
    
    print_error "WillowCMS service failed to become ready within timeout"
    exit 1
}

# Function to start/restart Docker stack and wait for services
start_and_wait_services() {
    deploy_stack
    wait_for_mysql
    wait_for_willowcms
}

# Function to handle operations
handle_operation() {
    local op="$1"
    case "$op" in
        wipe)
            print_step "Removing Docker Swarm stack..."
            if docker stack rm "$SWARM_STACK_NAME"; then
                print_success "Docker stack removed"
                print_info "Waiting for stack to be fully removed..."
                sleep 10
                start_and_wait_services
            else
                print_error "Failed to remove Docker stack"
                exit 1
            fi
            ;;
        rebuild)
            print_step "Rebuilding Docker image and redeploying stack..."
            build_willowcms_image
            if docker stack rm "$SWARM_STACK_NAME" 2>/dev/null || true; then
                print_info "Removed existing stack, waiting for cleanup..."
                sleep 10
            fi
            start_and_wait_services
            ;;
        restart)
            print_step "Restarting Docker Swarm stack..."
            if docker stack rm "$SWARM_STACK_NAME"; then
                print_info "Stack removed, waiting for cleanup..."
                sleep 10
                start_and_wait_services
            else
                print_error "Failed to restart Docker stack"
                exit 1
            fi
            ;;
        migrate)
            print_step "Running database migrations..."
            local container_id
            container_id=$(docker ps -q --filter "label=com.docker.swarm.service.name=${MAIN_APP_SERVICE}" | head -1)
            if [[ -n "$container_id" ]]; then
                if docker exec "$container_id" bin/cake migrations migrate; then
                    print_success "Migrations completed successfully"
                else
                    print_error "Failed to run migrations"
                    exit 1
                fi
            else
                print_error "No running WillowCMS container found"
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

# Ensure the app directory exists
if [[ ! -d "app" ]]; then
    print_error "The ./app/ directory does not exist. Please ensure WillowCMS application files are in ./app/"
    exit 1
fi

# Ensure the swarm compose file exists
if [[ ! -f "$SWARM_COMPOSE_FILE" ]]; then
    print_error "Swarm compose file '$SWARM_COMPOSE_FILE' not found"
    exit 1
fi

print_step "Creating required directories..."
# Create logs directory for the local host if needed
mkdir -p logs/nginx
if chmod 777 logs/nginx 2>/dev/null; then
    print_success "Created logs/nginx directory"
else
    print_warning "Could not set permissions on logs/nginx (may need sudo)"
fi

print_step "Checking Docker Swarm stack status..."
if ! check_stack_status; then
    print_info "Docker stack not found, will create it..."
    build_willowcms_image
    start_and_wait_services
else
    print_info "Docker stack is running."
    if check_willowcms_service_status; then
        print_info "WillowCMS service is running."
        wait_for_mysql  # Ensure MySQL is ready even if services are running
    else
        print_warning "WillowCMS service is not properly running."
    fi
fi

# Get a container ID for running commands
print_step "Finding running WillowCMS container..."
CONTAINER_ID=$(docker ps -q --filter "label=com.docker.swarm.service.name=${MAIN_APP_SERVICE}" | head -1)
if [[ -z "$CONTAINER_ID" ]]; then
    print_error "No running WillowCMS container found"
    exit 1
fi
print_success "Found WillowCMS container: $CONTAINER_ID"

print_step "Installing/updating Composer dependencies..."
if docker exec "$CONTAINER_ID" composer install --no-interaction --prefer-dist --optimize-autoloader; then
    print_success "Composer dependencies installed"
else
    print_error "Failed to install Composer dependencies"
    exit 1
fi

print_step "Checking if database has been set up (looking for 'settings' table)..."
# docker exec exits with 0 if command succeeds, 1 if fails.
if docker exec "$CONTAINER_ID" bin/cake check_table_exists settings 2>/dev/null; then
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

# Re-get container ID in case it changed during operations
CONTAINER_ID=$(docker ps -q --filter "label=com.docker.swarm.service.name=${MAIN_APP_SERVICE}" | head -1)
if [[ -z "$CONTAINER_ID" ]]; then
    print_error "No running WillowCMS container found after operations"
    exit 1
fi

# Re-check if database has been set up, as it might have been wiped.
print_step "Re-checking if database has been set up..."
if docker exec "$CONTAINER_ID" bin/cake check_table_exists settings 2>/dev/null; then
    TABLE_EXISTS_FINAL=0
else
    TABLE_EXISTS_FINAL=1
fi

if [ "$TABLE_EXISTS_FINAL" -ne 0 ]; then # If table still does not exist (or command failed)
    print_info "Running initial application setup..."

    print_step "Running database migrations..."
    if docker exec "$CONTAINER_ID" bin/cake migrations migrate; then
        print_success "Database migrations completed"
    else
        print_error "Failed to run database migrations"
        exit 1
    fi

    print_step "Creating default admin user (admin@test.com / password)..."
    if docker exec "$CONTAINER_ID" bin/cake create_user -u admin -p password -e admin@test.com -a 1; then
        print_success "Default admin user created"
        print_info "Login credentials: ${BOLD}admin@test.com${RESET} / ${BOLD}password${RESET}"
    else
        print_error "Failed to create default admin user"
        exit 1
    fi

    print_step "Importing default data (aiprompts, email_templates)..."

    if docker exec "$CONTAINER_ID" bin/cake default_data_import aiprompts 2>/dev/null; then
        print_success "AI prompts imported"
    else
        print_warning "Failed to import AI prompts (may not be available)"
    fi

    if docker exec "$CONTAINER_ID" bin/cake default_data_import email_templates 2>/dev/null; then
        print_success "Email templates imported"
    else
        print_warning "Failed to import email templates (may not be available)"
    fi

    if [ "$LOAD_I18N" -eq 1 ]; then
        print_step "Loading internationalisation data..."
        if docker exec "$CONTAINER_ID" bin/cake default_data_import internationalisations 2>/dev/null; then
            print_success "Internationalisation data imported"
        else
            print_warning "Failed to import internationalisation data (may not be available)"
        fi
    fi

    print_success "Initial setup completed!"
fi

print_step "Clearing application cache..."
if docker exec "$CONTAINER_ID" bin/cake cache clear_all; then
    print_success "Application cache cleared"
else
    print_warning "Failed to clear application cache"
fi

print_success "Docker Swarm development environment setup complete!"
echo
print_info "Stack Name: ${BOLD}${SWARM_STACK_NAME}${RESET}"
print_info "You can access Willow CMS at: ${BOLD}http://localhost:7770${RESET}"
print_info "Admin login: ${BOLD}http://localhost:7770/en/users/login${RESET}"
print_info "Login credentials: ${BOLD}admin@test.com${RESET} / ${BOLD}password${RESET}"
echo

if [ "$USE_JENKINS" -eq 1 ]; then
    print_info "Jenkins: ${BOLD}http://localhost:7772${RESET}"
fi

print_info "PHPMyAdmin: ${BOLD}http://localhost:7771${RESET} (root/password)"
print_info "Mailpit: ${BOLD}http://localhost:7773${RESET} (email testing)"
print_info "Redis Commander: ${BOLD}http://localhost:7774${RESET} (admin/password)"
echo

print_step "Stack Status:"
docker stack services "$SWARM_STACK_NAME"
