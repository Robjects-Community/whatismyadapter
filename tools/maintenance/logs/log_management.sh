#!/bin/bash
# WillowCMS Docker Service Log Management Tool
# Provides rotation, cleanup, archival, and monitoring for service-specific logs

set -euo pipefail

# Configuration
LOGS_BASE_DIR="infrastructure/docker/logs"
ARCHIVE_DIR="$LOGS_BASE_DIR/_archive"
SHARED_DIR="$LOGS_BASE_DIR/_shared" 
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../" && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Log services and their subdirectories
declare -A SERVICE_DIRS=(
    ["willowcms"]="nginx php cakephp supervisor"
    ["mysql"]="query error slow binlog"
    ["jenkins"]="build system audit"
    ["mailpit"]="smtp system"
    ["phpmyadmin"]="access error"
    ["redis-commander"]="access error"
)

print_header() {
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}                   WillowCMS Docker Log Management                      ${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
    echo
}

print_usage() {
    echo "Usage: $0 {rotate|clean|archive|monitor|size|list|aggregate}"
    echo
    echo "Commands:"
    echo "  rotate    - Rotate logs for all services (keeps last 10 files)"
    echo "  clean     - Clean old logs (removes files older than 30 days)"
    echo "  archive   - Archive logs to _archive directory with timestamp"
    echo "  monitor   - Monitor log sizes and growth"
    echo "  size      - Show disk usage by service"
    echo "  list      - List all log files by service"
    echo "  aggregate - Create aggregated logs in _shared directory"
    echo
    echo "Environment variables:"
    echo "  LOG_RETENTION_DAYS=30    - Days to keep logs before cleanup"
    echo "  LOG_ROTATION_COUNT=10    - Number of rotated files to keep"
    echo "  LOG_SIZE_LIMIT=100M      - Maximum size before rotation warning"
}

ensure_directories() {
    cd "$PROJECT_ROOT"
    
    # Ensure base directories exist
    mkdir -p "$ARCHIVE_DIR" "$SHARED_DIR"
    
    # Ensure service directories exist
    for service in "${!SERVICE_DIRS[@]}"; do
        service_path="$LOGS_BASE_DIR/$service"
        mkdir -p "$service_path"
        
        # Create subdirectories for each service
        for subdir in ${SERVICE_DIRS[$service]}; do
            mkdir -p "$service_path/$subdir"
        done
    done
    
    echo -e "${GREEN}✓ Directory structure verified${NC}"
}

rotate_logs() {
    local rotation_count=${LOG_ROTATION_COUNT:-10}
    echo -e "${CYAN}📄 Rotating logs (keeping $rotation_count versions)...${NC}"
    
    cd "$PROJECT_ROOT"
    
    # Find all log files and rotate them
    find "$LOGS_BASE_DIR" -name "*.log" -type f | while read -r logfile; do
        if [[ -s "$logfile" ]] && [[ ! "$logfile" =~ /_archive/|/_shared/ ]]; then
            local base_name="${logfile%.log}"
            
            # Rotate existing numbered files
            for ((i=$((rotation_count-1)); i>=1; i--)); do
                if [[ -f "${base_name}.log.$i" ]]; then
                    mv "${base_name}.log.$i" "${base_name}.log.$((i+1))"
                fi
            done
            
            # Move current log to .1
            cp "$logfile" "${base_name}.log.1"
            > "$logfile"  # Truncate original log
            
            echo -e "  ${GREEN}✓${NC} Rotated: $(basename "$logfile")"
        fi
    done
    
    echo -e "${GREEN}✓ Log rotation completed${NC}"
}

clean_old_logs() {
    local retention_days=${LOG_RETENTION_DAYS:-30}
    echo -e "${YELLOW}🧹 Cleaning logs older than $retention_days days...${NC}"
    
    cd "$PROJECT_ROOT"
    
    local deleted_count=0
    
    # Clean rotated logs
    find "$LOGS_BASE_DIR" -name "*.log.*" -type f -mtime +$retention_days | while read -r oldfile; do
        rm -f "$oldfile"
        echo -e "  ${RED}✗${NC} Removed: $(basename "$oldfile")"
        ((deleted_count++))
    done
    
    # Clean archived logs older than double retention period
    find "$ARCHIVE_DIR" -name "*.tar.gz" -type f -mtime +$((retention_days*2)) | while read -r oldarchive; do
        rm -f "$oldarchive"
        echo -e "  ${RED}✗${NC} Removed archived: $(basename "$oldarchive")"
        ((deleted_count++))
    done
    
    echo -e "${GREEN}✓ Cleanup completed${NC}"
}

archive_logs() {
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    echo -e "${PURPLE}📦 Archiving logs to _archive/$timestamp...${NC}"
    
    cd "$PROJECT_ROOT"
    
    local archive_name="logs_archive_$timestamp.tar.gz"
    local archive_path="$ARCHIVE_DIR/$archive_name"
    
    # Create archive of all current logs
    find "$LOGS_BASE_DIR" -name "*.log" -type f ! -path "*/_archive/*" ! -path "*/_shared/*" -print0 | \
        tar -czf "$archive_path" --null -T -
    
    if [[ -f "$archive_path" ]]; then
        local archive_size=$(du -h "$archive_path" | cut -f1)
        echo -e "${GREEN}✓ Archive created: $archive_name ($archive_size)${NC}"
        
        # Create manifest
        echo "# Log Archive Manifest - $timestamp" > "$ARCHIVE_DIR/${archive_name%.tar.gz}.manifest"
        echo "# Created: $(date)" >> "$ARCHIVE_DIR/${archive_name%.tar.gz}.manifest"
        tar -tzf "$archive_path" >> "$ARCHIVE_DIR/${archive_name%.tar.gz}.manifest"
    else
        echo -e "${RED}✗ Archive creation failed${NC}"
    fi
}

monitor_logs() {
    local size_limit=${LOG_SIZE_LIMIT:-100M}
    echo -e "${CYAN}📊 Monitoring log sizes and growth...${NC}"
    
    cd "$PROJECT_ROOT"
    
    echo -e "\n${BLUE}Current Log Status by Service:${NC}"
    echo "=================================="
    
    for service in "${!SERVICE_DIRS[@]}"; do
        local service_path="$LOGS_BASE_DIR/$service"
        if [[ -d "$service_path" ]]; then
            local service_size=$(du -sh "$service_path" 2>/dev/null | cut -f1)
            echo -e "\n${PURPLE}$service${NC} ($service_size):"
            
            for subdir in ${SERVICE_DIRS[$service]}; do
                local subdir_path="$service_path/$subdir"
                if [[ -d "$subdir_path" ]]; then
                    find "$subdir_path" -name "*.log" -type f | while read -r logfile; do
                        if [[ -f "$logfile" ]]; then
                            local file_size=$(du -h "$logfile" | cut -f1)
                            local mod_time=$(date -r "$logfile" "+%Y-%m-%d %H:%M")
                            echo "  $(basename "$logfile"): $file_size (modified: $mod_time)"
                        fi
                    done
                fi
            done
        fi
    done
    
    echo -e "\n${BLUE}Large Files (>10MB):${NC}"
    echo "===================="
    find "$LOGS_BASE_DIR" -name "*.log" -type f -size +10M -exec du -h {} + | sort -hr
}

show_sizes() {
    echo -e "${CYAN}💾 Disk usage by service:${NC}"
    
    cd "$PROJECT_ROOT"
    
    for service in "${!SERVICE_DIRS[@]}"; do
        local service_path="$LOGS_BASE_DIR/$service"
        if [[ -d "$service_path" ]]; then
            local size=$(du -sh "$service_path" | cut -f1)
            printf "  %-20s %s\n" "$service:" "$size"
        fi
    done
    
    echo
    local total_size=$(du -sh "$LOGS_BASE_DIR" | cut -f1)
    echo -e "${GREEN}Total logs size: $total_size${NC}"
}

list_logs() {
    echo -e "${CYAN}📋 Log files by service:${NC}"
    
    cd "$PROJECT_ROOT"
    
    for service in "${!SERVICE_DIRS[@]}"; do
        local service_path="$LOGS_BASE_DIR/$service"
        if [[ -d "$service_path" ]]; then
            echo -e "\n${PURPLE}$service:${NC}"
            find "$service_path" -name "*.log*" -type f | sort | while read -r logfile; do
                local rel_path=${logfile#$LOGS_BASE_DIR/$service/}
                local size=$(du -h "$logfile" | cut -f1)
                printf "  %-40s %s\n" "$rel_path" "$size"
            done
        fi
    done
}

aggregate_logs() {
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    echo -e "${CYAN}📊 Creating aggregated logs...${NC}"
    
    cd "$PROJECT_ROOT"
    
    # Aggregate error logs
    echo "# Aggregated Error Logs - $timestamp" > "$SHARED_DIR/all_errors_$timestamp.log"
    find "$LOGS_BASE_DIR" -name "*error*.log" -type f ! -path "*/_archive/*" ! -path "*/_shared/*" -exec cat {} + >> "$SHARED_DIR/all_errors_$timestamp.log"
    
    # Aggregate access logs
    echo "# Aggregated Access Logs - $timestamp" > "$SHARED_DIR/all_access_$timestamp.log" 
    find "$LOGS_BASE_DIR" -name "*access*.log" -type f ! -path "*/_archive/*" ! -path "*/_shared/*" -exec cat {} + >> "$SHARED_DIR/all_access_$timestamp.log"
    
    # Create summary
    echo "# Log Summary - $timestamp" > "$SHARED_DIR/summary_$timestamp.txt"
    echo "Generated: $(date)" >> "$SHARED_DIR/summary_$timestamp.txt"
    echo >> "$SHARED_DIR/summary_$timestamp.txt"
    
    for service in "${!SERVICE_DIRS[@]}"; do
        local service_path="$LOGS_BASE_DIR/$service"
        if [[ -d "$service_path" ]]; then
            echo "## $service" >> "$SHARED_DIR/summary_$timestamp.txt"
            find "$service_path" -name "*.log" -type f -exec wc -l {} + | sort -nr >> "$SHARED_DIR/summary_$timestamp.txt"
            echo >> "$SHARED_DIR/summary_$timestamp.txt"
        fi
    done
    
    echo -e "${GREEN}✓ Aggregated logs created in _shared/${NC}"
}

main() {
    print_header
    
    if [[ $# -eq 0 ]]; then
        print_usage
        exit 1
    fi
    
    ensure_directories
    
    case "$1" in
        rotate)
            rotate_logs
            ;;
        clean)
            clean_old_logs
            ;;
        archive)
            archive_logs
            ;;
        monitor)
            monitor_logs
            ;;
        size)
            show_sizes
            ;;
        list)
            list_logs
            ;;
        aggregate)
            aggregate_logs
            ;;
        *)
            echo -e "${RED}Error: Unknown command '$1'${NC}"
            echo
            print_usage
            exit 1
            ;;
    esac
}

# Check if running directly (not sourced)
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi