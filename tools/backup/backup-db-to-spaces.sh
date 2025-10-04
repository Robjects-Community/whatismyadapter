#!/bin/bash

#
# Database Backup Script for DigitalOcean Spaces
# Compatible with WillowCMS backup system and numbering scheme
# 
# Usage: tools/backup/backup-db-to-spaces.sh
# Environment: Requires SPACES_* and DATABASE_URL environment variables
#

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BACKUP_DIR="${PROJECT_ROOT}/storage/backups/database"
LOG_FILE="${PROJECT_ROOT}/storage/logs/backup-$(date +%Y%m%d).log"

# Create necessary directories
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    log "ERROR: $1"
    exit 1
}

# Check required environment variables
check_env() {
    local missing_vars=()
    
    if [[ -z "${DATABASE_URL:-}" ]]; then
        missing_vars+=("DATABASE_URL")
    fi
    
    if [[ -z "${SPACES_KEY:-}" ]]; then
        missing_vars+=("SPACES_KEY")  
    fi
    
    if [[ -z "${SPACES_SECRET:-}" ]]; then
        missing_vars+=("SPACES_SECRET")
    fi
    
    if [[ -z "${SPACES_BUCKET:-}" ]]; then
        missing_vars+=("SPACES_BUCKET")
    fi
    
    if [[ -z "${SPACES_ENDPOINT:-}" ]]; then
        missing_vars+=("SPACES_ENDPOINT")
    fi
    
    if [[ ${#missing_vars[@]} -gt 0 ]]; then
        error_exit "Missing required environment variables: ${missing_vars[*]}"
    fi
}

# Parse DATABASE_URL for connection details
parse_database_url() {
    # Parse DATABASE_URL format: mysql://user:password@host:port/dbname
    if [[ ! "$DATABASE_URL" =~ mysql://([^:]+):([^@]+)@([^:]+):([0-9]+)/([^?]+) ]]; then
        error_exit "Invalid DATABASE_URL format. Expected: mysql://user:password@host:port/dbname"
    fi
    
    DB_USER="${BASH_REMATCH[1]}"
    DB_PASS="${BASH_REMATCH[2]}"
    DB_HOST="${BASH_REMATCH[3]}" 
    DB_PORT="${BASH_REMATCH[4]}"
    DB_NAME="${BASH_REMATCH[5]}"
    
    log "Database connection parsed: $DB_USER@$DB_HOST:$DB_PORT/$DB_NAME"
}

# Generate backup filename with numbering system (aligned with your preferences)
generate_backup_filename() {
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local env="${APP_ENV:-production}"
    
    # Find the next backup number for today
    local date_prefix=$(date +%Y%m%d)
    local existing_backups=($(find "$BACKUP_DIR" -name "${date_prefix}_*_${env}_*.sql.gz" 2>/dev/null | sort))
    local backup_number=$((${#existing_backups[@]} + 1))
    
    # Format with zero padding (001, 002, etc.)
    local backup_num=$(printf "%03d" "$backup_number")
    
    echo "${date_prefix}_${backup_num}_${env}_${DB_NAME}.sql.gz"
}

# Create database backup
create_backup() {
    local backup_file="$1"
    local backup_path="$BACKUP_DIR/$backup_file"
    
    log "Starting database backup: $backup_file"
    
    # Create MySQL dump with compression
    if ! mysqldump \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USER" \
        --password="$DB_PASS" \
        --single-transaction \
        --routines \
        --triggers \
        --quick \
        --lock-tables=false \
        --set-gtid-purged=OFF \
        "$DB_NAME" | gzip > "$backup_path"; then
        error_exit "Failed to create database backup"
    fi
    
    # Generate checksum for integrity verification (aligned with your log verification system)
    local checksum_file="${backup_path}.sha256"
    if ! sha256sum "$backup_path" > "$checksum_file"; then
        error_exit "Failed to generate backup checksum"
    fi
    
    # Get file size for metadata
    local file_size=$(du -h "$backup_path" | cut -f1)
    
    log "Backup created successfully: $backup_file ($file_size)"
    log "Checksum generated: $(basename "$checksum_file")"
    
    echo "$backup_path"
}

# Upload to DigitalOcean Spaces using AWS CLI compatible interface
upload_to_spaces() {
    local backup_path="$1"
    local checksum_path="${backup_path}.sha256"
    local backup_file=$(basename "$backup_path")
    local checksum_file=$(basename "$checksum_path")
    
    # Spaces path with organized folder structure
    local env="${APP_ENV:-production}"
    local date_folder=$(date +%Y/%m)
    local spaces_prefix="backups/database/$env/$date_folder"
    
    log "Uploading backup to Spaces: s3://$SPACES_BUCKET/$spaces_prefix/"
    
    # Configure AWS CLI for Spaces
    export AWS_ACCESS_KEY_ID="$SPACES_KEY"
    export AWS_SECRET_ACCESS_KEY="$SPACES_SECRET"
    export AWS_DEFAULT_REGION="${SPACES_REGION:-nyc3}"
    
    # Upload backup file
    if ! aws s3 cp "$backup_path" \
        "s3://$SPACES_BUCKET/$spaces_prefix/$backup_file" \
        --endpoint-url="https://$SPACES_ENDPOINT" \
        --metadata="environment=$env,created=$(date -Iseconds),database=$DB_NAME"; then
        error_exit "Failed to upload backup file to Spaces"
    fi
    
    # Upload checksum file
    if ! aws s3 cp "$checksum_path" \
        "s3://$SPACES_BUCKET/$spaces_prefix/$checksum_file" \
        --endpoint-url="https://$SPACES_ENDPOINT" \
        --metadata="environment=$env,created=$(date -Iseconds)"; then
        error_exit "Failed to upload checksum file to Spaces"
    fi
    
    log "Upload completed successfully"
    log "Backup URL: https://$SPACES_BUCKET.$SPACES_ENDPOINT/$spaces_prefix/$backup_file"
}

# Cleanup old local backups (keep last 3 days locally)
cleanup_local_backups() {
    log "Cleaning up old local backups (keeping last 3 days)"
    
    if find "$BACKUP_DIR" -name "*.sql.gz" -type f -mtime +3 -exec rm -f {} \; -exec rm -f {}.sha256 \;; then
        log "Local backup cleanup completed"
    else
        log "WARNING: Local backup cleanup failed"
    fi
}

# Generate backup metadata file
generate_metadata() {
    local backup_file="$1"
    local backup_path="$BACKUP_DIR/$backup_file"
    local metadata_file="${backup_path}.meta"
    
    # Create metadata file with backup information
    cat > "$metadata_file" << EOF
{
    "backup_file": "$backup_file",
    "database": "$DB_NAME",
    "environment": "${APP_ENV:-production}",
    "created_at": "$(date -Iseconds)",
    "created_by": "$(whoami)",
    "host": "$DB_HOST",
    "size": "$(du -h "$backup_path" | cut -f1)",
    "checksum": "$(cat "${backup_path}.sha256" | cut -d' ' -f1)",
    "checksum_algorithm": "sha256",
    "compression": "gzip",
    "backup_type": "full",
    "app_version": "$(git rev-parse HEAD 2>/dev/null || echo 'unknown')"
}
EOF
    
    log "Metadata file generated: $(basename "$metadata_file")"
}

# Main execution
main() {
    log "=== WillowCMS Database Backup Started ==="
    log "Environment: ${APP_ENV:-production}"
    
    # Pre-flight checks
    check_env
    parse_database_url
    
    # Generate backup filename with numbering
    local backup_filename
    backup_filename=$(generate_backup_filename)
    
    # Create the backup
    local backup_path
    backup_path=$(create_backup "$backup_filename")
    
    # Generate metadata
    generate_metadata "$backup_filename"
    
    # Upload to Spaces
    upload_to_spaces "$backup_path"
    
    # Cleanup old local files
    cleanup_local_backups
    
    log "=== Database Backup Completed Successfully ==="
    log "Backup file: $backup_filename"
    log "Total time: $SECONDS seconds"
}

# Execute main function
main "$@"