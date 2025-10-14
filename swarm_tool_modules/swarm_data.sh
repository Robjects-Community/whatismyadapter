#!/usr/bin/env bash
# swarm_data.sh - Data management operations: database, files, backups, and log checksums

# === Configuration ===

BACKUP_DIR="${BACKUP_DIR:-${PROJECT_ROOT}/backups}"
mkdir -p "${BACKUP_DIR}"

# Database defaults - can be overridden by environment
MYSQL_HOST="${MYSQL_HOST:-mysql}"
MYSQL_DATABASE="${MYSQL_DATABASE:-cms}"
MYSQL_USER="${MYSQL_USER:-cms_user}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-password}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-password}"

# === Database Operations ===

db_backup() {
    local ts; ts="$(date '+%Y%m%d-%H%M%S')"
    local file="${BACKUP_DIR}/db_${MYSQL_DATABASE}_${ts}.sql.gz"
    local cid; cid="$(svc_pick_container mysql)" || true
    
    if [ -z "$cid" ]; then
        die "MySQL service container not found locally. Ensure mysql service runs on this node or set appropriate DOCKER_CONTEXT."
    fi
    
    log "Creating database backup: ${file}"
    debug "Dumping database ${MYSQL_DATABASE} as user ${MYSQL_USER}"
    
    # Use the container's environment variables for consistency
    if docker exec "$cid" sh -c "mysqldump -u\"\${MYSQL_USER:-root}\" -p\"\${MYSQL_PASSWORD:-\${MYSQL_ROOT_PASSWORD}}\" \"\${MYSQL_DATABASE}\"" | gzip > "${file}"; then
        log "Database backup created successfully: ${file}"
        log "Backup size: $(du -h "${file}" | cut -f1)"
    else
        die "Database backup failed"
    fi
}

db_restore() {
    local file="$1"
    [ -f "$file" ] || die "Backup file not found: $file"
    
    local cid; cid="$(svc_pick_container mysql)" || true
    [ -n "$cid" ] || die "MySQL service container not found locally."
    
    log "Restoring database from: ${file}"
    if confirm "This will overwrite the current database. Continue? [y/N]"; then
        debug "Restoring to database ${MYSQL_DATABASE} as user ${MYSQL_USER}"
        
        if gunzip -c "${file}" | docker exec -i "$cid" sh -c "mysql -u\"\${MYSQL_USER:-root}\" -p\"\${MYSQL_PASSWORD:-\${MYSQL_ROOT_PASSWORD}}\" \"\${MYSQL_DATABASE}\""; then
            log "Database restored successfully from: ${file}"
        else
            die "Database restore failed"
        fi
    else
        log "Database restore cancelled"
    fi
}

db_shell() {
    log "Opening MySQL shell..."
    debug "Connecting to database ${MYSQL_DATABASE} as user ${MYSQL_USER}"
    svc_exec mysql sh -c 'mysql -u"${MYSQL_USER:-root}" -p"${MYSQL_PASSWORD:-${MYSQL_ROOT_PASSWORD}}" "${MYSQL_DATABASE}"'
}

db_import_default_data() {
    log "Importing default WillowCMS data..."
    
    # Import aiprompts
    log "Importing AI prompts..."
    if svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake default_data_import aiprompts' 2>/dev/null; then
        log "AI prompts imported successfully"
    else
        log "Warning: Failed to import AI prompts (may not be available)"
    fi
    
    # Import email templates
    log "Importing email templates..."
    if svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake default_data_import email_templates' 2>/dev/null; then
        log "Email templates imported successfully"
    else
        log "Warning: Failed to import email templates (may not be available)"
    fi
}

db_export_default_data() {
    log "Exporting default WillowCMS data..."
    local ts; ts="$(date '+%Y%m%d-%H%M%S')"
    local export_dir="${BACKUP_DIR}/default_data_export_${ts}"
    
    mkdir -p "${export_dir}"
    
    # This would need to be implemented based on WillowCMS export capabilities
    log "Default data export functionality needs to be implemented based on WillowCMS capabilities"
    log "Export directory created: ${export_dir}"
}

# === File Operations ===

files_backup() {
    log "Creating files backup..."
    local ts; ts="$(date '+%Y%m%d-%H%M%S')"
    local out="${BACKUP_DIR}/files_${ts}.tar.gz"
    local cid; cid="$(svc_pick_container willowcms)" || true
    
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    debug "Backing up application files from /var/www/html"
    if docker exec "$cid" sh -c "tar -C /var/www/html -czf - logs webroot/uploads tmp/cache" > "${out}"; then
        log "Files backup created: ${out}"
        log "Backup size: $(du -h "${out}" | cut -f1)"
    else
        die "Files backup failed"
    fi
}

files_restore() {
    local file="$1"
    [ -f "$file" ] || die "Backup file not found: $file"
    
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    log "Restoring files from: ${file}"
    if confirm "This will overwrite existing application files. Continue? [y/N]"; then
        debug "Restoring files to /var/www/html"
        if cat "${file}" | docker exec -i "$cid" sh -c "tar -C /var/www/html -xzf -"; then
            log "Files restored successfully from: ${file}"
        else
            die "Files restore failed"
        fi
    else
        log "Files restore cancelled"
    fi
}

# === Log Checksum Operations (per WARP.md requirements) ===

logs_checksum_generate() {
    log "Generating log checksums in willowcms container..."
    debug "Creating logs/checksums/latest.sha256"
    
    if svc_exec willowcms sh -c 'mkdir -p logs/checksums && shasum -a 256 logs/*.log > logs/checksums/latest.sha256 2>/dev/null || echo "No log files found to checksum"'; then
        log "Log checksums generated successfully"
        log "Checksum file: logs/checksums/latest.sha256 (inside willowcms container)"
    else
        log "Warning: Could not generate log checksums"
    fi
}

logs_checksum_verify() {
    log "Verifying log checksums in willowcms container..."
    debug "Checking logs/checksums/latest.sha256"
    
    if svc_exec willowcms sh -c 'cd logs && shasum -a 256 --check checksums/latest.sha256'; then
        log "Log checksum verification passed"
    else
        log "ERROR: Log checksum verification failed"
        return 1
    fi
}

logs_checksum_copy_to_host() {
    log "Copying log checksums to host..."
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    local host_file="${BACKUP_DIR}/log_checksums_$(date '+%Y%m%d-%H%M%S').sha256"
    
    if docker cp "${cid}:/var/www/html/logs/checksums/latest.sha256" "${host_file}" 2>/dev/null; then
        log "Log checksums copied to: ${host_file}"
    else
        log "Warning: Could not copy checksums to host (file may not exist)"
    fi
}

# === Backup Management ===

list_backups() {
    log "Available backups in ${BACKUP_DIR}:"
    echo
    
    if [ -d "${BACKUP_DIR}" ] && [ "$(find "${BACKUP_DIR}" -name "*.gz" -o -name "*.sql" -o -name "*.tar" 2>/dev/null | wc -l)" -gt 0 ]; then
        echo "Database backups:"
        find "${BACKUP_DIR}" -name "db_*.sql.gz" -exec ls -lh {} \; 2>/dev/null | awk '{print "  " $9 " (" $5 ", " $6 " " $7 " " $8 ")"}'
        
        echo
        echo "File backups:"
        find "${BACKUP_DIR}" -name "files_*.tar.gz" -exec ls -lh {} \; 2>/dev/null | awk '{print "  " $9 " (" $5 ", " $6 " " $7 " " $8 ")"}'
        
        echo
        echo "Other backups:"
        find "${BACKUP_DIR}" \( -name "*.gz" -o -name "*.sql" -o -name "*.tar" \) ! -name "db_*" ! -name "files_*" -exec ls -lh {} \; 2>/dev/null | awk '{print "  " $9 " (" $5 ", " $6 " " $7 " " $8 ")"}'
    else
        echo "  No backups found"
    fi
    echo
}

cleanup_old_backups() {
    local days="${1:-7}"
    log "Cleaning up backups older than ${days} days..."
    
    if [ -d "${BACKUP_DIR}" ]; then
        local count
        count=$(find "${BACKUP_DIR}" -name "*.gz" -o -name "*.sql" -o -name "*.tar" -mtime +${days} 2>/dev/null | wc -l)
        
        if [ "$count" -gt 0 ]; then
            if confirm "Delete ${count} backup files older than ${days} days? [y/N]"; then
                find "${BACKUP_DIR}" -name "*.gz" -o -name "*.sql" -o -name "*.tar" -mtime +${days} -delete 2>/dev/null
                log "Cleanup completed: ${count} old backup files removed"
            fi
        else
            log "No old backup files found"
        fi
    fi
}

# === Interactive Menu ===

data_menu() {
    while true; do
        echo
        echo "=== Data Management (${STACK_NAME}) ==="
        echo "1) Database Backup"
        echo "2) Database Restore"
        echo "3) Database Shell"
        echo "4) Import Default Data"
        echo "5) Export Default Data"
        echo "6) Files Backup"
        echo "7) Files Restore"
        echo "8) Generate Log Checksums"
        echo "9) Verify Log Checksums"
        echo "10) Copy Checksums to Host"
        echo "11) List Backups"
        echo "12) Cleanup Old Backups"
        echo "13) Back to Main Menu"
        echo
        read -r -p "Data > " choice
        
        case "$choice" in
            1)
                db_backup
                pause
                ;;
            2)
                list_backups
                read -r -p "Enter backup file path: " backup_file
                if [ -n "$backup_file" ]; then
                    db_restore "$backup_file"
                fi
                pause
                ;;
            3)
                db_shell
                ;;
            4)
                if confirm "Import default WillowCMS data? [y/N]"; then
                    db_import_default_data
                fi
                pause
                ;;
            5)
                db_export_default_data
                pause
                ;;
            6)
                files_backup
                pause
                ;;
            7)
                list_backups
                read -r -p "Enter backup file path: " backup_file
                if [ -n "$backup_file" ]; then
                    files_restore "$backup_file"
                fi
                pause
                ;;
            8)
                logs_checksum_generate
                pause
                ;;
            9)
                logs_checksum_verify
                pause
                ;;
            10)
                logs_checksum_copy_to_host
                pause
                ;;
            11)
                list_backups
                pause
                ;;
            12)
                read -r -p "Delete backups older than how many days? [7]: " days
                days="${days:-7}"
                cleanup_old_backups "$days"
                pause
                ;;
            13|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-13."
                ;;
        esac
    done
}
