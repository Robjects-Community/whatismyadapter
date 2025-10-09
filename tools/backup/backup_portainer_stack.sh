#!/usr/bin/env bash
# WillowCMS Portainer Stack Backup Script
# Creates numbered backups with checksums for verification
# Follows user preferences for organization and security

set -euo pipefail

# Configuration
ROOT="/Volumes/1TB_DAVINCI/docker/willow"
SRC_DIR="${ROOT}/portainer-stacks"
ENV_FILE="${ROOT}/stack.env"
DEST_ROOT="${ROOT}/tools/backup/stack"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if source directory exists
if [ ! -d "$SRC_DIR" ]; then
    log_error "Source directory not found: $SRC_DIR"
    exit 1
fi

# Check if required files exist
if [ ! -f "$SRC_DIR/docker-compose.yml" ]; then
    log_error "docker-compose.yml not found in $SRC_DIR"
    exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
    log_error "stack.env not found at $ENV_FILE"
    exit 1
fi

# Create backup directory
mkdir -p "$DEST_ROOT"

# Generate sequential backup number
count=$(ls -1 "$DEST_ROOT" 2>/dev/null | wc -l | tr -d ' ')
seq=$(printf "%03d" $((count+1)))
ts=$(date +%Y%m%d_%H%M%S)
dest="${DEST_ROOT}/${seq}_${ts}"

log "Creating backup directory: $dest"
mkdir -p "$dest"

# Copy files
log "Copying Portainer stack files..."
cp "$SRC_DIR/docker-compose.yml" "$dest/"
cp "$ENV_FILE" "$dest/"

# Generate checksums
log "Generating SHA-256 checksums..."
(cd "$dest" && shasum -a 256 * > SHA256SUMS)

# Create backup metadata
log "Creating backup metadata..."
cat > "$dest/BACKUP_INFO.md" << EOF
# WillowCMS Portainer Stack Backup

**Backup ID:** ${seq}_${ts}
**Created:** $(date '+%Y-%m-%d %H:%M:%S %Z')
**Git Branch:** $(git branch --show-current 2>/dev/null || echo "unknown")
**Git Commit:** $(git rev-parse --short HEAD 2>/dev/null || echo "unknown")

## Files Backed Up
- docker-compose.yml (Portainer stack configuration)
- stack.env (Environment variables)
- SHA256SUMS (Checksum verification file)

## Verification
To verify backup integrity:
\`\`\`bash
cd "$dest"
shasum -c SHA256SUMS
\`\`\`

## Restoration
To restore this backup:
\`\`\`bash
cp "$dest/docker-compose.yml" "$SRC_DIR/"
cp "$dest/stack.env" "$ROOT/"
\`\`\`

**Note:** Always verify checksums before restoration and ensure Portainer services are stopped.
EOF

# Verify checksums immediately
log "Verifying backup integrity..."
if (cd "$dest" && shasum -c SHA256SUMS >/dev/null 2>&1); then
    log_success "Backup integrity verified successfully"
else
    log_error "Backup integrity verification failed!"
    exit 1
fi

# Show backup summary
backup_size=$(du -sh "$dest" | cut -f1)
log_success "Backup completed successfully!"
echo
echo "📁 Backup Location: $dest"
echo "📊 Backup Size: $backup_size"
echo "🔢 Backup Number: $seq"
echo "⏰ Timestamp: $ts"
echo
echo "Files backed up:"
ls -la "$dest" | grep -v "^total" | awk '{printf "  %-20s %s\n", $9, $5 " bytes"}'
echo
echo "To verify checksums: shasum -c \"$dest/SHA256SUMS\""
echo "To list all backups: ls -la \"$DEST_ROOT\""