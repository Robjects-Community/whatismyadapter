#!/bin/bash

################################################################################
# Backup Archival Script
# 
# Purpose: Automatically manages project backups by keeping only the most
#          recent N backups and archiving older ones.
#
# Features:
#   - Configurable retention policy (keep N most recent backups)
#   - Automatic archive folder creation
#   - Preserves file timestamps
#   - Checksum verification for archived files
#   - Detailed logging
#   - Dry-run mode for testing
#   - Supports multiple backup types (files, logs, mysql)
#
# Usage:
#   ./archive_old_backups.sh [options]
#
# Options:
#   -k, --keep NUM       Number of recent backups to keep (default: 3)
#   -d, --dry-run        Show what would be archived without moving files
#   -v, --verbose        Enable verbose output
#   -c, --checksum       Generate checksums for archived files
#   -h, --help           Show this help message
#
# Example:
#   ./archive_old_backups.sh --keep 5 --checksum
#   ./archive_old_backups.sh --dry-run --verbose
#
################################################################################

set -euo pipefail

# Default configuration
KEEP_COUNT=3
DRY_RUN=false
VERBOSE=false
GENERATE_CHECKSUMS=false
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
ARCHIVE_ROOT="$PROJECT_ROOT/archives"
LOG_FILE="$PROJECT_ROOT/tools/logs/archive_backups.log"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Backup folder patterns to manage
BACKUP_FOLDERS=(
    "project_files_backups"
    "project_log_backups"
    "project_mysql_backups"
)

################################################################################
# Functions
################################################################################

print_usage() {
    cat << EOF
Usage: $(basename "$0") [options]

Automatically manages project backups by keeping only the most recent N backups
and archiving older ones.

Options:
    -k, --keep NUM       Number of recent backups to keep (default: 3)
    -d, --dry-run        Show what would be archived without moving files
    -v, --verbose        Enable verbose output
    -c, --checksum       Generate checksums for archived files
    -h, --help           Show this help message

Examples:
    $(basename "$0") --keep 5 --checksum
    $(basename "$0") --dry-run --verbose

EOF
}

log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp
    timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
    
    # Create log directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    
    echo "[$timestamp] [$level] $message" | tee -a "$LOG_FILE"
}

log_verbose() {
    if [[ "$VERBOSE" == true ]]; then
        log "DEBUG" "$@"
    fi
}

print_color() {
    local color="$1"
    shift
    echo -e "${color}$*${NC}"
}

check_dependencies() {
    local deps=(find sort head tail mv mkdir)
    
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            print_color "$RED" "Error: Required command '$dep' not found"
            exit 1
        fi
    done
    
    if [[ "$GENERATE_CHECKSUMS" == true ]]; then
        if ! command -v shasum &> /dev/null && ! command -v sha256sum &> /dev/null; then
            print_color "$RED" "Error: Neither 'shasum' nor 'sha256sum' found"
            exit 1
        fi
    fi
}

create_archive_structure() {
    log "INFO" "Creating archive directory structure..."
    
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="$ARCHIVE_ROOT/$folder"
        
        if [[ "$DRY_RUN" == false ]]; then
            mkdir -p "$archive_path"
            log_verbose "Created: $archive_path"
        else
            print_color "$YELLOW" "[DRY-RUN] Would create: $archive_path"
        fi
    done
}

count_files() {
    local dir="$1"
    
    if [[ ! -d "$dir" ]]; then
        echo 0
        return
    fi
    
    # Count files and directories (excluding . and ..)
    find "$dir" -mindepth 1 -maxdepth 1 | wc -l | tr -d ' '
}

get_sorted_backups() {
    local backup_dir="$1"
    
    if [[ ! -d "$backup_dir" ]]; then
        return
    fi
    
    # Sort by modification time (newest first)
    find "$backup_dir" -mindepth 1 -maxdepth 1 -print0 | \
        xargs -0 ls -1t 2>/dev/null || true
}

generate_checksum() {
    local file="$1"
    local checksum_file="${file}.sha256"
    
    if [[ -f "$file" ]]; then
        if command -v shasum &> /dev/null; then
            shasum -a 256 "$file" > "$checksum_file"
        elif command -v sha256sum &> /dev/null; then
            sha256sum "$file" > "$checksum_file"
        fi
        log_verbose "Generated checksum: $checksum_file"
    fi
}

archive_old_backups() {
    local backup_folder="$1"
    local backup_path="$PROJECT_ROOT/$backup_folder"
    local archive_path="$ARCHIVE_ROOT/$backup_folder"
    
    if [[ ! -d "$backup_path" ]]; then
        log_verbose "Skipping $backup_folder: directory not found"
        return
    fi
    
    local total_count
    total_count=$(count_files "$backup_path")
    
    if [[ $total_count -le $KEEP_COUNT ]]; then
        print_color "$GREEN" "✓ $backup_folder: $total_count backups (within retention limit)"
        log_verbose "No archival needed for $backup_folder"
        return
    fi
    
    local to_archive=$((total_count - KEEP_COUNT))
    print_color "$BLUE" "→ $backup_folder: $total_count backups found, archiving $to_archive oldest..."
    
    local archived_count=0
    
    # Get files sorted by time (newest first), then skip the first KEEP_COUNT
    while IFS= read -r item; do
        local basename_item
        basename_item=$(basename "$item")
        local dest="$archive_path/$basename_item"
        
        if [[ "$DRY_RUN" == false ]]; then
            mv "$item" "$dest"
            log "INFO" "Archived: $basename_item"
            
            if [[ "$GENERATE_CHECKSUMS" == true && -f "$dest" ]]; then
                generate_checksum "$dest"
            fi
            
            ((archived_count++))
        else
            print_color "$YELLOW" "  [DRY-RUN] Would archive: $basename_item"
            ((archived_count++))
        fi
    done < <(get_sorted_backups "$backup_path" | tail -n "+$((KEEP_COUNT + 1))")
    
    if [[ $archived_count -gt 0 ]]; then
        print_color "$GREEN" "  ✓ Archived $archived_count backup(s)"
    fi
}

print_summary() {
    print_color "$BLUE" "\n=== Backup Summary ==="
    echo ""
    
    print_color "$BLUE" "Active Backups:"
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local backup_path="$PROJECT_ROOT/$folder"
        local count
        count=$(count_files "$backup_path")
        printf "  %-30s %s\n" "$folder:" "$count files/folders"
    done
    
    echo ""
    print_color "$BLUE" "Archived Backups:"
    for folder in "${BACKUP_FOLDERS[@]}"; do
        local archive_path="$ARCHIVE_ROOT/$folder"
        local count
        count=$(count_files "$archive_path")
        printf "  %-30s %s\n" "$folder:" "$count files/folders"
    done
    
    echo ""
    if [[ "$DRY_RUN" == true ]]; then
        print_color "$YELLOW" "Note: This was a dry-run. No files were moved."
    fi
}

################################################################################
# Main Script
################################################################################

main() {
    print_color "$BLUE" "=== Backup Archival Script ==="
    echo ""
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -k|--keep)
                KEEP_COUNT="$2"
                shift 2
                ;;
            -d|--dry-run)
                DRY_RUN=true
                shift
                ;;
            -v|--verbose)
                VERBOSE=true
                shift
                ;;
            -c|--checksum)
                GENERATE_CHECKSUMS=true
                shift
                ;;
            -h|--help)
                print_usage
                exit 0
                ;;
            *)
                print_color "$RED" "Error: Unknown option: $1"
                print_usage
                exit 1
                ;;
        esac
    done
    
    # Validate configuration
    if ! [[ "$KEEP_COUNT" =~ ^[0-9]+$ ]] || [[ $KEEP_COUNT -lt 1 ]]; then
        print_color "$RED" "Error: --keep must be a positive integer"
        exit 1
    fi
    
    log "INFO" "Starting backup archival (Keep: $KEEP_COUNT, Dry-run: $DRY_RUN)"
    
    # Check dependencies
    check_dependencies
    
    # Create archive structure
    create_archive_structure
    
    # Process each backup folder
    echo ""
    for folder in "${BACKUP_FOLDERS[@]}"; do
        archive_old_backups "$folder"
    done
    
    # Print summary
    echo ""
    print_summary
    
    log "INFO" "Backup archival completed"
    print_color "$GREEN" "\n✓ Backup archival completed successfully!"
}

# Run main function
main "$@"
