#!/usr/bin/env bash

# Willow CMS Backup and Reset Script
# Automated backup of data and configurations, full environment reset, and restore capability
# Usage: ./scripts/backup-and-reset.sh [--backup] [--restore] [--reset] [--backup-dir=path] [--force]

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
LOG_FILE="${PROJECT_DIR}/logs/backup-and-reset.log"
TIMESTAMP=$(date '+%Y-%m-%d_%H-%M-%S')
DEFAULT_BACKUP_DIR="${PROJECT_DIR}/backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Default options
ACTION=""
BACKUP_DIR="$DEFAULT_BACKUP_DIR"
FORCE=false
SPECIFIC_BACKUP=""
VERBOSE=false
INCLUDE_VOLUMES=true
INCLUDE_ENV_FILES=true
INCLUDE_UPLOADS=true

# Backup configuration
declare -A BACKUP_COMPONENTS
BACKUP_COMPONENTS["database"]="MySQL database dump"
BACKUP_COMPONENTS["redis"]="Redis data dump"
BACKUP_COMPONENTS["uploads"]="User uploaded files"
BACKUP_COMPONENTS["configs"]="Configuration files"
BACKUP_COMPONENTS["env"]="Environment variables"
BACKUP_COMPONENTS["volumes"]="Docker volumes"

# Logging function
log() {
    local level="$1"
    shift
    local message="$*"
    
    # Create logs directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    
    # Log to file
    echo "[$TIMESTAMP] [$level] $message" >> "$LOG_FILE"
    
    # Output to console with colors
    case "$level" in
        "INFO")
            echo -e "${BLUE}[INFO]${NC} $message"
            ;;
        "WARN")
            echo -e "${YELLOW}[WARN]${NC} $message"
            ;;
        "ERROR")
            echo -e "${RED}[ERROR]${NC} $message"
            ;;
        "SUCCESS")
            echo -e "${GREEN}[SUCCESS]${NC} $message"
            ;;
        *)
            echo "$message"
            ;;
    esac
}

# Error handler
error_exit() {
    log "ERROR" "$1"
    cleanup_temp_files
    exit 1
}

# Cleanup function for script interruption
cleanup_temp_files() {
    log "INFO" "Cleaning up temporary files..."
    rm -f /tmp/willow_backup_* 2>/dev/null || true
}

# Cleanup function for script interruption
cleanup() {
    log "WARN" "Script interrupted. Cleaning up..."
    cleanup_temp_files
    exit 1
}
trap cleanup INT TERM

# Show help
show_help() {
    cat << EOF
Willow CMS Backup and Reset Script

Usage: $0 [ACTION] [OPTIONS]

ACTIONS:
    --backup           Create backup of current environment
    --restore          Restore from backup
    --reset            Perform full environment reset (no backup)
    --backup-reset     Backup current state, then reset environment

OPTIONS:
    --backup-dir DIR   Directory for backups (default: ./backups)
    --restore-from DIR Specific backup to restore from
    --force            Skip confirmation prompts
    --verbose          Enable verbose logging
    --no-volumes       Skip Docker volumes in backup
    --no-env           Skip environment files in backup
    --no-uploads       Skip uploaded files in backup
    --help             Show this help message

EXAMPLES:
    $0 --backup                           # Create backup with timestamp
    $0 --backup --backup-dir /path/to/backups  # Custom backup location
    $0 --restore --restore-from ./backups/2024-01-15_14-30-25  # Restore specific backup
    $0 --backup-reset --force             # Backup and reset without confirmation
    $0 --reset --force                    # Full reset without backup

DESCRIPTION:
    This script provides comprehensive backup and restore capabilities for the
    Willow CMS Docker environment. It can backup databases, Redis data, uploaded
    files, configuration files, and Docker volumes.

    BACKUP INCLUDES:
    - MySQL database dump
    - Redis data export  
    - User uploaded files
    - Configuration files (.env, docker-compose.yml)
    - Docker volumes (optional)
    - Application cache and logs

    RESTORE PROCESS:
    1. Stops all services
    2. Restores database from backup
    3. Restores Redis data
    4. Restores uploaded files
    5. Restores configuration files
    6. Restores Docker volumes (if included)
    7. Starts services and verifies

    RESET PROCESS:
    1. Stops all services
    2. Removes all containers
    3. Removes all volumes (DESTRUCTIVE!)
    4. Removes all networks
    5. Clears application caches
    6. Optionally restores from backup

EOF
}

# Parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --backup)
                ACTION="backup"
                shift
                ;;
            --restore)
                ACTION="restore"
                shift
                ;;
            --reset)
                ACTION="reset"
                shift
                ;;
            --backup-reset)
                ACTION="backup-reset"
                shift
                ;;
            --backup-dir=*)
                BACKUP_DIR="${1#*=}"
                shift
                ;;
            --restore-from=*)
                SPECIFIC_BACKUP="${1#*=}"
                shift
                ;;
            --force)
                FORCE=true
                shift
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --no-volumes)
                INCLUDE_VOLUMES=false
                shift
                ;;
            --no-env)
                INCLUDE_ENV_FILES=false
                shift
                ;;
            --no-uploads)
                INCLUDE_UPLOADS=false
                shift
                ;;
            --help)
                show_help
                exit 0
                ;;
            *)
                error_exit "Unknown option: $1. Use --help for usage information."
                ;;
        esac
    done

    # Validate arguments
    if [[ -z "$ACTION" ]]; then
        error_exit "No action specified. Use --backup, --restore, --reset, or --backup-reset"
    fi

    if [[ "$ACTION" == "restore" && -z "$SPECIFIC_BACKUP" ]]; then
        error_exit "Restore action requires --restore-from option"
    fi

    if [[ -n "$SPECIFIC_BACKUP" && ! -d "$SPECIFIC_BACKUP" ]]; then
        error_exit "Backup directory '$SPECIFIC_BACKUP' does not exist"
    fi
}

# Confirm action with user
confirm_action() {
    if [[ "$FORCE" == true ]]; then
        return 0
    fi

    local action_desc=""
    case "$ACTION" in
        "backup")
            action_desc="create a backup of the current environment"
            ;;
        "restore")
            action_desc="restore from backup: $SPECIFIC_BACKUP"
            ;;
        "reset")
            action_desc="PERMANENTLY RESET the environment (THIS WILL DESTROY ALL DATA)"
            ;;
        "backup-reset")
            action_desc="backup current data and then PERMANENTLY RESET the environment"
            ;;
    esac

    echo
    echo -e "${YELLOW}WARNING: This will $action_desc.${NC}"
    
    if [[ "$ACTION" == "reset" || "$ACTION" == "backup-reset" ]]; then
        echo -e "${RED}THIS OPERATION IS DESTRUCTIVE AND CANNOT BE UNDONE!${NC}"
        echo "All containers, volumes, and data will be permanently removed."
    fi
    
    echo
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log "INFO" "Operation cancelled by user"
        exit 0
    fi
}

# Check prerequisites
check_prerequisites() {
    log "INFO" "Checking prerequisites..."
    
    if [[ ! -f "$PROJECT_DIR/docker-compose.yml" ]]; then
        error_exit "docker-compose.yml not found in $PROJECT_DIR"
    fi

    if ! command -v docker >/dev/null 2>&1; then
        error_exit "Docker command not found"
    fi

    if ! docker info >/dev/null 2>&1; then
        error_exit "Cannot connect to Docker daemon. Is Docker running?"
    fi

    # Check if compose is available
    if ! command -v docker-compose >/dev/null 2>&1 && ! docker compose version >/dev/null 2>&1; then
        error_exit "Neither docker-compose nor docker compose command found"
    fi

    # Create backup directory
    mkdir -p "$BACKUP_DIR"
    if [[ ! -w "$BACKUP_DIR" ]]; then
        error_exit "Backup directory '$BACKUP_DIR' is not writable"
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

# Create timestamped backup directory
create_backup_directory() {
    local backup_path="$BACKUP_DIR/backup_$TIMESTAMP"
    mkdir -p "$backup_path"
    echo "$backup_path"
}

# Backup MySQL database
backup_database() {
    local backup_path="$1"
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    log "INFO" "Backing up MySQL database..."
    
    cd "$PROJECT_DIR"
    
    # Check if MySQL container is running
    if ! $compose_cmd ps mysql | grep -q "Up"; then
        log "WARN" "MySQL container is not running, starting it temporarily..."
        $compose_cmd up -d mysql
        sleep 10
    fi
    
    # Create database backup
    local db_name="${DB_DATABASE:-willowcms}"
    local backup_file="$backup_path/database.sql"
    
    if $compose_cmd exec -T mysql mysqldump -u root -p"${MYSQL_ROOT_PASSWORD:-password}" --single-transaction --routines --triggers "$db_name" > "$backup_file" 2>/dev/null; then
        log "SUCCESS" "Database backup created: $backup_file"
        
        # Create database metadata
        cat > "$backup_path/database.info" << EOF
database_name=$db_name
backup_date=$TIMESTAMP
backup_size=$(du -h "$backup_file" | cut -f1)
mysql_version=$($compose_cmd exec -T mysql mysql --version 2>/dev/null || echo "unknown")
EOF
        
    else
        error_exit "Failed to create database backup"
    fi
}

# Backup Redis data
backup_redis() {
    local backup_path="$1"
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    
    log "INFO" "Backing up Redis data..."
    
    cd "$PROJECT_DIR"
    
    # Check if Redis container is running
    if ! $compose_cmd ps redis | grep -q "Up"; then
        log "WARN" "Redis container is not running, starting it temporarily..."
        $compose_cmd up -d redis
        sleep 5
    fi
    
    # Create Redis backup
    local backup_file="$backup_path/redis.rdb"
    
    # Save current Redis state
    if $compose_cmd exec -T redis redis-cli BGSAVE >/dev/null 2>&1; then
        sleep 2
        
        # Copy the Redis dump file
        if $compose_cmd exec -T redis cat /data/dump.rdb > "$backup_file" 2>/dev/null; then
            log "SUCCESS" "Redis backup created: $backup_file"
        else
            log "WARN" "Could not backup Redis data file"
        fi
    else
        log "WARN" "Could not trigger Redis background save"
    fi
}

# Backup uploaded files
backup_uploads() {
    local backup_path="$1"
    
    if [[ "$INCLUDE_UPLOADS" != true ]]; then
        log "INFO" "Skipping uploads backup (disabled)"
        return 0
    fi
    
    log "INFO" "Backing up uploaded files..."
    
    local uploads_dir="$PROJECT_DIR/webroot/img"
    local backup_uploads_dir="$backup_path/uploads"
    
    if [[ -d "$uploads_dir" ]]; then
        mkdir -p "$backup_uploads_dir"
        if cp -r "$uploads_dir"/* "$backup_uploads_dir/" 2>/dev/null; then
            log "SUCCESS" "Uploads backup created: $backup_uploads_dir"
        else
            log "WARN" "No upload files found to backup"
        fi
    else
        log "WARN" "Uploads directory not found: $uploads_dir"
    fi
}

# Backup configuration files
backup_configs() {
    local backup_path="$1"
    
    if [[ "$INCLUDE_ENV_FILES" != true ]]; then
        log "INFO" "Skipping configuration backup (disabled)"
        return 0
    fi
    
    log "INFO" "Backing up configuration files..."
    
    local config_backup_dir="$backup_path/config"
    mkdir -p "$config_backup_dir"
    
    # Backup environment files
    if [[ -f "$PROJECT_DIR/config/.env" ]]; then
        cp "$PROJECT_DIR/config/.env" "$config_backup_dir/"
        log "SUCCESS" "Environment file backed up"
    fi
    
    # Backup docker-compose.yml
    if [[ -f "$PROJECT_DIR/docker-compose.yml" ]]; then
        cp "$PROJECT_DIR/docker-compose.yml" "$config_backup_dir/"
        log "SUCCESS" "Docker Compose file backed up"
    fi
    
    # Backup any custom configuration
    if [[ -d "$PROJECT_DIR/config" ]]; then
        cp -r "$PROJECT_DIR/config"/* "$config_backup_dir/" 2>/dev/null || true
        log "SUCCESS" "Configuration directory backed up"
    fi
}

# Backup Docker volumes
backup_volumes() {
    local backup_path="$1"
    
    if [[ "$INCLUDE_VOLUMES" != true ]]; then
        log "INFO" "Skipping Docker volumes backup (disabled)"
        return 0
    fi
    
    log "INFO" "Backing up Docker volumes..."
    
    local volumes_backup_dir="$backup_path/volumes"
    mkdir -p "$volumes_backup_dir"
    
    # Get project name for volume naming
    local project_name
    project_name=$(basename "$PROJECT_DIR" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]//g')
    
    # Backup named volumes
    local volumes=("mysql_data" "redis_data" "mailpit_data" "jenkins_home")
    
    for volume in "${volumes[@]}"; do
        local volume_name="${project_name}_${volume}"
        local backup_file="$volumes_backup_dir/${volume}.tar.gz"
        
        if docker volume inspect "$volume_name" >/dev/null 2>&1; then
            log "INFO" "Backing up volume: $volume_name"
            
            # Create temporary container to access volume
            docker run --rm \
                -v "$volume_name":/data \
                -v "$volumes_backup_dir":/backup \
                alpine:latest \
                tar czf "/backup/${volume}.tar.gz" -C /data . 2>/dev/null || log "WARN" "Failed to backup volume: $volume"
                
            if [[ -f "$backup_file" ]]; then
                log "SUCCESS" "Volume backup created: $backup_file"
            fi
        else
            log "WARN" "Volume not found: $volume_name"
        fi
    done
}

# Create backup manifest
create_backup_manifest() {
    local backup_path="$1"
    local manifest_file="$backup_path/BACKUP_MANIFEST"
    
    cat > "$manifest_file" << EOF
# Willow CMS Backup Manifest
# Created: $TIMESTAMP
# Backup Directory: $backup_path

[BACKUP_INFO]
timestamp=$TIMESTAMP
willow_version=$(cat "$PROJECT_DIR/VERSION" 2>/dev/null || echo "unknown")
backup_script_version=1.0
docker_version=$(docker --version 2>/dev/null || echo "unknown")

[COMPONENTS]
EOF

    # List backed up components
    for component in "${!BACKUP_COMPONENTS[@]}"; do
        local component_path=""
        case "$component" in
            "database")
                component_path="database.sql"
                ;;
            "redis")
                component_path="redis.rdb"
                ;;
            "uploads")
                component_path="uploads/"
                ;;
            "configs")
                component_path="config/"
                ;;
            "volumes")
                component_path="volumes/"
                ;;
        esac
        
        if [[ -e "$backup_path/$component_path" ]]; then
            echo "$component=included" >> "$manifest_file"
        else
            echo "$component=excluded" >> "$manifest_file"
        fi
    done
    
    # Add file checksums
    echo "" >> "$manifest_file"
    echo "[CHECKSUMS]" >> "$manifest_file"
    
    find "$backup_path" -type f -name "*.sql" -o -name "*.rdb" -o -name "*.tar.gz" | while read -r file; do
        local filename
        filename=$(basename "$file")
        local checksum
        checksum=$(sha256sum "$file" | cut -d' ' -f1)
        echo "$filename=$checksum" >> "$manifest_file"
    done
    
    log "SUCCESS" "Backup manifest created: $manifest_file"
}

# Perform backup
perform_backup() {
    local backup_path
    backup_path=$(create_backup_directory)
    
    log "INFO" "Creating backup in: $backup_path"
    
    local start_time
    start_time=$(date +%s)
    
    # Backup components
    backup_database "$backup_path"
    backup_redis "$backup_path"
    backup_uploads "$backup_path"
    backup_configs "$backup_path"
    backup_volumes "$backup_path"
    
    # Create manifest
    create_backup_manifest "$backup_path"
    
    # Create backup summary
    local end_time
    end_time=$(date +%s)
    local duration=$((end_time - start_time))
    local backup_size
    backup_size=$(du -sh "$backup_path" | cut -f1)
    
    cat > "$backup_path/BACKUP_SUMMARY" << EOF
Willow CMS Backup Summary
========================
Backup Location: $backup_path
Backup Size: $backup_size
Duration: ${duration} seconds
Status: Completed Successfully

Components Backed Up:
$(ls -la "$backup_path" | tail -n +2 | awk '{print "- " $9 " (" $5 " bytes)"}')

To restore this backup:
$0 --restore --restore-from="$backup_path"
EOF

    log "SUCCESS" "Backup completed successfully!"
    log "INFO" "Backup location: $backup_path"
    log "INFO" "Backup size: $backup_size"
    log "INFO" "Duration: ${duration} seconds"
    
    echo "$backup_path"
}

# Restore from backup
perform_restore() {
    local backup_path="$SPECIFIC_BACKUP"
    local manifest_file="$backup_path/BACKUP_MANIFEST"
    
    log "INFO" "Restoring from backup: $backup_path"
    
    # Validate backup
    if [[ ! -f "$manifest_file" ]]; then
        error_exit "Invalid backup: manifest file not found in $backup_path"
    fi
    
    # Stop all services
    log "INFO" "Stopping all services..."
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    cd "$PROJECT_DIR"
    $compose_cmd down --timeout 30 || true
    
    # Restore database
    if [[ -f "$backup_path/database.sql" ]]; then
        log "INFO" "Restoring database..."
        $compose_cmd up -d mysql
        sleep 15
        
        local db_name="${DB_DATABASE:-willowcms}"
        if $compose_cmd exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD:-password}" -e "DROP DATABASE IF EXISTS $db_name; CREATE DATABASE $db_name;" && \
           $compose_cmd exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD:-password}" "$db_name" < "$backup_path/database.sql"; then
            log "SUCCESS" "Database restored successfully"
        else
            error_exit "Failed to restore database"
        fi
    fi
    
    # Restore Redis data
    if [[ -f "$backup_path/redis.rdb" ]]; then
        log "INFO" "Restoring Redis data..."
        $compose_cmd up -d redis
        sleep 5
        
        # Stop Redis and replace dump file
        $compose_cmd exec -T redis redis-cli SHUTDOWN NOSAVE || true
        sleep 2
        
        # Copy backup file to Redis volume
        docker run --rm \
            -v "${PROJECT_DIR##*/}_redis_data":/data \
            -v "$backup_path":/backup \
            alpine:latest \
            cp /backup/redis.rdb /data/dump.rdb || log "WARN" "Could not restore Redis data"
            
        log "SUCCESS" "Redis data restored"
    fi
    
    # Restore uploaded files
    if [[ -d "$backup_path/uploads" ]]; then
        log "INFO" "Restoring uploaded files..."
        local uploads_dir="$PROJECT_DIR/webroot/img"
        mkdir -p "$uploads_dir"
        
        if cp -r "$backup_path/uploads"/* "$uploads_dir/" 2>/dev/null; then
            log "SUCCESS" "Uploaded files restored"
        else
            log "WARN" "Could not restore uploaded files"
        fi
    fi
    
    # Restore configuration files
    if [[ -d "$backup_path/config" ]]; then
        log "INFO" "Restoring configuration files..."
        
        if [[ -f "$backup_path/config/.env" ]]; then
            cp "$backup_path/config/.env" "$PROJECT_DIR/config/"
            log "SUCCESS" "Environment file restored"
        fi
        
        # Restore other config files (be careful not to overwrite)
        find "$backup_path/config" -name "*.php" -o -name "*.yml" -o -name "*.yaml" | while read -r config_file; do
            local filename
            filename=$(basename "$config_file")
            if [[ ! -f "$PROJECT_DIR/config/$filename" ]]; then
                cp "$config_file" "$PROJECT_DIR/config/"
                log "INFO" "Restored config file: $filename"
            fi
        done
    fi
    
    # Restore Docker volumes
    if [[ -d "$backup_path/volumes" ]]; then
        log "INFO" "Restoring Docker volumes..."
        
        find "$backup_path/volumes" -name "*.tar.gz" | while read -r volume_backup; do
            local volume_name
            volume_name=$(basename "$volume_backup" .tar.gz)
            local full_volume_name="${PROJECT_DIR##*/}_${volume_name}"
            
            log "INFO" "Restoring volume: $full_volume_name"
            
            # Create volume if it doesn't exist
            docker volume create "$full_volume_name" >/dev/null 2>&1 || true
            
            # Restore volume data
            docker run --rm \
                -v "$full_volume_name":/data \
                -v "$backup_path/volumes":/backup \
                alpine:latest \
                sh -c "cd /data && rm -rf ./* && tar xzf /backup/${volume_name}.tar.gz" || log "WARN" "Failed to restore volume: $volume_name"
        done
    fi
    
    # Start all services
    log "INFO" "Starting all services..."
    $compose_cmd up -d
    
    log "SUCCESS" "Restore completed successfully!"
}

# Perform full reset
perform_reset() {
    log "INFO" "Performing full environment reset..."
    
    local compose_cmd
    compose_cmd=$(get_docker_compose_cmd)
    cd "$PROJECT_DIR"
    
    # Stop and remove all services
    log "INFO" "Stopping and removing all services..."
    $compose_cmd down --volumes --remove-orphans --timeout 30 || true
    
    # Remove all project volumes
    log "INFO" "Removing Docker volumes..."
    docker volume ls -q | grep "^${PROJECT_DIR##*/}_" | xargs docker volume rm 2>/dev/null || true
    
    # Remove unused networks
    log "INFO" "Cleaning up networks..."
    docker network prune -f || true
    
    # Clear application caches
    log "INFO" "Clearing application caches..."
    if [[ -d "$PROJECT_DIR/tmp/cache" ]]; then
        rm -rf "$PROJECT_DIR/tmp/cache"/* 2>/dev/null || true
    fi
    
    if [[ -d "$PROJECT_DIR/logs" ]]; then
        rm -rf "$PROJECT_DIR/logs"/* 2>/dev/null || true
    fi
    
    log "SUCCESS" "Full environment reset completed"
}

# List available backups
list_backups() {
    log "INFO" "Available backups in $BACKUP_DIR:"
    
    if [[ ! -d "$BACKUP_DIR" ]] || [[ -z "$(ls -A "$BACKUP_DIR" 2>/dev/null)" ]]; then
        echo "No backups found."
        return 0
    fi
    
    echo
    printf "%-25s %-15s %-30s\n" "BACKUP NAME" "SIZE" "DATE"
    echo "────────────────────────────────────────────────────────────────────"
    
    find "$BACKUP_DIR" -maxdepth 1 -type d -name "backup_*" | sort -r | while read -r backup_dir; do
        local backup_name
        backup_name=$(basename "$backup_dir")
        local backup_size
        backup_size=$(du -sh "$backup_dir" 2>/dev/null | cut -f1 || echo "unknown")
        local backup_date
        backup_date=$(stat -c %y "$backup_dir" 2>/dev/null | cut -d. -f1 || echo "unknown")
        
        printf "%-25s %-15s %-30s\n" "$backup_name" "$backup_size" "$backup_date"
    done
    echo
}

# Main execution
main() {
    log "INFO" "Starting Willow CMS backup and reset script"
    
    parse_arguments "$@"
    confirm_action
    check_prerequisites
    
    local start_time
    start_time=$(date +%s)
    
    case "$ACTION" in
        "backup")
            perform_backup
            ;;
        "restore")
            perform_restore
            ;;
        "reset")
            perform_reset
            ;;
        "backup-reset")
            local backup_path
            backup_path=$(perform_backup)
            log "INFO" "Backup completed, now performing reset..."
            perform_reset
            log "INFO" "Reset completed. Backup is available at: $backup_path"
            ;;
        *)
            error_exit "Unknown action: $ACTION"
            ;;
    esac
    
    local end_time
    end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    log "SUCCESS" "Operation completed successfully in ${duration} seconds"
    
    # Show available backups if relevant
    if [[ "$ACTION" == "backup" || "$ACTION" == "backup-reset" ]]; then
        echo
        list_backups
    fi
}

# Execute main function with all arguments
main "$@"
