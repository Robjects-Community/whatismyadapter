#!/bin/bash

# Log Management Functions for WillowCMS Management Script

# Function to execute log management commands
execute_log_command() {
    local log_choice="$1"
    
    debug_output "Executing log command: $log_choice"
    
    case "$log_choice" in
        20)
            generate_log_checksums
            ;;
        21)
            verify_log_checksums
            ;;
        22)
            show_log_integrity_report
            ;;
        23)
            backup_logs_with_verification
            ;;
        24)
            clear_log_checksums
            ;;
        *)
            echo "Error: Invalid log management command: $log_choice"
            return 1
            ;;
    esac
    return $?
}

# Function to generate checksums for all log files
generate_log_checksums() {
    echo "=== Generate Log File Checksums ==="
    echo
    echo "This will generate SHA256, MD5, and SHA1 checksums for all log files"
    echo "to enable integrity verification and tamper detection."
    echo
    
    read -r -p "Do you want to continue? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo "Operation cancelled."
        return 0
    fi
    
    echo "Generating checksums for log files..."
    echo
    
    if docker compose exec willowcms bin/cake log_checksum generate --format=detailed; then
        echo
        echo "✓ Checksums generated successfully!"
        echo "  - Individual checksum files stored in tmp/checksums/"
        echo "  - Master checksum file created for easy verification"
        echo "  - Use 'verify checksums' to validate log integrity"
    else
        echo "✗ Failed to generate checksums"
        return 1
    fi
}

# Function to verify log file checksums
verify_log_checksums() {
    echo "=== Verify Log File Checksums ==="
    echo
    echo "This will verify the integrity of all log files against their stored checksums."
    echo
    
    echo "Verifying log file checksums..."
    echo
    
    if docker compose exec willowcms bin/cake log_checksum verify --format=detailed; then
        echo
        echo "✓ Checksum verification completed!"
        echo "  Review the results above for any integrity issues."
    else
        echo "✗ Checksum verification failed or found issues"
        echo "  Please review the error messages above."
        return 1
    fi
}

# Function to show comprehensive log integrity report
show_log_integrity_report() {
    echo "=== Log File Integrity Report ==="
    echo
    echo "Generating comprehensive integrity report for all log files..."
    echo
    
    if docker compose exec willowcms bin/cake log_checksum report; then
        echo
        echo "✓ Report generated successfully!"
    else
        echo "✗ Failed to generate integrity report"
        return 1
    fi
}

# Function to create verified backup of log files
backup_logs_with_verification() {
    echo "=== Create Verified Log Backup ==="
    echo
    echo "This will create a backup of all log files with checksum verification"
    echo "to ensure backup integrity."
    echo
    
    # Get current timestamp for backup directory name
    local timestamp=$(date '+%Y-%m-%d_%H-%M-%S')
    local backup_dir="./project_log_backups/logs_backup_${timestamp}"
    
    echo "Backup will be stored in: $backup_dir"
    echo
    
    read -r -p "Do you want to continue? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo "Operation cancelled."
        return 0
    fi
    
    # Create backup directory on host
    mkdir -p "$backup_dir"
    
    echo "Creating verified backup of log files..."
    echo
    
    if docker compose exec willowcms bin/cake log_checksum backup --backup-dir="/var/www/html/$backup_dir"; then
        echo
        echo "✓ Verified log backup created successfully!"
        echo "  Location: $backup_dir"
        echo "  All files verified for integrity during backup process"
        
        # Show backup contents
        echo
        echo "Backup Contents:"
        ls -la "$backup_dir"
        
        # Show total backup size
        local backup_size=$(du -sh "$backup_dir" | cut -f1)
        echo "Total backup size: $backup_size"
        
    else
        echo "✗ Failed to create verified log backup"
        return 1
    fi
}

# Function to clear old checksum files
clear_log_checksums() {
    echo "=== Clear Log Checksums ==="
    echo
    echo "This will remove all stored checksum files for log files."
    echo "⚠️  WARNING: After clearing, you won't be able to verify log integrity"
    echo "   until new checksums are generated."
    echo
    
    # Show current checksum files
    echo "Current checksum files:"
    if docker compose exec willowcms ls -la tmp/checksums/ 2>/dev/null | grep -v '^total'; then
        echo
    else
        echo "  (No checksum files found)"
        return 0
    fi
    
    read -r -p "Are you sure you want to clear all checksum files? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo "Operation cancelled."
        return 0
    fi
    
    echo "Clearing checksum files..."
    
    if docker compose exec willowcms sh -c 'rm -f tmp/checksums/*.txt tmp/checksums/*.json'; then
        echo "✓ Checksum files cleared successfully!"
    else
        echo "✗ Failed to clear checksum files"
        return 1
    fi
}

# Function to show log management menu options (called from ui.sh)
show_log_management_menu() {
    echo "Log Management:"
    echo "  20) Generate Log Checksums"
    echo "  21) Verify Log Checksums" 
    echo "  22) Log Integrity Report"
    echo "  23) Backup Logs with Verification"
    echo "  24) Clear Log Checksums"
}

# Function to get the valid choice range for log management commands
get_log_management_range() {
    echo "20-24"
}
