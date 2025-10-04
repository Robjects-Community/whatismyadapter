#!/bin/bash

# WillowCMS Log Checksum Utility
# Generates and verifies SHA-256 checksums for log files
# Supports verification mode for log integrity checking

set -euo pipefail

# Configuration
readonly LOG_PREFIX="[log-checksum]"
readonly CHECKSUM_SUFFIX=".sha256"

# Target log directories and files
readonly LOG_DIRS=(
    "/var/log/nginx"
    "/var/www/willowcms/logs"
)

readonly LOG_PATTERNS=(
    "*.log"
    "*.log.*"  # Rotated logs
)

# Logging functions
log_info() {
    echo "${LOG_PREFIX} INFO: $*" >&1
}

log_error() {
    echo "${LOG_PREFIX} ERROR: $*" >&2
}

log_debug() {
    if [[ "${DEBUG:-false}" == "true" ]]; then
        echo "${LOG_PREFIX} DEBUG: $*" >&1
    fi
}

# Usage information
show_usage() {
    cat << EOF
Usage: $0 [OPTIONS]

WillowCMS Log Checksum Utility

OPTIONS:
    -g, --generate      Generate checksums for all log files
    -v, --verify        Verify existing checksums
    -c, --clean         Remove old checksum files
    -d, --directory DIR Add custom directory to check
    -h, --help         Show this help message

EXAMPLES:
    $0 --generate                    # Generate checksums for all logs
    $0 --verify                      # Verify all existing checksums
    $0 --directory /custom/logs      # Add custom log directory
    $0 --generate --clean            # Clean old checksums and generate new ones

DESCRIPTION:
    This utility creates SHA-256 checksum files alongside log files for integrity
    verification. Checksum files have a ${CHECKSUM_SUFFIX} extension.
    
    Designed for on-demand use via docker compose exec or cron jobs.
    Do not run continuously to avoid I/O overhead.

EOF
}

# Find all log files in specified directories
find_log_files() {
    local files=()
    
    for dir in "${LOG_DIRS[@]}" "${CUSTOM_DIRS[@]:-}"; do
        if [[ ! -d "$dir" ]]; then
            log_debug "Directory not found: $dir"
            continue
        fi
        
        log_debug "Scanning directory: $dir"
        
        for pattern in "${LOG_PATTERNS[@]}"; do
            while IFS= read -r -d '' file; do
                if [[ -f "$file" && -r "$file" ]]; then
                    files+=("$file")
                    log_debug "Found log file: $file"
                fi
            done < <(find "$dir" -name "$pattern" -type f -print0 2>/dev/null || true)
        done
    done
    
    printf '%s\n' "${files[@]}" | sort -u
}

# Generate checksum for a single file
generate_checksum() {
    local log_file="$1"
    local checksum_file="${log_file}${CHECKSUM_SUFFIX}"
    
    if [[ ! -f "$log_file" ]]; then
        log_error "Log file not found: $log_file"
        return 1
    fi
    
    if [[ ! -r "$log_file" ]]; then
        log_error "Cannot read log file: $log_file"
        return 1
    fi
    
    log_debug "Generating checksum for: $log_file"
    
    local checksum
    if checksum=$(sha256sum "$log_file" 2>/dev/null); then
        echo "$checksum" > "$checksum_file"
        log_info "Generated checksum: $(basename "$log_file") -> $(basename "$checksum_file")"
        return 0
    else
        log_error "Failed to generate checksum for: $log_file"
        return 1
    fi
}

# Verify checksum for a single file
verify_checksum() {
    local log_file="$1"
    local checksum_file="${log_file}${CHECKSUM_SUFFIX}"
    
    if [[ ! -f "$checksum_file" ]]; then
        log_debug "No checksum file for: $(basename "$log_file")"
        return 2  # No checksum file (not an error)
    fi
    
    if [[ ! -f "$log_file" ]]; then
        log_error "Log file missing but checksum exists: $log_file"
        return 1
    fi
    
    log_debug "Verifying checksum for: $log_file"
    
    # Read stored checksum
    local stored_checksum
    if ! stored_checksum=$(cat "$checksum_file" 2>/dev/null); then
        log_error "Cannot read checksum file: $checksum_file"
        return 1
    fi
    
    # Calculate current checksum
    local current_checksum
    if ! current_checksum=$(sha256sum "$log_file" 2>/dev/null); then
        log_error "Cannot calculate checksum for: $log_file"
        return 1
    fi
    
    # Compare checksums
    if [[ "$stored_checksum" == "$current_checksum" ]]; then
        log_info "Checksum verified: $(basename "$log_file") ✓"
        return 0
    else
        log_error "Checksum mismatch: $(basename "$log_file") ✗"
        log_error "  Stored:  $stored_checksum"
        log_error "  Current: $current_checksum"
        return 1
    fi
}

# Clean old checksum files
clean_checksums() {
    local cleaned=0
    
    for dir in "${LOG_DIRS[@]}" "${CUSTOM_DIRS[@]:-}"; do
        if [[ ! -d "$dir" ]]; then
            continue
        fi
        
        log_debug "Cleaning checksums in: $dir"
        
        while IFS= read -r -d '' checksum_file; do
            if rm -f "$checksum_file" 2>/dev/null; then
                log_info "Removed checksum file: $(basename "$checksum_file")"
                ((cleaned++))
            fi
        done < <(find "$dir" -name "*${CHECKSUM_SUFFIX}" -type f -print0 2>/dev/null || true)
    done
    
    log_info "Cleaned $cleaned checksum files"
}

# Generate checksums for all log files
generate_all_checksums() {
    local total=0
    local success=0
    local failed=0
    
    log_info "Generating checksums for all log files"
    
    while IFS= read -r log_file; do
        if [[ -z "$log_file" ]]; then
            continue
        fi
        
        ((total++))
        
        if generate_checksum "$log_file"; then
            ((success++))
        else
            ((failed++))
        fi
    done < <(find_log_files)
    
    log_info "Checksum generation complete: $success success, $failed failed, $total total"
    
    if [[ $failed -gt 0 ]]; then
        return 1
    fi
    
    return 0
}

# Verify all existing checksums
verify_all_checksums() {
    local total=0
    local success=0
    local failed=0
    local missing=0
    
    log_info "Verifying all existing checksums"
    
    while IFS= read -r log_file; do
        if [[ -z "$log_file" ]]; then
            continue
        fi
        
        ((total++))
        
        local result
        verify_checksum "$log_file"
        result=$?
        
        case $result in
            0) ((success++)) ;;
            1) ((failed++)) ;;
            2) ((missing++)) ;;
        esac
    done < <(find_log_files)
    
    log_info "Checksum verification complete: $success verified, $failed failed, $missing no checksum, $total total"
    
    if [[ $failed -gt 0 ]]; then
        log_error "Some checksums failed verification"
        return 1
    fi
    
    return 0
}

# Main function
main() {
    local action=""
    local clean_first=false
    declare -a CUSTOM_DIRS=()
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -g|--generate)
                action="generate"
                shift
                ;;
            -v|--verify)
                action="verify"
                shift
                ;;
            -c|--clean)
                clean_first=true
                shift
                ;;
            -d|--directory)
                if [[ -n "${2:-}" ]]; then
                    CUSTOM_DIRS+=("$2")
                    shift 2
                else
                    log_error "Directory argument required for -d/--directory"
                    exit 1
                fi
                ;;
            -h|--help)
                show_usage
                exit 0
                ;;
            *)
                log_error "Unknown argument: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Validate action
    if [[ -z "$action" ]]; then
        log_error "Action required: --generate or --verify"
        show_usage
        exit 1
    fi
    
    # Clean old checksums if requested
    if [[ "$clean_first" == true ]]; then
        clean_checksums
    fi
    
    # Execute action
    case "$action" in
        generate)
            if generate_all_checksums; then
                log_info "All checksums generated successfully"
                exit 0
            else
                log_error "Some checksums failed to generate"
                exit 1
            fi
            ;;
        verify)
            if verify_all_checksums; then
                log_info "All checksums verified successfully"
                exit 0
            else
                log_error "Some checksums failed verification"
                exit 1
            fi
            ;;
        *)
            log_error "Invalid action: $action"
            exit 1
            ;;
    esac
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi