#!/usr/bin/env bash
#
# organize_deployment_backups.sh - Simple version
# 
# Organizes deployment cleanup backup directories
#

set -euo pipefail

# Default configuration
SRC="."
DEST=".backups/deployment-cleanup"
WIDTH=4
DRY_RUN=1

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}[INFO]${NC} $*"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $*" >&2
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $*" >&2
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $*"
}

# Parse arguments
while [ $# -gt 0 ]; do
    case "$1" in
        --source)
            SRC="$2"
            shift 2
            ;;
        --dest)
            DEST="$2"
            shift 2
            ;;
        --width)
            WIDTH="$2"
            shift 2
            ;;
        --apply)
            DRY_RUN=0
            shift
            ;;
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        *)
            echo "Unknown option: $1" >&2
            exit 1
            ;;
    esac
done

# Ensure destination exists
mkdir -p "$DEST"

log_info "Configuration:"
log_info "  Source: $SRC"
log_info "  Destination: $DEST"
log_info "  Number width: $WIDTH"
if [ $DRY_RUN -eq 1 ]; then
    log_info "  Mode: DRY RUN (preview only)"
else
    log_info "  Mode: APPLY (actual migration)"
fi
echo

# Find source directories
echo "Finding source directories..."
SOURCE_DIRS=()
if [ "$SRC" = "." ]; then
    pattern="deployment-cleanup-backup-*"
else
    pattern="$SRC/deployment-cleanup-backup-*"
fi

# Use a simple for loop with glob
shopt -s nullglob
for dir in $pattern; do
    if [ -d "$dir" ]; then
        SOURCE_DIRS+=("$dir")
    fi
done
shopt -u nullglob

log_info "Found ${#SOURCE_DIRS[@]} source directories"

if [ ${#SOURCE_DIRS[@]} -eq 0 ]; then
    log_warn "No deployment cleanup backup directories found in source"
    exit 0
fi

# Get last number from destination
get_last_number() {
    local last_num=0
    for existing in "$DEST"/deployment-cleanup-backup-[0-9][0-9][0-9][0-9]-*; do
        if [ -d "$existing" ]; then
            local name=$(basename "$existing")
            local num=$(echo "$name" | sed -nE 's/^deployment-cleanup-backup-([0-9]{4})-.*/\1/p')
            if [ -n "$num" ] && [ "$((10#$num))" -gt "$last_num" ]; then
                last_num=$((10#$num))
            fi
        fi
    done
    echo "$last_num"
}

last_num=$(get_last_number)
log_info "Last used number: $(printf "%04d" $last_num)"

# Create temp file for sorting
temp_file=$(mktemp)

# Build sortable list
for dir in "${SOURCE_DIRS[@]}"; do
    name=$(basename "$dir")
    # Extract timestamp from name
    ts=$(echo "$name" | sed -nE 's/.*([0-9]{8}_[0-9]{6}).*/\1/p')
    
    if [ -n "$ts" ]; then
        # Has timestamp, use it for sorting
        echo "0|$ts|$name|$dir" >> "$temp_file"
    else
        # No timestamp, use mtime
        if stat -f "%m" "$dir" >/dev/null 2>&1; then
            # BSD stat (macOS)
            mtime=$(stat -f "%m" "$dir")
        else
            # GNU stat (Linux)
            mtime=$(stat -c "%Y" "$dir" 2>/dev/null || echo "0")
        fi
        echo "1|$mtime|$name|$dir" >> "$temp_file"
    fi
done

# Sort and process
current_num=$last_num
migration_log="${DEST}/MIGRATION-$(date +%Y%m%d_%H%M%S).log"

if [ $DRY_RUN -eq 0 ]; then
    echo "# Migration Log - $(date)" > "$migration_log"
    echo "" >> "$migration_log"
fi

sort -t '|' -k1,1n -k2,2 "$temp_file" | while IFS='|' read -r priority sort_key name path; do
    current_num=$((current_num + 1))
    padded_num=$(printf "%0${WIDTH}d" "$current_num")
    
    # Extract or generate timestamp
    ts=$(echo "$name" | sed -nE 's/.*([0-9]{8}_[0-9]{6}).*/\1/p')
    if [ -z "$ts" ]; then
        ts=$(date +%Y%m%d_%H%M%S)
    fi
    
    new_name="deployment-cleanup-backup-${padded_num}-${ts}"
    new_path="$DEST/$new_name"
    
    # Handle collision
    counter=1
    while [ -e "$new_path" ]; do
        new_name="deployment-cleanup-backup-${padded_num}-${ts}-dup${counter}"
        new_path="$DEST/$new_name"
        counter=$((counter + 1))
    done
    
    if [ -n "$ts" ] && echo "$ts" | grep -qE '^[0-9]{8}_[0-9]{6}$'; then
        # Format timestamp for display
        date_part="${ts%_*}"
        time_part="${ts#*_}"
        formatted_time="${date_part:0:4}-${date_part:4:2}-${date_part:6:2} ${time_part:0:2}:${time_part:2:2}:${time_part:4:2}"
        log_info "MOVE: $name -> $new_name (timestamp: $formatted_time)"
    else
        log_info "MOVE: $name -> $new_name"
    fi
    
    if [ $DRY_RUN -eq 0 ]; then
        if mv "$path" "$new_path"; then
            echo "$path -> $new_path" >> "$migration_log"
            log_success "Moved: $name"
        else
            log_error "Failed to move: $path"
            exit 1
        fi
    fi
done

rm -f "$temp_file"

echo
if [ $DRY_RUN -eq 1 ]; then
    log_info "Dry run completed. Use --apply to perform actual migration."
else
    log_success "Migration completed successfully!"
    log_info "Migration log: $migration_log"
    
    # Create index.json
    index_file="$DEST/index.json"
    log_info "Creating index.json..."
    
    echo "[" > "$index_file"
    first=1
    
    for dir_path in "$DEST"/deployment-cleanup-backup-[0-9][0-9][0-9][0-9]-*; do
        if [ -d "$dir_path" ]; then
            dir_name=$(basename "$dir_path")
            number=$(echo "$dir_name" | sed -nE "s/^deployment-cleanup-backup-([0-9]{4})-.*/\1/p")
            timestamp=$(echo "$dir_name" | sed -nE "s/^deployment-cleanup-backup-[0-9]{4}-([0-9]{8}_[0-9]{6}).*/\1/p")
            
            # Get size
            size_kb=$(du -sk "$dir_path" 2>/dev/null | awk '{print $1}' || echo "0")
            
            # Get checksum
            if command -v shasum >/dev/null 2>&1; then
                checksum=$(find "$dir_path" -type f -print0 2>/dev/null | sort -z | xargs -0 shasum -a 256 2>/dev/null | shasum -a 256 | awk '{print $1}' || echo "unknown")
            else
                checksum="unknown"
            fi
            
            [ $first -eq 0 ] && echo "," >> "$index_file"
            printf '    {"name": "%s", "number": "%s", "timestamp": "%s", "size_kb": %s, "checksum": "%s"}' \
                "$dir_name" "$number" "$timestamp" "$size_kb" "$checksum" >> "$index_file"
            first=0
        fi
    done
    
    echo "" >> "$index_file"
    echo "]" >> "$index_file"
    
    log_success "Index created: $index_file"
fi

echo
log_info "Organization complete!"