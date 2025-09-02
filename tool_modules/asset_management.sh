#!/bin/bash

# Execute asset management commands
execute_asset_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        9)
            backup_files_directory
            ;;
        10)
            restore_files_from_backup
            ;;
        11)
            clear_files_backups
            ;;
        *)
            echo "Error: Invalid asset management option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Function to backup files directory
backup_files_directory() {
    echo "Backing up files directory..."
    local timestamp
    timestamp=$(date '+%Y%m%d_%H%M%S')
    local backup_target_dir="./project_files_backups"
    mkdir -p "${backup_target_dir}"
    local backup_file_on_host="${backup_target_dir}/files_backup_${timestamp}.tar.gz"

    # Check if the files directory exists in the container
    if ! docker compose exec -T willowcms [ -d "/var/www/html/webroot/files" ]; then
        echo "Error: The files directory does not exist in the container."
        return 1
    fi

    echo "Creating tar archive in container..."
    local container_temp_tarfile="/tmp/files_backup_${timestamp}.tar.gz"
    
    if ! docker compose exec -T willowcms tar -czf "${container_temp_tarfile}" -C /var/www/html/webroot files; then
        echo "Error: Failed to create tar archive in container."
        return 1
    fi

    echo "Copying tar archive to host..."
    if docker compose cp "willowcms:${container_temp_tarfile}" "${backup_file_on_host}"; then
        if [ -s "${backup_file_on_host}" ]; then
            echo "Files backup completed successfully!"
            echo "Backup saved to host: ${backup_file_on_host}"
        else
            echo "Error: Backup file copied successfully from container but is empty on the host."
            [ -f "${backup_file_on_host}" ] && rm -f "${backup_file_on_host}"
            return 1
        fi
    else
        echo "Error: Failed to copy backup file from container to host."
        [ -f "${backup_file_on_host}" ] && rm -f "${backup_file_on_host}"
        return 1
    fi

    echo "Cleaning up temporary file from container..."
    docker compose exec -T willowcms rm -f "${container_temp_tarfile}"
    
    return 0
}

# Function to restore files from backup
restore_files_from_backup() {
    echo "Restoring Files from Backup..."
    local backup_source_dir="./project_files_backups"

    if [ ! -d "${backup_source_dir}" ]; then
        echo "Backup directory ${backup_source_dir} not found on host."
        return 1
    fi

    # Get backup files sorted by filename (newest first based on YYYYMMDD_HHMMSS timestamp)
    local files_found=()
    while IFS= read -r file; do
        # Only add if it's a regular file (not a directory, symlink, etc)
        if [ -f "$file" ]; then
            files_found+=("$file")
        fi
    done < <(find "${backup_source_dir}" -maxdepth 1 -name "*.tar.gz" -type f -print0 | xargs -0 ls -1 2>/dev/null | sort -r)

    local file_count="${#files_found[@]}"
    if [ "$file_count" -eq 0 ]; then
        echo "No .tar.gz backup files found in host directory: ${backup_source_dir}"
        return 1
    fi

    echo "Available backups from host directory ${backup_source_dir} (newest first):"
    local i
    for i in "${!files_found[@]}"; do
        printf "  %s) %s\n" "$((i + 1))" "$(basename "${files_found[$i]}")"
    done
    echo

    read -r -p "Enter the number of the backup to restore (or 0 to cancel): " selection
    if ! echo "$selection" | grep -E '^[0-9]+$' > /dev/null; then
        echo "Invalid selection: Not a number."
        return 1
    fi

    local S_INT=$((selection))
    if [ "$S_INT" -eq 0 ]; then echo "Operation cancelled."; return 0; fi
    if [ "$S_INT" -lt 1 ] || [ "$S_INT" -gt "$file_count" ]; then echo "Invalid selection: Number out of range."; return 1; fi

    local selected_backup_file_on_host="${files_found[$((S_INT - 1))]}"
    local selected_backup_basename
    selected_backup_basename=$(basename "$selected_backup_file_on_host")
    echo "Selected backup on host: $selected_backup_basename"

    echo "This will replace the existing files directory in the container with the contents of the backup."
    read -r -p "ARE YOU SURE you want to continue? (y/N): " confirm

    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        # First, copy the backup file into the container
        echo "Copying backup file into container..."
        local container_backup_path="/tmp/${selected_backup_basename}"
        if ! docker compose cp "$selected_backup_file_on_host" "willowcms:$container_backup_path"; then
            echo "ERROR: Failed to copy backup file into container."
            return 1
        fi
        
        echo "Checking if files directory exists in container..."
        if ! docker compose exec -T willowcms [ -d "/var/www/html/webroot/files" ]; then
            echo "Creating files directory in container..."
            if ! docker compose exec -T willowcms mkdir -p "/var/www/html/webroot/files"; then
                echo "ERROR: Failed to create files directory in container."
                docker compose exec -T willowcms rm -f "$container_backup_path"
                return 1
            fi
        else
            echo "Backing up existing files directory in container..."
            local temp_backup="/tmp/files_existing_backup_$(date '+%Y%m%d_%H%M%S').tar.gz"
            if ! docker compose exec -T willowcms tar -czf "$temp_backup" -C /var/www/html/webroot files; then
                echo "WARNING: Failed to backup existing files directory. Proceeding anyway."
            fi
        fi
        
        # Extract to a temporary location rather than trying to remove the busy directory
        echo "Extracting backup to temporary location..."
        local temp_extract_dir="/tmp/files_extract_$(date '+%Y%m%d_%H%M%S')"
        if ! docker compose exec -T willowcms mkdir -p "$temp_extract_dir"; then
            echo "ERROR: Failed to create temporary extraction directory."
            docker compose exec -T willowcms rm -f "$container_backup_path"
            return 1
        fi
        
        if ! docker compose exec -T willowcms tar -xzf "$container_backup_path" -C "$temp_extract_dir"; then
            echo "ERROR: Failed to extract backup to temporary location."
            docker compose exec -T willowcms rm -f "$container_backup_path" 
            docker compose exec -T willowcms rm -rf "$temp_extract_dir"
            return 1
        fi
        
        # Now copy from temp location to destination, overwriting existing files
        echo "Copying extracted files to destination..."
        # First, try to use rsync if available
        if docker compose exec -T willowcms command -v rsync >/dev/null 2>&1; then
            echo "Using rsync to update files directory..."
            if ! docker compose exec -T willowcms rsync -a --delete "$temp_extract_dir/files/" "/var/www/html/webroot/files/"; then
                echo "ERROR: Failed to synchronize files with rsync."
                docker compose exec -T willowcms rm -f "$container_backup_path"
                docker compose exec -T willowcms rm -rf "$temp_extract_dir"
                return 1
            fi
        else
            # If rsync is not available, fall back to cp with cleanup
            echo "rsync not found, using cp command instead..."
            # Try to remove existing files (but don't fail if we can't)
            docker compose exec -T willowcms find "/var/www/html/webroot/files" -mindepth 1 -delete || true
            
            # Then copy all extracted files
            if ! docker compose exec -T willowcms cp -a "$temp_extract_dir/files/." "/var/www/html/webroot/files/"; then
                echo "ERROR: Failed to copy files using cp."
                docker compose exec -T willowcms rm -f "$container_backup_path"
                docker compose exec -T willowcms rm -rf "$temp_extract_dir"
                return 1
            fi
        fi
        
        # Clean up temporary files
        echo "Cleaning up temporary files in container..."
        docker compose exec -T willowcms rm -rf "$temp_extract_dir"
        
        # Set ownership to nobody:nobody (per your Dockerfile)
        echo "Setting proper ownership on files directory..."
        if ! docker compose exec -T willowcms chown -R nobody:nobody "/var/www/html/webroot/files"; then
            echo "WARNING: Failed to set ownership on files directory."
        fi
        
        # Set proper permissions (755 for directories, 644 for files - per your Dockerfile)
        echo "Setting proper permissions on files directory..."
        if ! docker compose exec -T willowcms find "/var/www/html/webroot/files" -type d -exec chmod 755 {} \; ; then
            echo "WARNING: Failed to set directory permissions."
        fi
        if ! docker compose exec -T willowcms find "/var/www/html/webroot/files" -type f -exec chmod 644 {} \; ; then
            echo "WARNING: Failed to set file permissions."
        fi
        
        # Remove temporary backup file
        docker compose exec -T willowcms rm -f "$container_backup_path"
        
        echo "Files restore process completed successfully."
        echo "Files ownership set to nobody:nobody (webserver user)"
        echo "Directory permissions set to 755, file permissions set to 644"
    else
        echo "Files restore cancelled."
    fi
    
    return 0
}

# Function to clear files backups
clear_files_backups() {
    echo "Clear Files Backups..."
    local backup_source_dir="./project_files_backups"

    if [ ! -d "${backup_source_dir}" ]; then
        echo "Backup directory ${backup_source_dir} not found."
        return 1
    fi

    # Get backup files sorted by filename (newest first based on YYYYMMDD_HHMMSS timestamp)
    local files_found=()
    while IFS= read -r file; do
        # Only add if it's a regular file (not a directory, symlink, etc)
        if [ -f "$file" ]; then
            files_found+=("$file")
        fi
    done < <(find "${backup_source_dir}" -maxdepth 1 -name "*.tar.gz" -type f -print0 | xargs -0 ls -1 2>/dev/null | sort -r)

    local file_count="${#files_found[@]}"
    if [ "$file_count" -eq 0 ]; then
        echo "No .tar.gz backup files found in directory: ${backup_source_dir}"
        return 0
    fi

    echo "Found $file_count files backup file(s) in ${backup_source_dir}:"
    local i
    for i in "${!files_found[@]}"; do
        printf "  %s\n" "$(basename "${files_found[$i]}")"
    done
    echo

    read -r -p "Are you sure you want to DELETE ALL files backup files? This cannot be undone! (y/N): " confirm

    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        echo "Deleting files backup files..."
        local deleted_count=0
        for file in "${files_found[@]}"; do
            if rm -f "$file"; then
                echo "Deleted: $(basename "$file")"
                ((deleted_count++))
            else
                echo "Failed to delete: $(basename "$file")"
            fi
        done
        echo "Successfully deleted $deleted_count out of $file_count files backup files."
        
        # Remove directory if it's empty
        if [ -d "${backup_source_dir}" ] && [ -z "$(ls -A "${backup_source_dir}")" ]; then
            rmdir "${backup_source_dir}"
            echo "Removed empty backup directory: ${backup_source_dir}"
        fi
    else
        echo "Files backup clearing cancelled."
    fi
    
    return 0
}