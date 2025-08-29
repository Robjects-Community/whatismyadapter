#!/bin/bash

# Execute data management commands (options 1-5)
execute_data_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        1)
            echo "Running Default Data Import..."
            docker compose exec willowcms bin/cake default_data_import
            ;;
        2)
            echo "Running Default Data Export..."
            docker compose exec willowcms bin/cake default_data_export
            ;;
        3)
            dump_mysql_database
            ;;
        4)
            load_database_from_backup
            ;;
        5)
            clear_database_backups
            ;;
        *)
            echo "Error: Invalid data management option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Function to dump MySQL database
dump_mysql_database() {
    echo "Dumping MySQL Database..."
    local timestamp
    timestamp=$(date '+%Y%m%d_%H%M%S')
    local backup_target_dir="./project_mysql_backups"
    mkdir -p "${backup_target_dir}"
    local backup_file_on_host="${backup_target_dir}/db_dump_${timestamp}.sql"

    local container_temp_filename="db_dump_internal_${timestamp}.sql"
    local container_full_temp_path="/tmp/${container_temp_filename}"

    echo "Checking required environment variables in 'mysql' container..."
    if ! docker compose exec -T mysql sh -c 'test -n "$DB_DATABASE"'; then
        echo "Error: DB_DATABASE environment variable is not set or empty in the 'mysql' container."
        return 1
    fi
    if ! docker compose exec -T mysql sh -c 'test -n "$MYSQL_ROOT_PASSWORD"'; then
        echo "Error: MYSQL_ROOT_PASSWORD environment variable is not set or empty in the 'mysql' container."
        return 1
    fi
    local db_to_dump_raw
    db_to_dump_raw=$(docker compose exec -T mysql printenv DB_DATABASE)
    local db_to_dump # Keep for messages if needed, but use $DB_DATABASE in container
    db_to_dump=$(echo "$db_to_dump_raw" | tr -d '\r\n')

    # Check mysqldump version (optional, for user info or more complex logic)
    echo "Checking mysqldump version in container..."
    local mysqldump_version
    mysqldump_version=$(docker compose exec -T mysql mysqldump --version || echo "Unknown")
    echo "MySQLDump version in container: $mysqldump_version"
    if [[ "$mysqldump_version" == "Unknown" ]]; then
         echo "Warning: Could not determine mysqldump version. Proceeding without --skip-definer."
    elif [[ "$mysqldump_version" =~ Ver\ ([0-9]+\.[0-9]+) && ( "$(echo "${BASH_REMATCH[1]} >= 5.7" | bc -l)" -eq 1 || "$(echo "${BASH_REMATCH[1]} >= 10.2" | bc -l)" -eq 1 ) ]]; then
        echo "Note: Your mysqldump version likely supports --skip-definer. Consider adding it back if portability is a concern and you upgrade your Docker image."
    else
        echo "Note: Your mysqldump version may not support --skip-definer. It will be omitted."
    fi

    echo "Attempting to dump database '$db_to_dump' to temp file in 'mysql' container..."
    local mysqldump_stderr_temp_file="/tmp/mysqldump_errors_${timestamp}.log"
    # Note: Removed --databases flag as it was causing issues during restore
    local cmd_for_sh="mysqldump --verbose --routines --triggers --events --no-tablespaces --single-transaction -uroot -p\"\$MYSQL_ROOT_PASSWORD\" \"\$DB_DATABASE\" > \"${container_full_temp_path}\" 2> \"${mysqldump_stderr_temp_file}\""

    echo "Running in container: sh -c '$cmd_for_sh'"
    local dump_exit_code=0
    docker compose exec -T mysql sh -c "$cmd_for_sh" || dump_exit_code=$?

    echo "--- MySQLDump Stderr Output (if any) ---"
    docker compose exec -T mysql sh -c "cat \"${mysqldump_stderr_temp_file}\" && rm -f \"${mysqldump_stderr_temp_file}\"" || echo "No stderr from mysqldump or failed to retrieve/remove stderr file."
    echo "--- End MySQLDump Stderr ---"

    if [ "$dump_exit_code" -eq 0 ]; then
        echo "Dump command inside container apparently succeeded. Copying to host..."
        if docker compose cp "mysql:${container_full_temp_path}" "${backup_file_on_host}"; then
            if [ -s "${backup_file_on_host}" ]; then
                echo "Database dump completed successfully!"
                echo "Backup saved to host: ${backup_file_on_host}"
            else
                echo "Error: Dump file copied successfully from container but is empty on the host."
                [ -f "${backup_file_on_host}" ] && rm -f "${backup_file_on_host}"
                return 1
            fi
        else
            echo "Error: Failed to copy dump file from container to host."
            [ -f "${backup_file_on_host}" ] && rm -f "${backup_file_on_host}"
            return 1
        fi
    else
        echo "Error: Database dump command (to file inside container) failed with exit code $dump_exit_code."
        return 1
    fi

    echo "Cleaning up temporary dump file from container: mysql:${container_full_temp_path}"
    docker compose exec -T mysql rm -f "${container_full_temp_path}"
    
    # Verify the dump contains CREATE TABLE statements
    echo "Verifying the dump contains table definitions..."
    if grep -q "CREATE TABLE" "${backup_file_on_host}"; then
        echo "Verification passed: The dump file contains table definitions."
    else
        echo "Warning: The dump file doesn't appear to contain CREATE TABLE statements."
        echo "This may cause issues during restore."
    fi
    
    return 0
}

# Function to load database from backup
load_database_from_backup() {
    echo "Loading Database from Backup..."
    local backup_source_dir="./project_mysql_backups"

    if [ ! -d "${backup_source_dir}" ]; then
        echo "Backup directory ${backup_source_dir} not found on host."
        return 1
    fi

    echo "Checking required environment variables in 'mysql' container for restore..."
    if ! docker compose exec -T mysql sh -c 'test -n "$DB_DATABASE"'; then
        echo "Error: DB_DATABASE environment variable is not set or empty in the 'mysql' container for restore."
        return 1
    fi
    if ! docker compose exec -T mysql sh -c 'test -n "$MYSQL_ROOT_PASSWORD"'; then
        echo "Error: MYSQL_ROOT_PASSWORD environment variable is not set or empty in the 'mysql' container for restore."
        return 1
    fi

    # Get SQL files sorted by filename (newest first based on YYYYMMDD_HHMMSS timestamp)
    local files_found=()
    while IFS= read -r file; do
        # Only add if it's a regular file (not a directory, symlink, etc)
        if [ -f "$file" ]; then
            files_found+=("$file")
        fi
    done < <(find "${backup_source_dir}" -maxdepth 1 -name "*.sql" -type f -print0 | xargs -0 ls -1 2>/dev/null | sort -r)

    local file_count="${#files_found[@]}"
    if [ "$file_count" -eq 0 ]; then
        echo "No .sql backup files found in host directory: ${backup_source_dir}"
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
    selected_backup_basename=$(basename "$selected_backup_file_on_host") # Safe: basename won't fail here
    echo "Selected backup on host: $selected_backup_basename"

    # Check if the SQL file has table definitions
    echo "Verifying SQL file contains CREATE TABLE statements..."
    if ! grep -q "CREATE TABLE" "$selected_backup_file_on_host"; then
        echo "ERROR: The selected SQL file doesn't appear to contain any CREATE TABLE statements."
        echo "This is likely not a valid database dump. Please select a different file."
        return 1
    fi
    echo "Verification OK: SQL file contains table definitions."

    # Fetch DB_DATABASE from container for messages
    local target_db_name_raw
    target_db_name_raw=$(docker compose exec -T mysql printenv DB_DATABASE)
    local target_db_name
    target_db_name=$(echo "$target_db_name_raw" | tr -d '\r\n')
    if [ -z "$target_db_name" ]; then echo "Error: Could not retrieve DB_DATABASE name from 'mysql' container."; return 1; fi

    echo "This will DROP the existing database '$target_db_name' (if it exists) in the container and RESTORE from '$selected_backup_basename'."
    read -r -p "ARE YOU SURE you want to continue? (y/N): " confirm

    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        # First, copy the SQL file into the container
        echo "Copying SQL file into MySQL container..."
        local container_sql_path="/tmp/${selected_backup_basename}"
        if ! docker compose cp "$selected_backup_file_on_host" "mysql:$container_sql_path"; then
            echo "ERROR: Failed to copy SQL file into container."
            return 1
        fi
        
        echo "Preparing database '$target_db_name' in container..."
        
        local sql_drop="DROP DATABASE IF EXISTS \`${target_db_name}\`;"
        local sql_create="CREATE DATABASE \`${target_db_name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        
        echo "Dropping database (if exists)..."
        if ! docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e '${sql_drop}'"; then
            echo "Warning: Drop database command showed an error. Continuing anyway."
        fi
        
        echo "Creating database..."
        if ! docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e '${sql_create}'"; then
            echo "ERROR: Failed to create database '$target_db_name'. Aborting restore."
            return 1
        fi
        
        echo "Database prepared. Starting restore process..."
        echo "Executing SQL file in container (this may take a while)..."
        
        # Execute the SQL file directly in the container
        if ! docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" $target_db_name < $container_sql_path"; then
            echo "ERROR: MySQL restore command failed."
            echo "Checking if any tables were created despite the error..."
            
            # Check if any tables were created
            local table_count
            table_count=$(docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e 'USE $target_db_name; SHOW TABLES;' | wc -l")
            if [ "$table_count" -gt 0 ]; then
                echo "Some tables appear to have been created despite the error."
                echo "Table count: $((table_count-1))" # Subtract 1 for the header row
            else
                echo "No tables were created. The restore completely failed."
            fi
            
            echo "Cleaning up temporary files in container..."
            docker compose exec -T mysql sh -c "rm -f $container_sql_path"
            return 1
        fi
        
        # Verify tables were created
        echo "Verifying tables were created in database..."
        local table_count
        table_count=$(docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e 'USE $target_db_name; SHOW TABLES;' | wc -l")
        if [ "$((table_count-1))" -gt 0 ]; then # Subtract 1 for the header row
            echo "SUCCESS: Database restore completed. $((table_count-1)) tables created."
            
            # Show the tables that were created
            echo "Tables created:"
            docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e 'USE $target_db_name; SHOW TABLES;'"
        else
            echo "ERROR: Database restore appeared to succeed, but no tables were created."
            echo "This suggests a problem with the SQL file format or MySQL compatibility."
            echo "Cleaning up temporary files in container..."
            docker compose exec -T mysql sh -c "rm -f $container_sql_path"
            return 1
        fi
        
        echo "Cleaning up temporary files in container..."
        docker compose exec -T mysql sh -c "rm -f $container_sql_path"
        
        # Clear CakePHP cache after successful restore
        echo "Clearing WillowCMS Cache..."
        if docker compose exec willowcms bin/cake cache clear_all; then
            echo "WillowCMS cache cleared successfully."
        else
            echo "Warning: Failed to clear WillowCMS cache. You may need to do this manually."
        fi
        
        echo "Database restore and cache clear process completed successfully."
    else
        echo "Database restore cancelled."
    fi
    
    return 0
}

# Function to clear database backups
clear_database_backups() {
    echo "Clear Database Backups..."
    local backup_source_dir="./project_mysql_backups"

    if [ ! -d "${backup_source_dir}" ]; then
        echo "Backup directory ${backup_source_dir} not found."
        return 1
    fi

    # Get SQL files sorted by filename (newest first based on YYYYMMDD_HHMMSS timestamp)
    local files_found=()
    while IFS= read -r file; do
        # Only add if it's a regular file (not a directory, symlink, etc)
        if [ -f "$file" ]; then
            files_found+=("$file")
        fi
    done < <(find "${backup_source_dir}" -maxdepth 1 -name "*.sql" -type f -print0 | xargs -0 ls -1 2>/dev/null | sort -r)

    local file_count="${#files_found[@]}"
    if [ "$file_count" -eq 0 ]; then
        echo "No .sql backup files found in directory: ${backup_source_dir}"
        return 0
    fi

    echo "Found $file_count database backup file(s) in ${backup_source_dir}:"
    local i
    for i in "${!files_found[@]}"; do
        printf "  %s\n" "$(basename "${files_found[$i]}")"
    done
    echo

    read -r -p "Are you sure you want to DELETE ALL database backup files? This cannot be undone! (y/N): " confirm

    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        echo "Deleting database backup files..."
        local deleted_count=0
        for file in "${files_found[@]}"; do
            if rm -f "$file"; then
                echo "Deleted: $(basename "$file")"
                ((deleted_count++))
            else
                echo "Failed to delete: $(basename "$file")"
            fi
        done
        echo "Successfully deleted $deleted_count out of $file_count database backup files."
        
        # Remove directory if it's empty
        if [ -d "${backup_source_dir}" ] && [ -z "$(ls -A "${backup_source_dir}")" ]; then
            rmdir "${backup_source_dir}"
            echo "Removed empty backup directory: ${backup_source_dir}"
        fi
    else
        echo "Database backup clearing cancelled."
    fi
    
    return 0
}