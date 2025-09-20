#!/bin/bash

# File Management Module for WillowCMS
# Provides functions for managing project files, refactoring, and organization

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Display file management menu
display_file_management_menu() {
    clear
    echo -e "${BLUE}========== WillowCMS File Management ==========${NC}"
    echo "1. Refactor Helper Files"
    echo "2. Check File Permissions"
    echo "3. Verify File Integrity (MD5/SHA256)"
    echo "4. Find Large Files"
    echo "5. Back to Main Menu"
    echo
    echo -e "${YELLOW}Choose an option (1-5):${NC} "
    read -r file_option

    case $file_option in
        1)
            refactor_helper_files
            ;;
        2)
            check_file_permissions
            ;;
        3)
            verify_file_integrity
            ;;
        4)
            find_large_files
            ;;
        5)
            return 0
            ;;
        *)
            echo -e "${RED}Invalid option. Please try again.${NC}"
            sleep 2
            display_file_management_menu
            ;;
    esac
}

# Function to refactor helper files
refactor_helper_files() {
    clear
    echo -e "${GREEN}🗂️ WillowCMS Helper Files Refactoring${NC}"
    echo -e "${BLUE}======================================${NC}"
    
    # Configuration
    HELPER_DIR="./helper-files(use-only-if-you-get-lost)"
    BACKUP_PREFIX="helper-files-backup"
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_FILE="${BACKUP_PREFIX}-${TIMESTAMP}.tar.gz"
    
    # Check if helper-files directory exists
    if [[ ! -d "$HELPER_DIR" ]]; then
        echo -e "${RED}[ERROR] Helper-files directory not found: $HELPER_DIR${NC}"
        read -p "Press Enter to return to menu..."
        display_file_management_menu
        return 1
    fi
    
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] Starting comprehensive helper-files refactoring...${NC}"
    
    # Create backup first
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] Creating backup of helper-files directory...${NC}"
    tar -czf "$BACKUP_FILE" "$HELPER_DIR"
    
    if [ $? -eq 0 ]; then
        echo -e "${PURPLE}[SUCCESS] Backup created: $BACKUP_FILE${NC}"
    else
        echo -e "${RED}[ERROR] Backup creation failed!${NC}"
        read -p "Press Enter to return to menu..."
        display_file_management_menu
        return 1
    fi
    
    # Create new directory structure (compatible with reorganization)
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] Creating new directory structure...${NC}"
    mkdir -p docs/{development,architecture,archive,legacy}
    mkdir -p tools/scripts
    mkdir -p tools/legacy-helpers  
    mkdir -p storage/backups/docker-compose/{current,historical}
    mkdir -p assets/presentations
    echo -e "${PURPLE}[SUCCESS] Directory structure created${NC}"
    
    # PHASE 1: DELETE OBSOLETE FILES
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 🔥 Phase 1: Removing obsolete files...${NC}"
    
    deleted_count=0
    
    # Delete archived documentation
    if [ -d "$HELPER_DIR/archived-docs-20250917_202001/" ]; then
        rm -rf "$HELPER_DIR/archived-docs-20250917_202001/"
        deleted_count=$((deleted_count + 7))  # 7 archived docs
        echo -e "${BLUE}[INFO] Removed archived documentation${NC}"
    fi
    
    # Delete single backup files
    if [ -f "$HELPER_DIR/docker-compose.yml.backup.20250917110452" ]; then
        rm -f "$HELPER_DIR/docker-compose.yml.backup.20250917110452"
        deleted_count=$((deleted_count + 1))
        echo -e "${BLUE}[INFO] Removed obsolete backup file${NC}"
    fi
    
    # Delete temporary files
    if [ -d "$HELPER_DIR/temp-files/" ]; then
        temp_count=$(find "$HELPER_DIR/temp-files/" -type f | wc -l)
        rm -rf "$HELPER_DIR/temp-files/"
        deleted_count=$((deleted_count + temp_count))
        echo -e "${BLUE}[INFO] Removed $temp_count temporary files${NC}"
    fi
    
    echo -e "${PURPLE}[SUCCESS] Phase 1 Complete: $deleted_count obsolete files removed${NC}"
    
    # PHASE 2: INTEGRATE VALUABLE CONTENT
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] ✅ Phase 2: Integrating valuable content...${NC}"
    
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
    
    echo -e "${BLUE}[INFO] Integrated $integrated_count development documentation files${NC}"
    
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
    
    echo -e "${BLUE}[INFO] Integrated $script_count development scripts${NC}"
    
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
    
    echo -e "${BLUE}[INFO] Integrated $backup_count docker backup components${NC}"
    
    integrated_count=$((integrated_count + script_count + backup_count))
    echo -e "${PURPLE}[SUCCESS] Phase 2 Complete: $integrated_count valuable files integrated${NC}"
    
    # PHASE 3: REFACTOR AND MODERNIZE
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 🔄 Phase 3: Refactoring documentation...${NC}"
    
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
    
    echo -e "${PURPLE}[SUCCESS] Phase 3 Complete: $refactored_count files refactored${NC}"
    
    # PHASE 4: ARCHIVE REFERENCE MATERIAL
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 📚 Phase 4: Archiving reference material...${NC}"
    
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
    
    echo -e "${PURPLE}[SUCCESS] Phase 4 Complete: $archived_count files archived${NC}"
    
    # PHASE 5: SET PROPER PERMISSIONS
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 🔧 Phase 5: Setting permissions...${NC}"
    
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
    
    echo -e "${PURPLE}[SUCCESS] Phase 5 Complete: Permissions set${NC}"
    
    # PHASE 6: CLEANUP EMPTY DIRECTORIES
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 🧹 Phase 6: Cleaning up empty directories...${NC}"
    
    cleanup_count=0
    
    # Remove empty helper-files subdirectories
    if [ -d "$HELPER_DIR" ]; then
        # Remove empty directories
        find "$HELPER_DIR" -type d -empty -delete 2>/dev/null || true
        
        # Check if main directory is now empty
        if [ -z "$(ls -A "$HELPER_DIR" 2>/dev/null)" ]; then
            rmdir "$HELPER_DIR"
            echo -e "${PURPLE}[SUCCESS] Helper-files directory removed (was empty)${NC}"
            cleanup_count=1
        else
            echo -e "${YELLOW}[WARN] Helper-files directory not empty - manual review needed${NC}"
            echo "   Remaining contents:"
            ls -la "$HELPER_DIR" | head -10
            if [ $(ls -la "$HELPER_DIR" | wc -l) -gt 11 ]; then
                echo "   ... and more files"
            fi
        fi
    fi
    
    echo -e "${PURPLE}[SUCCESS] Phase 6 Complete: Cleaned up empty directories${NC}"
    
    # FINAL SUMMARY
    echo
    echo -e "${GREEN}🎉 HELPER FILES REFACTORING COMPLETED SUCCESSFULLY!${NC}"
    echo -e "${GREEN}==================================================${NC}"
    
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 📊 Refactoring Summary:${NC}"
    echo "   🔥 Deleted: $deleted_count obsolete files"
    echo "   ✅ Integrated: $integrated_count valuable files"
    echo "   🔄 Refactored: $refactored_count files"
    echo "   📚 Archived: $archived_count files"
    echo "   🧹 Cleaned up: $cleanup_count directories"
    echo
    
    echo -e "${PURPLE}[SUCCESS] 💾 Backup created: $BACKUP_FILE${NC}"
    echo
    
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] 📁 New organized structure:${NC}"
    echo "   ├── docs/development/        # Active development documentation"
    echo "   ├── docs/architecture/       # Architecture and structure docs"
    echo "   ├── docs/archive/           # Historical reference material"
    echo "   ├── docs/legacy/            # Legacy comprehensive documentation"
    echo "   ├── tools/scripts/          # Active automation scripts"
    echo "   ├── tools/legacy-helpers/   # Legacy reference scripts"
    echo "   ├── storage/backups/        # Organized backup storage"
    echo "   └── assets/presentations/   # Project presentations"
    echo
    
    echo -e "${BLUE}[INFO] 🎯 Next Steps:${NC}"
    echo "   1. Review integrated files in their new locations"
    echo "   2. Test that moved scripts work correctly"
    echo "   3. Update any hardcoded references to old paths"
    echo "   4. Run the secure reorganization: ./reorganize_willow_secure.sh"
    echo "   5. Commit the clean, organized structure"
    echo
    
    echo -e "${YELLOW}[WARN] ⚠️  Backup Information:${NC}"
    echo "   • Full backup of helper-files: $BACKUP_FILE"
    echo "   • If you need to restore: tar -xzf $BACKUP_FILE"
    echo "   • Consider moving backup to storage/backups/ directory"
    
    echo -e "${PURPLE}✨ Your helper-files are now beautifully organized and integrated! ✨${NC}"
    
    read -p "Press Enter to return to menu..."
    display_file_management_menu
}

# Function to check file permissions
check_file_permissions() {
    clear
    echo -e "${BLUE}========== File Permission Check ==========${NC}"
    echo "This will check important directory and file permissions."
    echo
    
    # Check directories that should be writable
    echo "Checking for writable directories..."
    directories=("app/tmp" "app/logs" "app/webroot/uploads" "storage/backups")
    
    for dir in "${directories[@]}"; do
        if [ -d "$dir" ]; then
            if [ -w "$dir" ]; then
                echo -e "✅ $dir: ${GREEN}WRITABLE${NC}"
            else
                echo -e "⚠️ $dir: ${RED}NOT WRITABLE${NC}"
            fi
        else
            echo -e "❌ $dir: ${RED}DOES NOT EXIST${NC}"
        fi
    done
    
    # Check script execution permissions
    echo -e "\nChecking script execution permissions..."
    scripts=("*.sh" "tools/scripts/*.sh" "bin/*.sh")
    
    for pattern in "${scripts[@]}"; do
        for script in $pattern; do
            if [ -f "$script" ]; then
                if [ -x "$script" ]; then
                    echo -e "✅ $script: ${GREEN}EXECUTABLE${NC}"
                else
                    echo -e "⚠️ $script: ${RED}NOT EXECUTABLE${NC}"
                fi
            fi
        done
    done
    
    # Offer to fix permissions
    echo
    read -p "Would you like to fix any permission issues? (y/n): " fix_perms
    
    if [[ "$fix_perms" =~ ^[Yy]$ ]]; then
        echo "Fixing permissions..."
        
        # Fix directory permissions
        for dir in "${directories[@]}"; do
            if [ -d "$dir" ]; then
                chmod -R 755 "$dir"
                echo "Set permissions on $dir"
            fi
        done
        
        # Fix script permissions
        for pattern in "${scripts[@]}"; do
            for script in $pattern; do
                if [ -f "$script" ]; then
                    chmod +x "$script"
                    echo "Made $script executable"
                fi
            done
        done
        
        echo -e "${GREEN}Permissions fixed successfully!${NC}"
    fi
    
    read -p "Press Enter to return to menu..."
    display_file_management_menu
}

# Function to verify file integrity
verify_file_integrity() {
    clear
    echo -e "${BLUE}========== File Integrity Verification ==========${NC}"
    echo "This will verify integrity of log files and any checksum files."
    echo
    
    # Look for checksum files
    echo "Looking for checksum files..."
    md5_files=$(find . -name "*.md5" -type f | sort)
    sha_files=$(find . -name "*.sha256" -type f | sort)
    
    if [ -z "$md5_files" ] && [ -z "$sha_files" ]; then
        echo -e "${YELLOW}No checksum files found.${NC}"
    else
        if [ -n "$md5_files" ]; then
            echo -e "\n${BLUE}Found MD5 checksum files:${NC}"
            echo "$md5_files"
            
            echo -e "\nVerifying MD5 checksums..."
            for file in $md5_files; do
                echo "Checking $file"
                if command -v md5sum >/dev/null; then
                    md5sum -c "$file" 2>/dev/null || echo -e "${RED}Failed verification: $file${NC}"
                elif command -v md5 >/dev/null; then
                    # For macOS
                    while read -r line; do
                        if [ -n "$line" ]; then
                            expected=$(echo "$line" | awk '{print $1}')
                            filename=$(echo "$line" | awk '{print $2}')
                            if [ -f "$filename" ]; then
                                actual=$(md5 -q "$filename")
                                if [ "$expected" = "$actual" ]; then
                                    echo -e "${GREEN}$filename: OK${NC}"
                                else
                                    echo -e "${RED}$filename: FAILED${NC}"
                                fi
                            else
                                echo -e "${RED}$filename: File not found${NC}"
                            fi
                        fi
                    done < "$file"
                else
                    echo -e "${RED}md5sum/md5 command not found${NC}"
                fi
            done
        fi
        
        if [ -n "$sha_files" ]; then
            echo -e "\n${BLUE}Found SHA256 checksum files:${NC}"
            echo "$sha_files"
            
            echo -e "\nVerifying SHA256 checksums..."
            for file in $sha_files; do
                echo "Checking $file"
                if command -v sha256sum >/dev/null; then
                    sha256sum -c "$file" 2>/dev/null || echo -e "${RED}Failed verification: $file${NC}"
                elif command -v shasum >/dev/null; then
                    # For macOS
                    while read -r line; do
                        if [ -n "$line" ]; then
                            expected=$(echo "$line" | awk '{print $1}')
                            filename=$(echo "$line" | awk '{print $2}')
                            if [ -f "$filename" ]; then
                                actual=$(shasum -a 256 "$filename" | awk '{print $1}')
                                if [ "$expected" = "$actual" ]; then
                                    echo -e "${GREEN}$filename: OK${NC}"
                                else
                                    echo -e "${RED}$filename: FAILED${NC}"
                                fi
                            else
                                echo -e "${RED}$filename: File not found${NC}"
                            fi
                        fi
                    done < "$file"
                else
                    echo -e "${RED}sha256sum/shasum command not found${NC}"
                fi
            done
        fi
    fi
    
    read -p "Press Enter to return to menu..."
    display_file_management_menu
}

# Function to find large files
find_large_files() {
    clear
    echo -e "${BLUE}========== Find Large Files ==========${NC}"
    echo "This will search for large files in the project directory."
    echo
    
    read -p "Enter minimum file size (e.g., 10M for 10 MB): " min_size
    min_size=${min_size:-10M}
    
    echo -e "Searching for files larger than $min_size...\n"
    
    # Find large files and sort by size (largest first)
    if command -v find >/dev/null && command -v sort >/dev/null; then
        find . -type f -size +$min_size -not -path "*/\.*" -exec ls -lh {} \; | sort -rh -k5 | head -20
    else
        echo -e "${RED}Required commands (find/sort) not found${NC}"
    fi
    
    echo -e "\nListing shows the 20 largest files above the specified threshold."
    
    read -p "Press Enter to return to menu..."
    display_file_management_menu
}