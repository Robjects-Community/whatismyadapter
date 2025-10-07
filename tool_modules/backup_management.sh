#!/bin/bash

# Backup Management Module
# Handles archival, restoration, and statistics for project backups

# Configuration
KEEP_BACKUPS_COUNT=3
ARCHIVE_BASE_DIR="./archives"
BACKUP_FOLDERS=(
    "project_files_backups"
    "project_log_backups"
    "project_mysql_backups"
)

# Execute backup management commands
execute_backup_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        25)
            archive_old_backups
            ;;
        26)
            view_backup_statistics
            ;;
        27)
            restore_archived_backup
            ;;
        28)
            clean_archived_backups
            ;;
        *)
            echo "Error: Invalid backup management option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Initialize archive directory structure
initialize_archive_directories() {
    local created_count=0
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        if [ ! -d "$archive_path" ]; then
            if mkdir -p "$archive_path"; then
                ((created_count++))
                debug_output "Created archive directory: $archive_path"
            else
                echo "Error: Failed to create archive directory: $archive_path"
                return 1
            fi
        fi
    done
    
    if [ "$created_count" -gt 0 ]; then
        echo "Created $created_count archive directories"
    fi
    return 0
}

# Count files in a directory
count_files_in_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
        echo "0"
        return
    fi
    find "$dir" -mindepth 1 -maxdepth 1 | wc -l | tr -d ' '
}

# Get sorted backups (newest first)
get_sorted_backups() {
    local backup_dir="$1"
    if [ ! -d "$backup_dir" ]; then
        return
    fi
    find "$backup_dir" -mindepth 1 -maxdepth 1 -print0 | xargs -0 ls -1t 2>/dev/null || true
}

# Archive old backups - keep only N most recent
archive_old_backups() {
    echo "==================================="
    echo "Archive Old Backups"
    echo "==================================="
    echo ""
    echo "This will keep the $KEEP_BACKUPS_COUNT most recent backups in each folder"
    echo "and move older backups to ./archives/"
    echo ""
    
    # Initialize archive structure
    if ! initialize_archive_directories; then
        echo "Error: Failed to initialize archive directories"
        return 1
    fi
    
    local total_archived=0
    local has_errors=false
    
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local backup_path="./${folder}"
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        
        if [ ! -d "$backup_path" ]; then
            echo "Skipping $folder: directory not found"
            continue
        fi
        
        local total_count
        total_count=$(count_files_in_dir "$backup_path")
        
        if [ "$total_count" -le "$KEEP_BACKUPS_COUNT" ]; then
            echo "✓ $folder: $total_count backups (within retention limit)"
            continue
        fi
        
        local to_archive=$((total_count - KEEP_BACKUPS_COUNT))
        echo "→ $folder: $total_count backups found, archiving $to_archive oldest..."
        
        local archived_count=0
        
        # Get files sorted by time (newest first), then skip the first KEEP_BACKUPS_COUNT
        while IFS= read -r item; do
            local basename_item
            basename_item=$(basename "$item")
            local dest="$archive_path/$basename_item"
            
            if mv "$item" "$dest" 2>/dev/null; then
                echo "  Archived: $basename_item"
                ((archived_count++))
                ((total_archived++))
            else
                echo "  Error: Failed to archive $basename_item"
                has_errors=true
            fi
        done < <(get_sorted_backups "$backup_path" | tail -n "+$((KEEP_BACKUPS_COUNT + 1))")
        
        if [ "$archived_count" -gt 0 ]; then
            echo "  ✓ Archived $archived_count backup(s)"
        fi
        echo ""
    done
    
    echo "==================================="
    if [ "$total_archived" -gt 0 ]; then
        echo "✓ Successfully archived $total_archived backup(s)"
    else
        echo "No backups needed archiving"
    fi
    
    if [ "$has_errors" = true ]; then
        echo "⚠  Some errors occurred during archival"
        return 1
    fi
    
    echo "==================================="
    return 0
}

# View backup statistics
view_backup_statistics() {
    echo "==================================="
    echo "Backup Statistics"
    echo "==================================="
    echo ""
    
    # Initialize archive structure if needed
    initialize_archive_directories > /dev/null 2>&1
    
    local total_active=0
    local total_archived=0
    
    echo "Active Backups:"
    echo "-------------------------------------"
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local backup_path="./${folder}"
        local count
        count=$(count_files_in_dir "$backup_path")
        total_active=$((total_active + count))
        printf "  %-30s %3s files/folders\n" "$folder:" "$count"
    done
    
    echo ""
    echo "Archived Backups:"
    echo "-------------------------------------"
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        local count
        count=$(count_files_in_dir "$archive_path")
        total_archived=$((total_archived + count))
        printf "  %-30s %3s files/folders\n" "$folder:" "$count"
    done
    
    echo ""
    echo "==================================="
    echo "Total Active Backups:   $total_active"
    echo "Total Archived Backups: $total_archived"
    echo "Total All Backups:      $((total_active + total_archived))"
    echo "==================================="
    
    return 0
}

# Get file size in human-readable format
get_file_size() {
    local file="$1"
    if [ -f "$file" ]; then
        du -h "$file" | cut -f1
    elif [ -d "$file" ]; then
        du -sh "$file" | cut -f1
    else
        echo "N/A"
    fi
}

# Get file modification time
get_file_time() {
    local file="$1"
    if [ -e "$file" ]; then
        stat -f "%Sm" -t "%Y-%m-%d %H:%M:%S" "$file" 2>/dev/null || \
        stat -c "%y" "$file" 2>/dev/null | cut -d'.' -f1 || \
        echo "Unknown"
    else
        echo "Unknown"
    fi
}

# Restore archived backup
restore_archived_backup() {
    echo "==================================="
    echo "Restore Archived Backup"
    echo "==================================="
    echo ""
    
    # Check if archives exist
    if [ ! -d "$ARCHIVE_BASE_DIR" ]; then
        echo "No archive directory found at: $ARCHIVE_BASE_DIR"
        return 1
    fi
    
    # First, let user choose backup type
    echo "Select backup type to restore:"
    echo ""
    local i=1
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        local count
        count=$(count_files_in_dir "$archive_path")
        printf "  %s) %-30s (%s archived)\n" "$i" "$folder" "$count"
        ((i++))
    done
    echo "  0) Cancel"
    echo ""
    
    read -r -p "Enter your choice [0-${#BACKUP_FOLDERS[@]}]: " type_choice
    
    if ! echo "$type_choice" | grep -Eq '^[0-9]+$'; then
        echo "Invalid selection: Not a number"
        return 1
    fi
    
    local type_int=$((type_choice))
    if [ "$type_int" -eq 0 ]; then
        echo "Operation cancelled"
        return 0
    fi
    
    if [ "$type_int" -lt 1 ] || [ "$type_int" -gt "${#BACKUP_FOLDERS[@]}" ]; then
        echo "Invalid selection: Number out of range"
        return 1
    fi
    
    local selected_folder="${BACKUP_FOLDERS[$((type_int - 1))]}"
    local archive_path="${ARCHIVE_BASE_DIR}/${selected_folder}"
    local restore_path="./${selected_folder}"
    
    echo ""
    echo "Selected: $selected_folder"
    echo ""
    
    # Get archived files
    local files_found=()
    while IFS= read -r file; do
        if [ -e "$file" ]; then
            files_found+=("$file")
        fi
    done < <(get_sorted_backups "$archive_path")
    
    local file_count="${#files_found[@]}"
    if [ "$file_count" -eq 0 ]; then
        echo "No archived backups found in: $archive_path"
        return 1
    fi
    
    echo "Available archived backups (newest first):"
    echo "-------------------------------------"
    for i in "${!files_found[@]}"; do
        local file="${files_found[$i]}"
        local basename_file
        basename_file=$(basename "$file")
        local size
        size=$(get_file_size "$file")
        local mtime
        mtime=$(get_file_time "$file")
        printf "  %2s) %-40s %8s  %s\n" "$((i + 1))" "$basename_file" "$size" "$mtime"
    done
    echo ""
    
    read -r -p "Enter the number of the backup to restore (or 0 to cancel): " selection
    if ! echo "$selection" | grep -Eq '^[0-9]+$'; then
        echo "Invalid selection: Not a number"
        return 1
    fi
    
    local sel_int=$((selection))
    if [ "$sel_int" -eq 0 ]; then
        echo "Operation cancelled"
        return 0
    fi
    
    if [ "$sel_int" -lt 1 ] || [ "$sel_int" -gt "$file_count" ]; then
        echo "Invalid selection: Number out of range"
        return 1
    fi
    
    local selected_file="${files_found[$((sel_int - 1))]}"
    local selected_basename
    selected_basename=$(basename "$selected_file")
    
    echo ""
    echo "Selected: $selected_basename"
    echo "This will restore this backup from archives to: $restore_path"
    echo ""
    read -r -p "Continue with restore? (y/N): " confirm
    
    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        # Ensure restore directory exists
        mkdir -p "$restore_path"
        
        local dest="$restore_path/$selected_basename"
        if mv "$selected_file" "$dest"; then
            echo ""
            echo "✓ Successfully restored: $selected_basename"
            echo "  Restored to: $dest"
            return 0
        else
            echo ""
            echo "Error: Failed to restore backup"
            return 1
        fi
    else
        echo "Restore cancelled"
        return 0
    fi
}

# Clean archived backups
clean_archived_backups() {
    echo "==================================="
    echo "Clean Archived Backups"
    echo "==================================="
    echo ""
    echo "⚠️  WARNING: This will permanently delete archived backups!"
    echo ""
    
    # Check if archives exist
    if [ ! -d "$ARCHIVE_BASE_DIR" ]; then
        echo "No archive directory found at: $ARCHIVE_BASE_DIR"
        return 1
    fi
    
    # Show statistics first
    echo "Current archived backups:"
    echo "-------------------------------------"
    local total_archived=0
    local has_archives=false
    
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        local count
        count=$(count_files_in_dir "$archive_path")
        if [ "$count" -gt 0 ]; then
            has_archives=true
        fi
        total_archived=$((total_archived + count))
        printf "  %-30s %3s files/folders\n" "$folder:" "$count"
    done
    
    echo "-------------------------------------"
    echo "Total archived: $total_archived"
    echo ""
    
    if [ "$has_archives" = false ]; then
        echo "No archived backups to clean"
        return 0
    fi
    
    # First confirmation
    echo "Select what to clean:"
    echo ""
    local i=1
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        local count
        count=$(count_files_in_dir "$archive_path")
        printf "  %s) Clean %-30s (%s archived)\n" "$i" "$folder" "$count"
        ((i++))
    done
    echo "  $i) Clean ALL archived backups"
    echo "  0) Cancel"
    echo ""
    
    read -r -p "Enter your choice [0-$i]: " clean_choice
    
    if ! echo "$clean_choice" | grep -Eq '^[0-9]+$'; then
        echo "Invalid selection: Not a number"
        return 1
    fi
    
    local clean_int=$((clean_choice))
    if [ "$clean_int" -eq 0 ]; then
        echo "Operation cancelled"
        return 0
    fi
    
    local clean_all=false
    local folders_to_clean=()
    
    if [ "$clean_int" -eq "$i" ]; then
        # Clean all
        clean_all=true
        folders_to_clean=("${BACKUP_FOLDERS[@]}")
    elif [ "$clean_int" -ge 1 ] && [ "$clean_int" -le "${#BACKUP_FOLDERS[@]}" ]; then
        # Clean specific folder
        folders_to_clean=("${BACKUP_FOLDERS[$((clean_int - 1))]}")
    else
        echo "Invalid selection: Number out of range"
        return 1
    fi
    
    # Second confirmation
    echo ""
    echo "⚠️  FINAL CONFIRMATION REQUIRED ⚠️"
    echo ""
    if [ "$clean_all" = true ]; then
        echo "You are about to DELETE ALL $total_archived archived backup(s)!"
    else
        echo "You are about to DELETE archived backups for: ${folders_to_clean[0]}"
    fi
    echo ""
    echo "This action CANNOT be undone!"
    echo ""
    read -r -p "Type 'DELETE' to confirm (anything else cancels): " final_confirm
    
    if [ "$final_confirm" != "DELETE" ]; then
        echo "Operation cancelled"
        return 0
    fi
    
    # Perform deletion
    echo ""
    echo "Deleting archived backups..."
    local deleted_count=0
    local error_count=0
    
    for folder in "${folders_to_clean[@]}"; do
        local archive_path="${ARCHIVE_BASE_DIR}/${folder}"
        
        if [ ! -d "$archive_path" ]; then
            continue
        fi
        
        echo "Cleaning: $folder"
        
        while IFS= read -r item; do
            local basename_item
            basename_item=$(basename "$item")
            if rm -rf "$item" 2>/dev/null; then
                echo "  Deleted: $basename_item"
                ((deleted_count++))
            else
                echo "  Error: Failed to delete $basename_item"
                ((error_count++))
            fi
        done < <(find "$archive_path" -mindepth 1 -maxdepth 1)
    done
    
    echo ""
    echo "==================================="
    echo "Deleted: $deleted_count backup(s)"
    if [ "$error_count" -gt 0 ]; then
        echo "Errors: $error_count"
        echo "==================================="
        return 1
    fi
    echo "==================================="
    
    return 0
}
