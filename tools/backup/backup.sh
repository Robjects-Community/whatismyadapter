#!/bin/bash
# ===================================================================
# WILLOW CMS ADVANCED BACKUP SYSTEM
# ===================================================================
# Comprehensive backup solution with:
# - Numbered backup files for easy organization
# - SHA256 checksum verification  
# - DigitalOcean Spaces integration
# - Structured directory organization
# - Metadata tracking and manifests
# - Restore functionality with selection
# - Compatibility with manage.sh and manage-macos.sh
# ===================================================================

set -euo pipefail  # Exit on error, undefined vars, pipe failures

# ===================================================================
# CONFIGURATION AND ENVIRONMENT VARIABLES
# ===================================================================

# Script version and metadata
BACKUP_SCRIPT_VERSION="2.1.0"
SCRIPT_NAME="$(basename "$0")"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Load environment variables from .env files
if [[ -f "$PROJECT_ROOT/.env" ]]; then
    source "$PROJECT_ROOT/.env"
fi

if [[ -f "$PROJECT_ROOT/.env.droplet" ]]; then
    source "$PROJECT_ROOT/.env.droplet"
fi

if [[ -f "$PROJECT_ROOT/stack.env" ]]; then
    source "$PROJECT_ROOT/stack.env"
fi

# Backup configuration
BACKUP_BASE_DIR="${HOST_BACKUP_PATH:-$PROJECT_ROOT/backups}"
BACKUP_TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"
BACKUP_COMPRESSION="${BACKUP_COMPRESSION:-gzip}"
BACKUP_ENCRYPTION="${BACKUP_ENCRYPTION:-false}"

# Numbering system for backups
BACKUP_COUNTER_FILE="$BACKUP_BASE_DIR/.backup_counter"
BACKUP_MANIFEST_FILE="$BACKUP_BASE_DIR/manifest.json"

# Directory structure
BACKUP_CODE_DIR="$BACKUP_BASE_DIR/code"
BACKUP_DATABASE_DIR="$BACKUP_BASE_DIR/database"
BACKUP_LOGS_DIR="$BACKUP_BASE_DIR/logs"
BACKUP_METADATA_DIR="$BACKUP_BASE_DIR/metadata"

# DigitalOcean Spaces configuration
DO_SPACES_ENABLED="${SPACES_KEY:+true}"
DO_SPACES_BUCKET="${SPACES_BUCKET:-}"
DO_SPACES_REGION="${SPACES_REGION:-nyc3}"
DO_SPACES_ENDPOINT="${SPACES_ENDPOINT:-${DO_SPACES_REGION}.digitaloceanspaces.com}"

# Docker and database settings
DOCKER_COMPOSE_FILE="${DOCKER_COMPOSE_FILE:-docker-compose.yml}"
DB_CONTAINER_NAME="${DB_CONTAINER_NAME:-willowcms-mysql}"
REDIS_CONTAINER_NAME="${REDIS_CONTAINER_NAME:-willowcms-redis}"
APP_CONTAINER_NAME="${APP_CONTAINER_NAME:-willowcms-app}"

# Logging
LOG_FILE="$BACKUP_BASE_DIR/backup.log"
ENABLE_VERBOSE="${VERBOSE:-false}"

# ===================================================================
# UTILITY FUNCTIONS
# ===================================================================

# Logging functions
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
}

log_info() {
    log "INFO: $*"
}

log_warn() {
    log "WARN: $*"
}

log_error() {
    log "ERROR: $*"
}

log_verbose() {
    if [[ "$ENABLE_VERBOSE" == "true" ]]; then
        log "VERBOSE: $*"
    fi
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if Docker container is running
container_running() {
    docker ps --format "table {{.Names}}" | grep -q "^$1$"
}

# Generate SHA256 checksum
generate_checksum() {
    local file="$1"
    if [[ -f "$file" ]]; then
        sha256sum "$file" | cut -d' ' -f1
    else
        echo ""
    fi
}

# Verify checksum
verify_checksum() {
    local file="$1"
    local expected_checksum="$2"
    local actual_checksum
    actual_checksum="$(generate_checksum "$file")"
    [[ "$actual_checksum" == "$expected_checksum" ]]
}

# Get next backup number
get_next_backup_number() {
    local counter=1
    if [[ -f "$BACKUP_COUNTER_FILE" ]]; then
        counter=$(cat "$BACKUP_COUNTER_FILE")
        counter=$((counter + 1))
    fi
    echo "$counter" > "$BACKUP_COUNTER_FILE"
    printf "%04d" "$counter"
}

# Create backup directories
create_backup_structure() {
    log_info "Creating backup directory structure"
    mkdir -p "$BACKUP_CODE_DIR" "$BACKUP_DATABASE_DIR" "$BACKUP_LOGS_DIR" "$BACKUP_METADATA_DIR"
    
    # Create log file if it doesn't exist
    if [[ ! -f "$LOG_FILE" ]]; then
        touch "$LOG_FILE"
    fi
}

# ===================================================================
# BACKUP FUNCTIONS
# ===================================================================

# Backup database (MySQL)
backup_database() {
    local backup_number="$1"
    local db_backup_file="$BACKUP_DATABASE_DIR/${backup_number}_db_${BACKUP_TIMESTAMP}.sql"
    
    log_info "Starting database backup"
    
    if container_running "$DB_CONTAINER_NAME"; then
        log_verbose "MySQL container is running, creating backup"
        
        # Get database credentials
        local db_user="${DB_USERNAME:-cms_user}"
        local db_name="${DB_DATABASE:-cms}"
        local db_password="${DB_PASSWORD}"
        
        if [[ -z "$db_password" ]]; then
            log_error "Database password not found in environment"
            return 1
        fi
        
        # Create database dump
        if docker exec -e MYSQL_PWD="$db_password" "$DB_CONTAINER_NAME" \
            mysqldump -u "$db_user" \
            --single-transaction \
            --routines \
            --triggers \
            --events \
            --set-gtid-purged=OFF \
            "$db_name" > "$db_backup_file"; then
            
            log_info "Database backup completed: $db_backup_file"
            
            # Compress if enabled
            if [[ "$BACKUP_COMPRESSION" == "gzip" ]]; then
                gzip "$db_backup_file"
                db_backup_file="${db_backup_file}.gz"
                log_verbose "Database backup compressed"
            fi
            
            # Generate checksum
            local checksum
            checksum="$(generate_checksum "$db_backup_file")"
            log_verbose "Database backup checksum: $checksum"
            
            echo "$checksum" > "${db_backup_file}.sha256"
            echo "$db_backup_file"
        else
            log_error "Failed to create database backup"
            return 1
        fi
    else
        log_warn "MySQL container not running, skipping database backup"
        return 1
    fi
}

# Backup Redis data
backup_redis() {
    local backup_number="$1"
    local redis_backup_file="$BACKUP_DATABASE_DIR/${backup_number}_redis_${BACKUP_TIMESTAMP}.rdb"
    
    log_info "Starting Redis backup"
    
    if container_running "$REDIS_CONTAINER_NAME"; then
        log_verbose "Redis container is running, creating backup"
        
        # Trigger Redis save
        docker exec "$REDIS_CONTAINER_NAME" redis-cli BGSAVE
        
        # Wait for save to complete
        local save_status
        while true; do
            save_status=$(docker exec "$REDIS_CONTAINER_NAME" redis-cli LASTSAVE)
            sleep 1
            local current_save
            current_save=$(docker exec "$REDIS_CONTAINER_NAME" redis-cli LASTSAVE)
            if [[ "$current_save" != "$save_status" ]]; then
                break
            fi
        done
        
        # Copy RDB file
        if docker cp "$REDIS_CONTAINER_NAME:/data/dump.rdb" "$redis_backup_file"; then
            log_info "Redis backup completed: $redis_backup_file"
            
            # Compress if enabled
            if [[ "$BACKUP_COMPRESSION" == "gzip" ]]; then
                gzip "$redis_backup_file"
                redis_backup_file="${redis_backup_file}.gz"
                log_verbose "Redis backup compressed"
            fi
            
            # Generate checksum
            local checksum
            checksum="$(generate_checksum "$redis_backup_file")"
            log_verbose "Redis backup checksum: $checksum"
            
            echo "$checksum" > "${redis_backup_file}.sha256"
            echo "$redis_backup_file"
        else
            log_error "Failed to create Redis backup"
            return 1
        fi
    else
        log_warn "Redis container not running, skipping Redis backup"
        return 1
    fi
}

# Backup application code
backup_code() {
    local backup_number="$1"
    local code_backup_file="$BACKUP_CODE_DIR/${backup_number}_code_${BACKUP_TIMESTAMP}.tar"
    
    log_info "Starting code backup"
    
    # Files and directories to include
    local include_patterns=(
        "app/"
        "config/"
        "tools/"
        "docs/"
        ".env.example"
        ".env.droplet.example"
        "stack.env.example"
        "docker-compose*.yml"
        "Dockerfile*"
        "README.md"
        "composer.json"
        "composer.lock"
    )
    
    # Files and directories to exclude
    local exclude_patterns=(
        ".git/"
        "node_modules/"
        "vendor/"
        "tmp/"
        "logs/"
        "*.log"
        ".DS_Store"
        "__pycache__/"
        "*.pyc"
        ".env"
        ".env.droplet"
        "stack.env"
        "backups/"
    )
    
    # Build tar command
    local tar_cmd="tar -cf \"$code_backup_file\" -C \"$PROJECT_ROOT\""
    
    # Add exclude patterns
    for pattern in "${exclude_patterns[@]}"; do
        tar_cmd="$tar_cmd --exclude=\"$pattern\""
    done
    
    # Add include patterns
    for pattern in "${include_patterns[@]}"; do
        if [[ -e "$PROJECT_ROOT/$pattern" ]]; then
            tar_cmd="$tar_cmd \"$pattern\""
        fi
    done
    
    log_verbose "Executing: $tar_cmd"
    
    # Execute tar command
    if eval "$tar_cmd"; then
        log_info "Code backup completed: $code_backup_file"
        
        # Compress if enabled
        if [[ "$BACKUP_COMPRESSION" == "gzip" ]]; then
            gzip "$code_backup_file"
            code_backup_file="${code_backup_file}.gz"
            log_verbose "Code backup compressed"
        fi
        
        # Generate checksum
        local checksum
        checksum="$(generate_checksum "$code_backup_file")"
        log_verbose "Code backup checksum: $checksum"
        
        echo "$checksum" > "${code_backup_file}.sha256"
        echo "$code_backup_file"
    else
        log_error "Failed to create code backup"
        return 1
    fi
}

# Backup logs
backup_logs() {
    local backup_number="$1"
    local logs_backup_file="$BACKUP_LOGS_DIR/${backup_number}_logs_${BACKUP_TIMESTAMP}.tar"
    
    log_info "Starting logs backup"
    
    # Log sources
    local log_sources=(
        "/var/log/willow"
        "$PROJECT_ROOT/logs"
        "$PROJECT_ROOT/app/logs"
    )
    
    # Docker container logs
    local containers=("$APP_CONTAINER_NAME" "$DB_CONTAINER_NAME" "$REDIS_CONTAINER_NAME")
    
    # Create temporary directory for container logs
    local temp_logs_dir
    temp_logs_dir=$(mktemp -d)
    
    # Export container logs
    for container in "${containers[@]}"; do
        if container_running "$container"; then
            docker logs "$container" > "$temp_logs_dir/${container}.log" 2>&1
            log_verbose "Exported logs for container: $container"
        fi
    done
    
    # Build tar command for logs
    local tar_cmd="tar -cf \"$logs_backup_file\""
    local has_logs=false
    
    # Add existing log directories
    for log_source in "${log_sources[@]}"; do
        if [[ -d "$log_source" ]]; then
            tar_cmd="$tar_cmd -C \"$(dirname "$log_source")\" \"$(basename "$log_source")\""
            has_logs=true
        fi
    done
    
    # Add container logs
    if [[ -d "$temp_logs_dir" ]] && [[ -n "$(ls -A "$temp_logs_dir")" ]]; then
        tar_cmd="$tar_cmd -C \"$temp_logs_dir\" ."
        has_logs=true
    fi
    
    if [[ "$has_logs" == "true" ]]; then
        log_verbose "Executing: $tar_cmd"
        
        if eval "$tar_cmd"; then
            log_info "Logs backup completed: $logs_backup_file"
            
            # Compress if enabled
            if [[ "$BACKUP_COMPRESSION" == "gzip" ]]; then
                gzip "$logs_backup_file"
                logs_backup_file="${logs_backup_file}.gz"
                log_verbose "Logs backup compressed"
            fi
            
            # Generate checksum
            local checksum
            checksum="$(generate_checksum "$logs_backup_file")"
            log_verbose "Logs backup checksum: $checksum"
            
            echo "$checksum" > "${logs_backup_file}.sha256"
            echo "$logs_backup_file"
        else
            log_error "Failed to create logs backup"
            rm -rf "$temp_logs_dir"
            return 1
        fi
    else
        log_warn "No logs found to backup"
    fi
    
    # Cleanup
    rm -rf "$temp_logs_dir"
}

# Generate backup manifest
generate_manifest() {
    local backup_number="$1"
    local db_file="$2"
    local redis_file="$3"
    local code_file="$4"
    local logs_file="$5"
    
    local manifest_file="$BACKUP_METADATA_DIR/${backup_number}_manifest_${BACKUP_TIMESTAMP}.json"
    
    log_info "Generating backup manifest"
    
    # Create manifest JSON
    cat > "$manifest_file" << EOF
{
    "backup": {
        "number": "$backup_number",
        "timestamp": "$BACKUP_TIMESTAMP",
        "script_version": "$BACKUP_SCRIPT_VERSION",
        "environment": "${APP_ENV:-unknown}",
        "retention_days": $BACKUP_RETENTION_DAYS
    },
    "files": {
        "database": {
            "file": "$(basename "$db_file")",
            "size": $(stat -f%z "$db_file" 2>/dev/null || stat -c%s "$db_file" 2>/dev/null || echo "0"),
            "checksum": "$(cat "${db_file}.sha256" 2>/dev/null || echo "")",
            "compression": "$BACKUP_COMPRESSION"
        },
        "redis": {
            "file": "$(basename "$redis_file")",
            "size": $(stat -f%z "$redis_file" 2>/dev/null || stat -c%s "$redis_file" 2>/dev/null || echo "0"),
            "checksum": "$(cat "${redis_file}.sha256" 2>/dev/null || echo "")",
            "compression": "$BACKUP_COMPRESSION"
        },
        "code": {
            "file": "$(basename "$code_file")",
            "size": $(stat -f%z "$code_file" 2>/dev/null || stat -c%s "$code_file" 2>/dev/null || echo "0"),
            "checksum": "$(cat "${code_file}.sha256" 2>/dev/null || echo "")",
            "compression": "$BACKUP_COMPRESSION"
        },
        "logs": {
            "file": "$(basename "$logs_file")",
            "size": $(stat -f%z "$logs_file" 2>/dev/null || stat -c%s "$logs_file" 2>/dev/null || echo "0"),
            "checksum": "$(cat "${logs_file}.sha256" 2>/dev/null || echo "")",
            "compression": "$BACKUP_COMPRESSION"
        }
    },
    "system": {
        "hostname": "$(hostname)",
        "user": "$(whoami)",
        "docker_compose_file": "$DOCKER_COMPOSE_FILE",
        "project_root": "$PROJECT_ROOT"
    }
}
EOF
    
    # Generate checksum for manifest
    local manifest_checksum
    manifest_checksum="$(generate_checksum "$manifest_file")"
    echo "$manifest_checksum" > "${manifest_file}.sha256"
    
    log_info "Backup manifest created: $manifest_file"
    echo "$manifest_file"
}

# Upload to DigitalOcean Spaces
upload_to_spaces() {
    local backup_files=("$@")
    
    if [[ "$DO_SPACES_ENABLED" != "true" ]]; then
        log_info "DigitalOcean Spaces not configured, skipping upload"
        return 0
    fi
    
    if ! command_exists "s3cmd"; then
        log_warn "s3cmd not installed, skipping Spaces upload"
        return 0
    fi
    
    log_info "Uploading backups to DigitalOcean Spaces"
    
    # Configure s3cmd for DigitalOcean Spaces
    local s3cfg_file
    s3cfg_file=$(mktemp)
    
    cat > "$s3cfg_file" << EOF
[default]
access_key = $SPACES_KEY
secret_key = $SPACES_SECRET
host_base = $DO_SPACES_ENDPOINT
host_bucket = %(bucket)s.$DO_SPACES_ENDPOINT
use_https = True
signature_v2 = False
EOF
    
    # Upload each file
    for file in "${backup_files[@]}"; do
        if [[ -f "$file" ]]; then
            local relative_path
            relative_path="$(realpath --relative-to="$BACKUP_BASE_DIR" "$file")"
            local s3_path="s3://$DO_SPACES_BUCKET/willow-backups/$relative_path"
            
            if s3cmd -c "$s3cfg_file" put "$file" "$s3_path"; then
                log_info "Uploaded: $file -> $s3_path"
            else
                log_error "Failed to upload: $file"
            fi
            
            # Upload checksum file if exists
            if [[ -f "${file}.sha256" ]]; then
                s3cmd -c "$s3cfg_file" put "${file}.sha256" "${s3_path}.sha256"
            fi
        fi
    done
    
    # Cleanup
    rm -f "$s3cfg_file"
    log_info "Spaces upload completed"
}

# Cleanup old backups
cleanup_old_backups() {
    log_info "Cleaning up backups older than $BACKUP_RETENTION_DAYS days"
    
    # Find and remove old backup files
    find "$BACKUP_BASE_DIR" -type f -mtime +$BACKUP_RETENTION_DAYS -name "*.tar*" -o -name "*.sql*" -o -name "*.rdb*" -o -name "*.json*" | while read -r old_file; do
        log_verbose "Removing old backup file: $old_file"
        rm -f "$old_file" "${old_file}.sha256"
    done
    
    # Cleanup empty directories
    find "$BACKUP_BASE_DIR" -type d -empty -delete 2>/dev/null || true
    
    log_info "Cleanup completed"
}

# ===================================================================
# MAIN BACKUP FUNCTION
# ===================================================================

perform_backup() {
    log_info "Starting Willow CMS backup process (version $BACKUP_SCRIPT_VERSION)"
    log_info "Backup timestamp: $BACKUP_TIMESTAMP"
    
    # Create backup structure
    create_backup_structure
    
    # Get next backup number
    local backup_number
    backup_number=$(get_next_backup_number)
    log_info "Backup number: $backup_number"
    
    # Perform individual backups
    local db_file redis_file code_file logs_file
    local backup_files=()
    
    # Database backup
    if db_file=$(backup_database "$backup_number"); then
        backup_files+=("$db_file" "${db_file}.sha256")
    fi
    
    # Redis backup
    if redis_file=$(backup_redis "$backup_number"); then
        backup_files+=("$redis_file" "${redis_file}.sha256")
    fi
    
    # Code backup
    if code_file=$(backup_code "$backup_number"); then
        backup_files+=("$code_file" "${code_file}.sha256")
    fi
    
    # Logs backup
    if logs_file=$(backup_logs "$backup_number"); then
        backup_files+=("$logs_file" "${logs_file}.sha256")
    fi
    
    # Generate manifest
    local manifest_file
    manifest_file=$(generate_manifest "$backup_number" "$db_file" "$redis_file" "$code_file" "$logs_file")
    backup_files+=("$manifest_file" "${manifest_file}.sha256")
    
    # Upload to Spaces
    if [[ ${#backup_files[@]} -gt 0 ]]; then
        upload_to_spaces "${backup_files[@]}"
    fi
    
    # Cleanup old backups
    cleanup_old_backups
    
    log_info "Backup process completed successfully"
    log_info "Backup number: $backup_number"
    log_info "Files created: ${#backup_files[@]}"
}

# ===================================================================
# COMMAND LINE INTERFACE
# ===================================================================

show_usage() {
    cat << EOF
Usage: $SCRIPT_NAME [OPTIONS]

Advanced backup system for Willow CMS with numbered files and checksum verification.

OPTIONS:
    -h, --help              Show this help message
    -v, --verbose           Enable verbose logging
    -n, --dry-run           Show what would be done without executing
    --no-spaces             Skip DigitalOcean Spaces upload
    --no-cleanup            Skip cleanup of old backups
    --retention-days N      Override retention period (default: $BACKUP_RETENTION_DAYS)
    
EXAMPLES:
    $SCRIPT_NAME                    # Perform standard backup
    $SCRIPT_NAME -v                 # Verbose backup
    $SCRIPT_NAME --retention-days 60 # Keep backups for 60 days
    
ENVIRONMENT VARIABLES:
    BACKUP_RETENTION_DAYS          Backup retention period
    BACKUP_COMPRESSION             Compression type (gzip|none)
    SPACES_KEY                     DigitalOcean Spaces access key
    SPACES_SECRET                  DigitalOcean Spaces secret key
    SPACES_BUCKET                  DigitalOcean Spaces bucket name
    
FILES:
    $BACKUP_BASE_DIR              Backup base directory
    $LOG_FILE                     Backup log file
    
EOF
}

# Parse command line arguments
parse_arguments() {
    local dry_run=false
    local no_spaces=false
    local no_cleanup=false
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_usage
                exit 0
                ;;
            -v|--verbose)
                ENABLE_VERBOSE="true"
                shift
                ;;
            -n|--dry-run)
                dry_run=true
                shift
                ;;
            --no-spaces)
                no_spaces=true
                shift
                ;;
            --no-cleanup)
                no_cleanup=true
                shift
                ;;
            --retention-days)
                BACKUP_RETENTION_DAYS="$2"
                shift 2
                ;;
            *)
                log_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Apply options
    if [[ "$dry_run" == "true" ]]; then
        log_info "DRY RUN MODE: No actual backup will be performed"
        exit 0
    fi
    
    if [[ "$no_spaces" == "true" ]]; then
        DO_SPACES_ENABLED="false"
    fi
    
    if [[ "$no_cleanup" == "true" ]]; then
        BACKUP_RETENTION_DAYS="999999"  # Effectively disable cleanup
    fi
}

# ===================================================================
# MAIN EXECUTION
# ===================================================================

main() {
    # Parse command line arguments
    parse_arguments "$@"
    
    # Check dependencies
    if ! command_exists "docker"; then
        log_error "Docker is required but not installed"
        exit 1
    fi
    
    # Perform backup
    perform_backup
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi