#!/bin/bash

# WillowCMS Helper Files Refactoring Script
# Refactors the cluttered helper-files directory into a clean, organized structure

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Configuration
HELPER_DIR="./helper-files(use-only-if-you-get-lost)"
BACKUP_PREFIX="helper-files-backup"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_PREFIX}-${TIMESTAMP}.tar.gz"

# Logging functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARN] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

success() {
    echo -e "${PURPLE}[SUCCESS] $1${NC}"
}

# Check if helper-files directory exists
if [[ ! -d "$HELPER_DIR" ]]; then
    error "Helper-files directory not found: $HELPER_DIR"
    exit 1
fi

echo -e "${GREEN}"
echo "🗂️ WillowCMS Helper Files Refactoring"
echo "======================================"
echo -e "${NC}"
log "Starting comprehensive helper-files refactoring..."

# Create backup first
log "Creating backup of helper-files directory..."
tar -czf "$BACKUP_FILE" "$HELPER_DIR"
if [ $? -eq 0 ]; then
    success "Backup created: $BACKUP_FILE"
else
    error "Backup creation failed!"
    exit 1
fi

# Create new directory structure (compatible with reorganization)
log "Creating new directory structure..."
mkdir -p docs/{development,architecture,archive,legacy}
mkdir -p tools/scripts
mkdir -p tools/legacy-helpers  
mkdir -p storage/backups/docker-compose/{current,historical}
mkdir -p assets/presentations
success "Directory structure created"

# PHASE 1: DELETE OBSOLETE FILES
log "🔥 Phase 1: Removing obsolete files..."

deleted_count=0

# Delete archived documentation
if [ -d "$HELPER_DIR/archived-docs-20250917_202001/" ]; then
    rm -rf "$HELPER_DIR/archived-docs-20250917_202001/"
    deleted_count=$((deleted_count + 7))  # 7 archived docs
    info "Removed archived documentation"
fi

# Delete single backup files
if [ -f "$HELPER_DIR/docker-compose.yml.backup.20250917110452" ]; then
    rm -f "$HELPER_DIR/docker-compose.yml.backup.20250917110452"
    deleted_count=$((deleted_count + 1))
    info "Removed obsolete backup file"
fi

# Delete temporary files
if [ -d "$HELPER_DIR/temp-files/" ]; then
    temp_count=$(find "$HELPER_DIR/temp-files/" -type f | wc -l)
    rm -rf "$HELPER_DIR/temp-files/"
    deleted_count=$((deleted_count + temp_count))
    info "Removed $temp_count temporary files"
fi

success "Phase 1 Complete: $deleted_count obsolete files removed"

# PHASE 2: INTEGRATE VALUABLE CONTENT
log "✅ Phase 2: Integrating valuable content..."

integrated_count=0

# Development Documentation
if [ -f "$HELPER_DIR/docs/README.md" ]; then
    mv "$HELPER_DIR/docs/README.md" "docs/legacy/COMPREHENSIVE_README.md"
    integrated_count=$((integrated_count + 1))
fi

if [ -f "$HELPER_DIR/docs/HELPER.md" ]; then
    mv "$HELPER_DIR/docs/HELPER.md" "docs/architecture/DIRECTORY_STRUCTURE.md"
    integrated_count=$((integrated_count + 1))
fi

if [ -f "$HELPER_DIR/docs/TROUBLESHOOTING.md" ]; then
    mv "$HELPER_DIR/docs/TROUBLESHOOTING.md" "docs/development/TROUBLESHOOTING.md"
    integrated_count=$((integrated_count + 1))
fi

if [ -f "$HELPER_DIR/docs/DeveloperGuide.md" ]; then
    mv "$HELPER_DIR/docs/DeveloperGuide.md" "docs/development/DEVELOPER_GUIDE.md"
    integrated_count=$((integrated_count + 1))
fi

if [ -f "$HELPER_DIR/docs/DOCKER_ENV_README.md" ]; then
    mv "$HELPER_DIR/docs/DOCKER_ENV_README.md" "docs/development/DOCKER_ENVIRONMENT.md"
    integrated_count=$((integrated_count + 1))
fi

info "Integrated $integrated_count development documentation files"

# Development Scripts
script_count=0
if [ -f "$HELPER_DIR/scripts/README.md" ]; then
    mv "$HELPER_DIR/scripts/README.md" "tools/scripts/AUTOMATION_SCRIPTS_GUIDE.md"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/backup-and-reset.sh" ]; then
    mv "$HELPER_DIR/scripts/backup-and-reset.sh" "tools/scripts/backup_and_reset.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/cleanup.sh" ]; then
    mv "$HELPER_DIR/scripts/cleanup.sh" "tools/scripts/cleanup.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/docker-compose.sh" ]; then
    mv "$HELPER_DIR/scripts/docker-compose.sh" "tools/scripts/docker_compose_wrapper.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/health-check.sh" ]; then
    mv "$HELPER_DIR/scripts/health-check.sh" "tools/scripts/health_check.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/restart-environment.sh" ]; then
    mv "$HELPER_DIR/scripts/restart-environment.sh" "tools/scripts/restart_environment.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/start_willow_env.sh.bk" ]; then
    mv "$HELPER_DIR/scripts/start_willow_env.sh.bk" "tools/legacy-helpers/start_willow_env_backup.sh"
    script_count=$((script_count + 1))
fi

if [ -f "$HELPER_DIR/scripts/validate-env.sh" ]; then
    mv "$HELPER_DIR/scripts/validate-env.sh" "tools/scripts/validate_environment.sh"
    script_count=$((script_count + 1))
fi

info "Integrated $script_count development scripts"

# Docker Backups
backup_count=0
if [ -d "$HELPER_DIR/docker-backups/current-backup/" ]; then
    mv "$HELPER_DIR/docker-backups/current-backup/" "storage/backups/docker-compose/current/"
    backup_count=$((backup_count + 1))
fi

if [ -d "$HELPER_DIR/docker-backups/historical/" ]; then
    mv "$HELPER_DIR/docker-backups/historical/" "storage/backups/docker-compose/historical/"
    backup_count=$((backup_count + 1))
fi

if [ -f "$HELPER_DIR/docker-backups/README.md" ]; then
    mv "$HELPER_DIR/docker-backups/README.md" "storage/backups/docker-compose/README.md"
    backup_count=$((backup_count + 1))
fi

info "Integrated $backup_count docker backup components"

integrated_count=$((integrated_count + script_count + backup_count))
success "Phase 2 Complete: $integrated_count valuable files integrated"

# PHASE 3: REFACTOR AND MODERNIZE
log "🔄 Phase 3: Refactoring documentation..."

refactored_count=0

# Architecture docs
if [ -f "$HELPER_DIR/docs/REFACTORING_PLAN.md" ]; then
    mv "$HELPER_DIR/docs/REFACTORING_PLAN.md" "docs/architecture/LEGACY_REFACTORING_PLAN.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/ROUTE_OPTIMIZATION_RECOMMENDATIONS.md" ]; then
    mv "$HELPER_DIR/docs/ROUTE_OPTIMIZATION_RECOMMENDATIONS.md" "docs/development/ROUTE_OPTIMIZATION.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/simple-products-REFACTORING-plan.md" ]; then
    mv "$HELPER_DIR/docs/simple-products-REFACTORING-plan.md" "docs/development/PRODUCT_REFACTORING.md"
    refactored_count=$((refactored_count + 1))
fi

# Process documentation
if [ -f "$HELPER_DIR/docs/CLEANUP_PROCEDURES.md" ]; then
    mv "$HELPER_DIR/docs/CLEANUP_PROCEDURES.md" "docs/development/CLEANUP_PROCEDURES.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/ENVIRONMENT_RESTART_PROCEDURES.md" ]; then
    mv "$HELPER_DIR/docs/ENVIRONMENT_RESTART_PROCEDURES.md" "docs/development/RESTART_PROCEDURES.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/SHUTDOWN_PROCEDURES.md" ]; then
    mv "$HELPER_DIR/docs/SHUTDOWN_PROCEDURES.md" "docs/development/SHUTDOWN_PROCEDURES.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/VERIFICATION_CHECKLIST.md" ]; then
    mv "$HELPER_DIR/docs/VERIFICATION_CHECKLIST.md" "docs/development/VERIFICATION_CHECKLIST.md"
    refactored_count=$((refactored_count + 1))
fi

# Development guides
if [ -f "$HELPER_DIR/docs/docker-compose-override-guide.md" ]; then
    mv "$HELPER_DIR/docs/docker-compose-override-guide.md" "docs/development/DOCKER_COMPOSE_CUSTOMIZATION.md"
    refactored_count=$((refactored_count + 1))
fi

if [ -f "$HELPER_DIR/docs/docker-restart-guide.md" ]; then
    mv "$HELPER_DIR/docs/docker-restart-guide.md" "docs/development/DOCKER_RESTART_GUIDE.md"
    refactored_count=$((refactored_count + 1))
fi

success "Phase 3 Complete: $refactored_count files refactored"

# PHASE 4: ARCHIVE REFERENCE MATERIAL
log "📚 Phase 4: Archiving reference material..."

archived_count=0

# Historical docs
archive_files=(
    "AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md:AI_IMPROVEMENTS_PLAN.md"
    "AI_METRICS_IMPLEMENTATION_SUMMARY.md:AI_METRICS_SUMMARY.md"
    "AI_METRICS_STATUS_REPORT.md:AI_METRICS_STATUS.md"
    "CLAUDE.md:CLAUDE_INTERACTION_GUIDE.md"
    "CLEANUP_SUMMARY.md:CLEANUP_SUMMARY.md"
    "REALTIME_METRICS_IMPLEMENTATION.md:REALTIME_METRICS.md"
    "TEST_REFACTORING_SUMMARY.md:TEST_REFACTORING.md"
    "BETA-RELEASES-INFO.md:BETA_RELEASES_INFO.md"
    "README_STRUCTURE.md:README_STRUCTURE.md"
)

for file_mapping in "${archive_files[@]}"; do
    source_file="${file_mapping%%:*}"
    dest_file="${file_mapping##*:}"
    
    if [ -f "$HELPER_DIR/docs/$source_file" ]; then
        mv "$HELPER_DIR/docs/$source_file" "docs/archive/$dest_file"
        archived_count=$((archived_count + 1))
    fi
done

# Presentations
if [ -f "$HELPER_DIR/archive-files/willow-evolution-presentation.zip" ]; then
    mv "$HELPER_DIR/archive-files/willow-evolution-presentation.zip" "assets/presentations/"
    archived_count=$((archived_count + 1))
fi

success "Phase 4 Complete: $archived_count files archived"

# PHASE 5: SET PROPER PERMISSIONS
log "🔧 Phase 5: Setting permissions..."

# Make scripts executable
if [ -d "tools/scripts/" ]; then
    find tools/scripts/ -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true
fi

if [ -d "tools/legacy-helpers/" ]; then
    find tools/legacy-helpers/ -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true
fi

# Set directory permissions
chmod -R 755 docs/ 2>/dev/null || true
chmod -R 755 storage/backups/ 2>/dev/null || true

success "Phase 5 Complete: Permissions set"

# PHASE 6: CLEANUP EMPTY DIRECTORIES
log "🧹 Phase 6: Cleaning up empty directories..."

cleanup_count=0

# Remove empty helper-files subdirectories
if [ -d "$HELPER_DIR" ]; then
    # Remove empty directories
    find "$HELPER_DIR" -type d -empty -delete 2>/dev/null || true
    
    # Check if main directory is now empty
    if [ -z "$(ls -A "$HELPER_DIR" 2>/dev/null)" ]; then
        rmdir "$HELPER_DIR"
        success "Helper-files directory removed (was empty)"
        cleanup_count=1
    else
        warn "Helper-files directory not empty - manual review needed"
        echo "   Remaining contents:"
        ls -la "$HELPER_DIR" | head -10
        if [ $(ls -la "$HELPER_DIR" | wc -l) -gt 11 ]; then
            echo "   ... and more files"
        fi
    fi
fi

success "Phase 6 Complete: Cleaned up empty directories"

# FINAL SUMMARY
echo
echo -e "${GREEN}"
echo "🎉 HELPER FILES REFACTORING COMPLETED SUCCESSFULLY!"
echo "=================================================="
echo -e "${NC}"

log "📊 Refactoring Summary:"
echo "   🔥 Deleted: $deleted_count obsolete files"
echo "   ✅ Integrated: $integrated_count valuable files"
echo "   🔄 Refactored: $refactored_count files"
echo "   📚 Archived: $archived_count files"
echo "   🧹 Cleaned up: $cleanup_count directories"
echo

success "💾 Backup created: $BACKUP_FILE"
echo

log "📁 New organized structure:"
echo "   ├── docs/development/        # Active development documentation"
echo "   ├── docs/architecture/       # Architecture and structure docs"
echo "   ├── docs/archive/           # Historical reference material"
echo "   ├── docs/legacy/            # Legacy comprehensive documentation"
echo "   ├── tools/scripts/          # Active automation scripts"
echo "   ├── tools/legacy-helpers/   # Legacy reference scripts"
echo "   ├── storage/backups/        # Organized backup storage"
echo "   └── assets/presentations/   # Project presentations"
echo

info "🎯 Next Steps:"
echo "   1. Review integrated files in their new locations"
echo "   2. Test that moved scripts work correctly"
echo "   3. Update any hardcoded references to old paths"
echo "   4. Run the secure reorganization: ./reorganize_willow_secure.sh"
echo "   5. Commit the clean, organized structure"
echo

warn "⚠️  Backup Information:"
echo "   • Full backup of helper-files: $BACKUP_FILE"
echo "   • If you need to restore: tar -xzf $BACKUP_FILE"
echo "   • Consider moving backup to storage/backups/ directory"

echo -e "${PURPLE}✨ Your helper-files are now beautifully organized and integrated! ✨${NC}"