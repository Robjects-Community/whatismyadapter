#!/bin/bash
# ===================================================================
# WILLOW CMS RESTORE SYSTEM  
# ===================================================================
# Advanced restore functionality with:
# - Interactive backup selection with metadata
# - Checksum verification before restore
# - Dry-run capability  
# - Selective restore (database, code, etc.)
# - Compatibility with numbered backup system
# - Safety checks and rollback options
# ===================================================================

set -euo pipefail

# ===================================================================
# CONFIGURATION AND ENVIRONMENT VARIABLES
# ===================================================================

RESTORE_SCRIPT_VERSION="2.1.0"
SCRIPT_NAME="$(basename "$0")"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Load environment variables
if [[ -f "$PROJECT_ROOT/.env" ]]; then
    source "$PROJECT_ROOT/.env"
fi

if [[ -f "$PROJECT_ROOT/.env.droplet" ]]; then
    source "$PROJECT_ROOT/.env.droplet"
fi

if [[ -f "$PROJECT_ROOT/stack.env" ]]; then
    source "$PROJECT_ROOT/stack.env"
fi

# Restore configuration
BACKUP_BASE_DIR="${HOST_BACKUP_PATH:-$PROJECT_ROOT/backups}"
BACKUP_CODE_DIR="$BACKUP_BASE_DIR/code"
BACKUP_DATABASE_DIR="$BACKUP_BASE_DIR/database"
BACKUP_LOGS_DIR="$BACKUP_BASE_DIR/logs"
BACKUP_METADATA_DIR="$BACKUP_BASE_DIR/metadata"

# Docker settings
DB_CONTAINER_NAME="${DB_CONTAINER_NAME:-willowcms-mysql}"
REDIS_CONTAINER_NAME="${REDIS_CONTAINER_NAME:-willowcms-redis}"
APP_CONTAINER_NAME="${APP_CONTAINER_NAME:-willowcms-app}"

# Logging
LOG_FILE="$BACKUP_BASE_DIR/restore.log"
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

# Format file size
format_size() {
    local size="$1"
    if [[ "$size" -gt 1073741824 ]]; then
        echo "$(( size / 1073741824 ))GB"
    elif [[ "$size" -gt 1048576 ]]; then
        echo "$(( size / 1048576 ))MB"
    elif [[ "$size" -gt 1024 ]]; then
        echo "$(( size / 1024 ))KB"
    else
        echo "${size}B"
    fi
}

# ===================================================================
# BACKUP DISCOVERY AND LISTING FUNCTIONS
# ===================================================================

# List available backups with metadata
list_backups() {
    local format="${1:-table}"
    
    log_info "Scanning for available backups"
    
    # Find all manifest files
    local manifest_files=()
    while IFS= read -r -d '' manifest; do
        manifest_files+=("$manifest")
    done < <(find "$BACKUP_METADATA_DIR" -name "*_manifest_*.json" -print0 2>/dev/null | sort -z)
    
    if [[ ${#manifest_files[@]} -eq 0 ]]; then
        log_warn "No backups found in $BACKUP_BASE_DIR"
        return 1
    fi
    
    # Parse and display backups
    case "$format" in
        table)
            printf "%-6s %-20s %-12s %-15s %-10s %-10s %-10s %-10s\n" \
                "Num" "Timestamp" "Environment" "Database" "Redis" "Code" "Logs" "Total"
            printf "%-6s %-20s %-12s %-15s %-10s %-10s %-10s %-10s\n" \
                "---" "-------------------" "-----------" "--------------" "---------" "---------" "---------" "---------"
            ;;
        json)
            echo "["
            ;;
        simple)
            # Simple format for scripting
            ;;
    esac
    
    local first_item=true
    for manifest_file in "${manifest_files[@]}"; do
        if [[ -f "$manifest_file" ]]; then
            local backup_info
            backup_info=$(parse_backup_manifest "$manifest_file" "$format")
            
            case "$format" in
                table|simple)
                    echo "$backup_info"
                    ;;
                json)
                    if [[ "$first_item" != "true" ]]; then
                        echo ","
                    fi
                    echo "$backup_info"
                    first_item=false
                    ;;
            esac
        fi
    done
    
    if [[ "$format" == "json" ]]; then
        echo "]"
    fi
}

# Parse individual backup manifest
parse_backup_manifest() {
    local manifest_file="$1"
    local format="$2"
    
    # Extract data using jq if available, otherwise use sed/grep
    if command_exists "jq"; then
        local number timestamp environment
        local db_size redis_size code_size logs_size total_size
        local db_file redis_file code_file logs_file
        
        number=$(jq -r '.backup.number // "unknown"' "$manifest_file")
        timestamp=$(jq -r '.backup.timestamp // "unknown"' "$manifest_file")
        environment=$(jq -r '.backup.environment // "unknown"' "$manifest_file")
        
        db_size=$(jq -r '.files.database.size // 0' "$manifest_file")
        redis_size=$(jq -r '.files.redis.size // 0' "$manifest_file")
        code_size=$(jq -r '.files.code.size // 0' "$manifest_file")
        logs_size=$(jq -r '.files.logs.size // 0' "$manifest_file")
        
        db_file=$(jq -r '.files.database.file // "N/A"' "$manifest_file")
        redis_file=$(jq -r '.files.redis.file // "N/A"' "$manifest_file")
        code_file=$(jq -r '.files.code.file // "N/A"' "$manifest_file")
        logs_file=$(jq -r '.files.logs.file // "N/A"' "$manifest_file")
        
        total_size=$((db_size + redis_size + code_size + logs_size))
        
        case "$format" in
            table)
                printf "%-6s %-20s %-12s %-15s %-10s %-10s %-10s %-10s\n" \
                    "$number" \
                    "$(echo "$timestamp" | sed 's/_/ /')" \
                    "$environment" \
                    "$(format_size "$db_size")" \
                    "$(format_size "$redis_size")" \
                    "$(format_size "$code_size")" \
                    "$(format_size "$logs_size")" \
                    "$(format_size "$total_size")"
                ;;
            json)
                jq -c ". + {\"manifest_file\": \"$manifest_file\", \"total_size\": $total_size}" "$manifest_file"
                ;;
            simple)
                echo "$number|$timestamp|$environment|$total_size|$manifest_file"
                ;;
        esac
    else
        # Fallback parsing without jq
        local number timestamp environment
        number=$(grep -o '"number": "[^"]*"' "$manifest_file" | sed 's/"number": "\([^"]*\)"/\1/')
        timestamp=$(grep -o '"timestamp": "[^"]*"' "$manifest_file" | sed 's/"timestamp": "\([^"]*\)"/\1/')
        environment=$(grep -o '"environment": "[^"]*"' "$manifest_file" | sed 's/"environment": "\([^"]*\)"/\1/')
        
        case "$format" in
            table)
                printf "%-6s %-20s %-12s %-15s %-10s %-10s %-10s %-10s\n" \
                    "${number:-unknown}" \
                    "$(echo "${timestamp:-unknown}" | sed 's/_/ /')" \
                    "${environment:-unknown}" \
                    "Unknown" "Unknown" "Unknown" "Unknown" "Unknown"
                ;;
            simple)
                echo "${number:-unknown}|${timestamp:-unknown}|${environment:-unknown}|0|$manifest_file"
                ;;
        esac
    fi
}

# Select backup interactively
select_backup() {
    local backup_list
    backup_list=$(list_backups simple)
    
    if [[ -z "$backup_list" ]]; then
        log_error "No backups available"
        return 1
    fi
    
    echo "Available backups:"
    echo
    list_backups table
    echo
    
    local backup_count
    backup_count=$(echo "$backup_list" | wc -l)
    
    echo "Enter backup number to restore (1-$backup_count) or 'q' to quit:"
    read -r selection
    
    if [[ "$selection" == "q" ]]; then
        log_info "Restore cancelled by user"
        exit 0
    fi
    
    if [[ ! "$selection" =~ ^[0-9]+$ ]] || [[ "$selection" -lt 1 ]] || [[ "$selection" -gt "$backup_count" ]]; then
        log_error "Invalid selection: $selection"
        return 1
    fi
    
    # Get the selected backup manifest file
    local selected_line
    selected_line=$(echo "$backup_list" | sed -n "${selection}p")
    local manifest_file
    manifest_file=$(echo "$selected_line" | cut -d'|' -f5)
    
    echo "$manifest_file"
}

# ===================================================================
# RESTORE FUNCTIONS
# ===================================================================

# Restore database
restore_database() {
    local manifest_file="$1"
    local dry_run="$2"
    
    log_info "Starting database restore"
    
    if ! command_exists "jq"; then
        log_error "jq is required for database restore but not installed"
        return 1
    fi
    
    local db_file checksum
    db_file=$(jq -r '.files.database.file' "$manifest_file")
    checksum=$(jq -r '.files.database.checksum' "$manifest_file")
    
    if [[ "$db_file" == "null" ]] || [[ -z "$db_file" ]]; then
        log_warn "No database backup found in manifest"
        return 1
    fi
    
    local db_backup_path="$BACKUP_DATABASE_DIR/$db_file"
    
    if [[ ! -f "$db_backup_path" ]]; then
        log_error "Database backup file not found: $db_backup_path"
        return 1
    fi
    
    # Verify checksum
    if [[ -n "$checksum" ]] && [[ "$checksum" != "null" ]]; then
        log_verbose "Verifying database backup checksum"
        if ! verify_checksum "$db_backup_path" "$checksum"; then
            log_error "Database backup checksum verification failed"
            return 1
        fi
        log_info "Database backup checksum verified"
    fi
    
    if [[ "$dry_run" == "true" ]]; then
        log_info "DRY RUN: Would restore database from $db_backup_path"
        return 0
    fi
    
    # Check if database container is running
    if ! container_running "$DB_CONTAINER_NAME"; then
        log_error "Database container $DB_CONTAINER_NAME is not running"
        return 1
    fi
    
    # Get database credentials
    local db_user="${DB_USERNAME:-cms_user}"
    local db_name="${DB_DATABASE:-cms}"
    local db_password="${DB_PASSWORD}"
    
    if [[ -z "$db_password" ]]; then
        log_error "Database password not found in environment"
        return 1
    fi
    
    # Create backup of current database before restore
    local current_backup="$BACKUP_DATABASE_DIR/pre_restore_$(date +%Y%m%d_%H%M%S).sql"
    log_info "Creating backup of current database before restore"
    
    if docker exec -e MYSQL_PWD="$db_password" "$DB_CONTAINER_NAME" \
        mysqldump -u "$db_user" "$db_name" > "$current_backup"; then
        log_info "Current database backed up to: $current_backup"
    else
        log_warn "Failed to create current database backup, continuing with restore"
    fi
    
    # Restore database
    local restore_file="$db_backup_path"
    
    # Handle compressed files
    if [[ "$db_backup_path" == *.gz ]]; then
        log_verbose "Decompressing database backup"
        restore_file=$(mktemp)
        gunzip -c "$db_backup_path" > "$restore_file"
    fi
    
    log_info "Restoring database from $db_backup_path"
    
    if docker exec -i -e MYSQL_PWD="$db_password" "$DB_CONTAINER_NAME" \
        mysql -u "$db_user" "$db_name" < "$restore_file"; then
        log_info "Database restore completed successfully"
        
        # Cleanup temporary file if created
        if [[ "$restore_file" != "$db_backup_path" ]]; then
            rm -f "$restore_file"
        fi
        
        return 0
    else
        log_error "Database restore failed"
        
        # Cleanup temporary file if created
        if [[ "$restore_file" != "$db_backup_path" ]]; then
            rm -f "$restore_file"
        fi
        
        # Attempt to restore from pre-restore backup
        if [[ -f "$current_backup" ]]; then
            log_info "Attempting to restore from pre-restore backup"
            docker exec -i -e MYSQL_PWD="$db_password" "$DB_CONTAINER_NAME" \
                mysql -u "$db_user" "$db_name" < "$current_backup" || true
        fi
        
        return 1
    fi
}

# Restore Redis
restore_redis() {
    local manifest_file="$1"
    local dry_run="$2"
    
    log_info "Starting Redis restore"
    
    if ! command_exists "jq"; then
        log_error "jq is required for Redis restore but not installed"
        return 1
    fi
    
    local redis_file checksum
    redis_file=$(jq -r '.files.redis.file' "$manifest_file")
    checksum=$(jq -r '.files.redis.checksum' "$manifest_file")
    
    if [[ "$redis_file" == "null" ]] || [[ -z "$redis_file" ]]; then
        log_warn "No Redis backup found in manifest"
        return 1
    fi
    
    local redis_backup_path="$BACKUP_DATABASE_DIR/$redis_file"
    
    if [[ ! -f "$redis_backup_path" ]]; then
        log_error "Redis backup file not found: $redis_backup_path"
        return 1
    fi
    
    # Verify checksum
    if [[ -n "$checksum" ]] && [[ "$checksum" != "null" ]]; then
        log_verbose "Verifying Redis backup checksum"
        if ! verify_checksum "$redis_backup_path" "$checksum"; then
            log_error "Redis backup checksum verification failed"
            return 1
        fi
        log_info "Redis backup checksum verified"
    fi
    
    if [[ "$dry_run" == "true" ]]; then
        log_info "DRY RUN: Would restore Redis from $redis_backup_path"
        return 0
    fi
    
    # Check if Redis container is running
    if ! container_running "$REDIS_CONTAINER_NAME"; then
        log_error "Redis container $REDIS_CONTAINER_NAME is not running"
        return 1
    fi
    
    # Handle compressed files
    local restore_file="$redis_backup_path"
    if [[ "$redis_backup_path" == *.gz ]]; then
        log_verbose "Decompressing Redis backup"
        restore_file=$(mktemp)
        gunzip -c "$redis_backup_path" > "$restore_file"
    fi
    
    log_info "Restoring Redis from $redis_backup_path"
    
    # Stop Redis, replace RDB file, start Redis
    if docker exec "$REDIS_CONTAINER_NAME" redis-cli SHUTDOWN NOSAVE; then
        log_verbose "Redis shutdown successfully"
        sleep 2
    fi
    
    # Copy backup to Redis container
    if docker cp "$restore_file" "$REDIS_CONTAINER_NAME:/data/dump.rdb"; then
        log_info "Redis backup file copied to container"
        
        # Restart Redis container
        if docker restart "$REDIS_CONTAINER_NAME"; then
            log_info "Redis restore completed successfully"
            
            # Cleanup temporary file if created
            if [[ "$restore_file" != "$redis_backup_path" ]]; then
                rm -f "$restore_file"
            fi
            
            return 0
        else
            log_error "Failed to restart Redis container"
        fi
    else
        log_error "Failed to copy Redis backup to container"
    fi
    
    # Cleanup temporary file if created
    if [[ "$restore_file" != "$redis_backup_path" ]]; then
        rm -f "$restore_file"
    fi
    
    return 1
}

# Restore application code
restore_code() {
    local manifest_file="$1"
    local dry_run="$2"
    
    log_info "Starting code restore"
    
    if ! command_exists "jq"; then
        log_error "jq is required for code restore but not installed"
        return 1
    fi
    
    local code_file checksum
    code_file=$(jq -r '.files.code.file' "$manifest_file")
    checksum=$(jq -r '.files.code.checksum' "$manifest_file")
    
    if [[ "$code_file" == "null" ]] || [[ -z "$code_file" ]]; then
        log_warn "No code backup found in manifest"
        return 1
    fi
    
    local code_backup_path="$BACKUP_CODE_DIR/$code_file"
    
    if [[ ! -f "$code_backup_path" ]]; then
        log_error "Code backup file not found: $code_backup_path"
        return 1
    fi
    
    # Verify checksum
    if [[ -n "$checksum" ]] && [[ "$checksum" != "null" ]]; then
        log_verbose "Verifying code backup checksum"
        if ! verify_checksum "$code_backup_path" "$checksum"; then
            log_error "Code backup checksum verification failed"
            return 1
        fi
        log_info "Code backup checksum verified"
    fi
    
    if [[ "$dry_run" == "true" ]]; then
        log_info "DRY RUN: Would restore code from $code_backup_path"
        return 0
    fi
    
    # Create backup of current code
    local current_code_backup="$BACKUP_CODE_DIR/pre_restore_code_$(date +%Y%m%d_%H%M%S).tar.gz"
    log_info "Creating backup of current code before restore"
    
    tar -czf "$current_code_backup" -C "$PROJECT_ROOT" \
        --exclude="backups/" \
        --exclude=".git/" \
        --exclude="logs/" \
        --exclude="tmp/" \
        . 2>/dev/null || log_warn "Failed to create current code backup"
    
    # Extract code backup
    local restore_file="$code_backup_path"
    local temp_extract_dir
    
    # Handle compressed files
    if [[ "$code_backup_path" == *.gz ]]; then
        temp_extract_dir=$(mktemp -d)
        log_verbose "Extracting compressed code backup to temporary directory"
        tar -xzf "$code_backup_path" -C "$temp_extract_dir"
    else
        temp_extract_dir=$(mktemp -d)
        tar -xf "$code_backup_path" -C "$temp_extract_dir"
    fi
    
    log_info "Restoring code from $code_backup_path"
    
    # Copy restored files to project root
    if cp -r "$temp_extract_dir"/* "$PROJECT_ROOT/"; then
        log_info "Code restore completed successfully"
        rm -rf "$temp_extract_dir"
        return 0
    else
        log_error "Code restore failed"
        rm -rf "$temp_extract_dir"
        return 1
    fi
}

# ===================================================================
# MAIN RESTORE FUNCTIONS
# ===================================================================

# Perform full restore
perform_restore() {
    local manifest_file="$1"
    local dry_run="$2"
    local components="$3"  # all,database,redis,code,logs
    
    log_info "Starting Willow CMS restore process (version $RESTORE_SCRIPT_VERSION)"
    
    if [[ ! -f "$manifest_file" ]]; then
        log_error "Manifest file not found: $manifest_file"
        return 1
    fi
    
    # Verify manifest checksum
    if [[ -f "${manifest_file}.sha256" ]]; then
        local manifest_checksum
        manifest_checksum=$(cat "${manifest_file}.sha256")
        if ! verify_checksum "$manifest_file" "$manifest_checksum"; then
            log_error "Manifest file checksum verification failed"
            return 1
        fi
        log_info "Manifest checksum verified"
    fi
    
    local restore_success=true
    
    # Database restore
    if [[ "$components" == "all" ]] || [[ "$components" == *"database"* ]]; then
        if ! restore_database "$manifest_file" "$dry_run"; then
            restore_success=false
            log_error "Database restore failed"
        fi
    fi
    
    # Redis restore
    if [[ "$components" == "all" ]] || [[ "$components" == *"redis"* ]]; then
        if ! restore_redis "$manifest_file" "$dry_run"; then
            restore_success=false
            log_error "Redis restore failed"
        fi
    fi
    
    # Code restore
    if [[ "$components" == "all" ]] || [[ "$components" == *"code"* ]]; then
        if ! restore_code "$manifest_file" "$dry_run"; then
            restore_success=false
            log_error "Code restore failed"
        fi
    fi
    
    if [[ "$restore_success" == "true" ]]; then
        log_info "Restore process completed successfully"
        if [[ "$dry_run" != "true" ]]; then
            log_info "Consider restarting application containers to ensure all changes take effect"
        fi
        return 0
    else
        log_error "Restore process completed with errors"
        return 1
    fi
}

# ===================================================================
# COMMAND LINE INTERFACE
# ===================================================================

show_usage() {
    cat << EOF
Usage: $SCRIPT_NAME [OPTIONS]

Advanced restore system for Willow CMS with backup selection and verification.

OPTIONS:
    -h, --help              Show this help message
    -v, --verbose           Enable verbose logging  
    -n, --dry-run           Show what would be done without executing
    -l, --list              List available backups and exit
    -f, --format FORMAT     Output format for listing (table|json|simple)
    -b, --backup NUM        Restore specific backup number
    -c, --components COMP   Restore specific components (all|database|redis|code|logs)
    -i, --interactive       Interactive backup selection (default)
    
EXAMPLES:
    $SCRIPT_NAME                            # Interactive backup selection
    $SCRIPT_NAME -l                         # List available backups
    $SCRIPT_NAME -b 5 -c database           # Restore database from backup #5
    $SCRIPT_NAME -n -c all                  # Dry run full restore
    $SCRIPT_NAME --list --format json       # List backups in JSON format
    
COMPONENTS:
    all                     Restore everything (default)
    database                Restore MySQL database only
    redis                   Restore Redis data only  
    code                    Restore application code only
    logs                    Restore logs only
    database,code           Restore multiple components
    
FILES:
    $BACKUP_BASE_DIR       Backup base directory
    $LOG_FILE              Restore log file
    
EOF
}

# Parse command line arguments
parse_arguments() {
    local list_only=false
    local interactive=true
    local dry_run=false
    local format="table"
    local backup_number=""
    local components="all"
    
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
            -l|--list)
                list_only=true
                interactive=false
                shift
                ;;
            -f|--format)
                format="$2"
                shift 2
                ;;
            -b|--backup)
                backup_number="$2"
                interactive=false
                shift 2
                ;;
            -c|--components)
                components="$2"
                shift 2
                ;;
            -i|--interactive)
                interactive=true
                shift
                ;;
            *)
                log_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Execute based on options
    if [[ "$list_only" == "true" ]]; then
        list_backups "$format"
        exit 0
    fi
    
    local manifest_file
    
    if [[ "$interactive" == "true" ]]; then
        manifest_file=$(select_backup)
        if [[ $? -ne 0 ]]; then
            log_error "Failed to select backup"
            exit 1
        fi
    elif [[ -n "$backup_number" ]]; then
        # Find manifest for specific backup number
        manifest_file=$(find "$BACKUP_METADATA_DIR" -name "${backup_number}_manifest_*.json" | head -1)
        if [[ -z "$manifest_file" ]]; then
            log_error "Backup number $backup_number not found"
            exit 1
        fi
    else
        # Use latest backup
        manifest_file=$(find "$BACKUP_METADATA_DIR" -name "*_manifest_*.json" | sort | tail -1)
        if [[ -z "$manifest_file" ]]; then
            log_error "No backups found"
            exit 1
        fi
        log_info "Using latest backup: $(basename "$manifest_file")"
    fi
    
    # Perform restore
    perform_restore "$manifest_file" "$dry_run" "$components"
}

# ===================================================================
# MAIN EXECUTION
# ===================================================================

main() {
    # Check dependencies
    if ! command_exists "docker"; then
        log_error "Docker is required but not installed"
        exit 1
    fi
    
    # Create log file if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    touch "$LOG_FILE"
    
    # Parse arguments and execute
    parse_arguments "$@"
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi