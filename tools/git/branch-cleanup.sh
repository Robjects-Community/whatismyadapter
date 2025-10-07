#!/usr/bin/env bash
#
# Branch Cleanup Script for WillowCMS
# 
# Purpose: Safely identify, analyze, and cleanup stale or merged git branches
# Location: /Volumes/1TB_DAVINCI/docker/willow/tools/git/branch-cleanup.sh
#
# Features:
# - Protected branch safety checks
# - Automated backup before deletion
# - Merge status analysis
# - Stale branch detection
# - Interactive and dry-run modes
# - Comprehensive logging
#
# Usage: ./branch-cleanup.sh [OPTIONS]
#
set -euo pipefail

# ============================================================================
# CONFIGURATION
# ============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
ARCHIVE_DIR="${SCRIPT_DIR}/archives"
REPORT_DIR="${SCRIPT_DIR}/reports"
LOG_DIR="${SCRIPT_DIR}/logs"
CONFIG_DIR="${SCRIPT_DIR}/config"

TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
LOG_FILE="${LOG_DIR}/cleanup-${TIMESTAMP}.log"
REPORT_FILE="${REPORT_DIR}/branch-analysis-${TIMESTAMP}.txt"
ARCHIVE_FILE="${ARCHIVE_DIR}/deleted-branches-${TIMESTAMP}.log"

# Protected branches (never delete these)
PROTECTED_BRANCHES=(
    "main"
    "main-clean"
    "portainer-stack"
    "main-server"
    "main-prototype"
    "demo"
    "master"
)

# Stale threshold in days
STALE_DAYS=28

# ============================================================================
# COLORS
# ============================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# ============================================================================
# LOGGING FUNCTIONS
# ============================================================================

log() {
    echo -e "[$(date +'%Y-%m-%d %H:%M:%S')] $*" | tee -a "${LOG_FILE}"
}

log_info() {
    echo -e "${CYAN}ℹ${NC}  $*" | tee -a "${LOG_FILE}"
}

log_success() {
    echo -e "${GREEN}✓${NC}  $*" | tee -a "${LOG_FILE}"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC}  $*" | tee -a "${LOG_FILE}"
}

log_error() {
    echo -e "${RED}✗${NC}  $*" | tee -a "${LOG_FILE}"
}

# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

is_protected_branch() {
    local branch="$1"
    for protected in "${PROTECTED_BRANCHES[@]}"; do
        if [[ "$branch" == "$protected" ]]; then
            return 0
        fi
    done
    return 1
}

get_branch_last_commit_date() {
    local branch="$1"
    git log -1 --format=%ct "$branch" 2>/dev/null || echo "0"
}

get_days_since_last_commit() {
    local branch="$1"
    local last_commit_timestamp
    last_commit_timestamp=$(get_branch_last_commit_date "$branch")
    
    if [[ "$last_commit_timestamp" == "0" ]]; then
        echo "999"
        return
    fi
    
    local current_timestamp
    current_timestamp=$(date +%s)
    local diff_seconds=$((current_timestamp - last_commit_timestamp))
    local diff_days=$((diff_seconds / 86400))
    echo "$diff_days"
}

is_merged_into() {
    local branch="$1"
    local base_branch="$2"
    
    # Check if branch is merged into base
    if git merge-base --is-ancestor "$branch" "$base_branch" 2>/dev/null; then
        # Verify it's actually merged (not just ancestor)
        local merge_base
        merge_base=$(git merge-base "$branch" "$base_branch" 2>/dev/null)
        local branch_commit
        branch_commit=$(git rev-parse "$branch" 2>/dev/null)
        
        if [[ "$merge_base" == "$branch_commit" ]]; then
            return 0
        fi
    fi
    return 1
}

has_unique_commits() {
    local branch="$1"
    local base_branch="$2"
    
    # Check if branch has commits not in base
    local unique_count
    unique_count=$(git rev-list --count "${base_branch}..${branch}" 2>/dev/null || echo "0")
    
    if [[ "$unique_count" -gt 0 ]]; then
        return 0
    fi
    return 1
}

get_branch_author() {
    local branch="$1"
    git log -1 --format=%an "$branch" 2>/dev/null || echo "Unknown"
}

get_branch_date() {
    local branch="$1"
    git log -1 --format="%ci" "$branch" 2>/dev/null || echo "Unknown"
}

create_backup_tag() {
    local branch="$1"
    local tag_name="backup/${branch//\//-}-${TIMESTAMP}"
    
    git tag "$tag_name" "$branch" 2>/dev/null || {
        log_warning "Failed to create backup tag for $branch"
        return 1
    }
    
    log_success "Created backup tag: $tag_name"
    echo "$tag_name"
}

# ============================================================================
# ANALYSIS FUNCTIONS
# ============================================================================

analyze_branches() {
    local mode="$1"
    
    log_info "Analyzing branches..."
    echo ""
    
    # Get current branch
    local current_branch
    current_branch=$(git branch --show-current)
    
    # Initialize category arrays (bash 3.2 compatible)
    local merged_branches=()
    local stale_branches=()
    local active_branches=()
    local protected_branches=()
    
    # Analyze each local branch
    while IFS= read -r branch; do
        branch=$(echo "$branch" | sed 's/^[* ] //')
        
        # Skip current branch in analysis
        if [[ "$branch" == "$current_branch" ]]; then
            continue
        fi
        
        # Check if protected
        if is_protected_branch "$branch"; then
            protected_branches+=("$branch")
            continue
        fi
        
        # Check if merged into main-clean or portainer-stack
        local is_merged=false
        if is_merged_into "$branch" "main-clean" || is_merged_into "$branch" "portainer-stack"; then
            is_merged=true
        fi
        
        # Check if stale
        local days_old
        days_old=$(get_days_since_last_commit "$branch")
        
        # Categorize
        if $is_merged; then
            merged_branches+=("$branch")
        elif [[ "$days_old" -ge "$STALE_DAYS" ]]; then
            stale_branches+=("$branch")
        else
            active_branches+=("$branch")
        fi
        
    done < <(git branch --format='%(refname:short)')
    
    # Generate report
    {
        echo "╔════════════════════════════════════════════════════════════════╗"
        echo "║         Branch Analysis Report                                 ║"
        echo "║         Generated: $(date)                         ║"
        echo "╚════════════════════════════════════════════════════════════════╝"
        echo ""
        echo "Current Branch: $current_branch"
        echo "Project: WillowCMS"
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        
        # Protected branches
        echo "PROTECTED BRANCHES (never delete): ${#protected_branches[@]}"
        echo "────────────────────────────────────────────────────────────────"
        for branch in "${protected_branches[@]}"; do
            local author=$(get_branch_author "$branch")
            local date=$(get_branch_date "$branch")
            echo "  • $branch"
            echo "    Author: $author | Last: $date"
        done
        echo ""
        
        # Merged branches
        echo "MERGED BRANCHES (safe to delete): ${#merged_branches[@]}"
        echo "────────────────────────────────────────────────────────────────"
        for branch in "${merged_branches[@]}"; do
            local author=$(get_branch_author "$branch")
            local date=$(get_branch_date "$branch")
            local days=$(get_days_since_last_commit "$branch")
            echo "  • $branch"
            echo "    Author: $author | Age: ${days} days | Last: $date"
        done
        echo ""
        
        # Stale branches
        echo "STALE BRANCHES (${STALE_DAYS}+ days, not merged): ${#stale_branches[@]}"
        echo "────────────────────────────────────────────────────────────────"
        for branch in "${stale_branches[@]}"; do
            local author=$(get_branch_author "$branch")
            local date=$(get_branch_date "$branch")
            local days=$(get_days_since_last_commit "$branch")
            local has_unique=$(has_unique_commits "$branch" "main-clean" && echo "YES" || echo "NO")
            echo "  • $branch"
            echo "    Author: $author | Age: ${days} days | Unique commits: $has_unique"
            echo "    Last: $date"
        done
        echo ""
        
        # Active branches
        echo "ACTIVE BRANCHES (recent, not merged): ${#active_branches[@]}"
        echo "────────────────────────────────────────────────────────────────"
        for branch in "${active_branches[@]}"; do
            local author=$(get_branch_author "$branch")
            local date=$(get_branch_date "$branch")
            local days=$(get_days_since_last_commit "$branch")
            echo "  • $branch"
            echo "    Author: $author | Age: ${days} days | Last: $date"
        done
        echo ""
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo "SUMMARY"
        echo "────────────────────────────────────────────────────────────────"
        echo "  Total Branches: $(git branch | wc -l | tr -d ' ')"
        echo "  Protected: ${#protected_branches[@]}"
        echo "  Merged (deletable): ${#merged_branches[@]}"
        echo "  Stale (review): ${#stale_branches[@]}"
        echo "  Active: ${#active_branches[@]}"
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        
    } | tee "${REPORT_FILE}"
    
    log_success "Analysis complete. Report saved to: ${REPORT_FILE}"
    
    # Return categories for cleanup operations
    printf "%s\n" "${merged_branches[@]}" > "${CONFIG_DIR}/merged-branches.tmp" 2>/dev/null || touch "${CONFIG_DIR}/merged-branches.tmp"
    printf "%s\n" "${stale_branches[@]}" > "${CONFIG_DIR}/stale-branches.tmp" 2>/dev/null || touch "${CONFIG_DIR}/stale-branches.tmp"
}

# ============================================================================
# CLEANUP FUNCTIONS
# ============================================================================

cleanup_merged_branches() {
    local dry_run="$1"
    local interactive="$2"
    
    log_info "Processing merged branches..."
    
    if [[ ! -f "${CONFIG_DIR}/merged-branches.tmp" ]]; then
        log_error "No branch analysis found. Run analysis first."
        return 1
    fi
    
    local merged_branches
    merged_branches=$(cat "${CONFIG_DIR}/merged-branches.tmp")
    
    if [[ -z "$merged_branches" ]]; then
        log_info "No merged branches to cleanup."
        return 0
    fi
    
    echo "$merged_branches" | tr ' ' '\n' | while IFS= read -r branch; do
        [[ -z "$branch" ]] && continue
        
        if $interactive; then
            echo ""
            echo -e "${YELLOW}Branch:${NC} $branch"
            echo -e "${CYAN}Author:${NC} $(get_branch_author "$branch")"
            echo -e "${CYAN}Last commit:${NC} $(get_branch_date "$branch")"
            echo ""
            read -p "Delete this branch? (y/N): " -n 1 -r
            echo ""
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                log_info "Skipped: $branch"
                continue
            fi
        fi
        
        if $dry_run; then
            log_info "[DRY-RUN] Would delete: $branch"
        else
            # Create backup tag
            local tag_name
            tag_name=$(create_backup_tag "$branch")
            
            if [[ -n "$tag_name" ]]; then
                # Delete the branch
                if git branch -D "$branch" &>/dev/null; then
                    log_success "Deleted: $branch (backup: $tag_name)"
                    echo "$(date +%Y-%m-%d\ %H:%M:%S) | $branch | $tag_name" >> "${ARCHIVE_FILE}"
                else
                    log_error "Failed to delete: $branch"
                fi
            fi
        fi
    done
}

cleanup_stale_branches() {
    local dry_run="$1"
    
    log_info "Processing stale branches (manual review recommended)..."
    log_warning "Stale branches contain unique commits. Review before deletion."
    
    if [[ ! -f "${CONFIG_DIR}/stale-branches.tmp" ]]; then
        log_error "No branch analysis found. Run analysis first."
        return 1
    fi
    
    local stale_branches
    stale_branches=$(cat "${CONFIG_DIR}/stale-branches.tmp")
    
    if [[ -z "$stale_branches" ]]; then
        log_info "No stale branches found."
        return 0
    fi
    
    echo ""
    log_warning "Stale branches require manual review:"
    echo "$stale_branches" | tr ' ' '\n' | while IFS= read -r branch; do
        [[ -z "$branch" ]] && continue
        echo "  • $branch ($(get_days_since_last_commit "$branch") days old)"
    done
    echo ""
    log_info "Use --interactive mode to review and delete stale branches one by one."
}

# ============================================================================
# MAIN FUNCTIONS
# ============================================================================

show_help() {
    cat << EOF
${BOLD}Branch Cleanup Script for WillowCMS${NC}

${BOLD}USAGE:${NC}
    $(basename "$0") [OPTIONS]

${BOLD}OPTIONS:${NC}
    -h, --help              Show this help message
    -a, --analyze           Analyze branches and generate report (default)
    -c, --cleanup           Cleanup merged branches after analysis
    -i, --interactive       Interactive mode (confirm each deletion)
    -d, --dry-run           Dry-run mode (show what would be deleted)
    -s, --stale             Include stale branches in cleanup (with confirmation)
    -r, --report            Show latest analysis report
    --force                 Skip safety confirmations (use with caution)

${BOLD}EXAMPLES:${NC}
    # Analyze branches
    $(basename "$0") --analyze

    # Dry-run cleanup
    $(basename "$0") --cleanup --dry-run

    # Interactive cleanup
    $(basename "$0") --cleanup --interactive

    # Cleanup merged branches automatically
    $(basename "$0") --cleanup

    # Show latest report
    $(basename "$0") --report

${BOLD}PROTECTED BRANCHES:${NC}
$(printf '    • %s\n' "${PROTECTED_BRANCHES[@]}")

${BOLD}BRANCH CATEGORIES:${NC}
    • MERGED: Branches fully merged into main-clean or portainer-stack
    • STALE: Branches with no activity for ${STALE_DAYS}+ days (not merged)
    • ACTIVE: Recent branches with unique commits
    • PROTECTED: Core branches that are never deleted

${BOLD}SAFETY FEATURES:${NC}
    • Backup tags created before deletion (backup/branch-name-YYYYMMDD)
    • Comprehensive logging to ${LOG_DIR}
    • Deletion archive maintained at ${ARCHIVE_DIR}
    • Protected branches cannot be deleted

${BOLD}FILES:${NC}
    Logs:     ${LOG_DIR}
    Reports:  ${REPORT_DIR}
    Archives: ${ARCHIVE_DIR}

EOF
}

show_latest_report() {
    local latest_report
    latest_report=$(ls -t "${REPORT_DIR}"/branch-analysis-*.txt 2>/dev/null | head -1)
    
    if [[ -z "$latest_report" ]]; then
        log_error "No analysis report found. Run --analyze first."
        return 1
    fi
    
    cat "$latest_report"
}

main() {
    local mode="analyze"
    local dry_run=false
    local interactive=false
    local include_stale=false
    local force=false
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -a|--analyze)
                mode="analyze"
                shift
                ;;
            -c|--cleanup)
                mode="cleanup"
                shift
                ;;
            -i|--interactive)
                interactive=true
                shift
                ;;
            -d|--dry-run)
                dry_run=true
                shift
                ;;
            -s|--stale)
                include_stale=true
                shift
                ;;
            -r|--report)
                show_latest_report
                exit 0
                ;;
            --force)
                force=true
                shift
                ;;
            *)
                echo "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done
    
    # Change to project root
    cd "$PROJECT_ROOT" || {
        log_error "Failed to change to project root: $PROJECT_ROOT"
        exit 1
    }
    
    # Verify we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        log_error "Not a git repository: $PROJECT_ROOT"
        exit 1
    fi
    
    # Check for uncommitted changes (unless force)
    if ! $force && [[ -n "$(git status --porcelain)" ]]; then
        log_warning "You have uncommitted changes."
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log_info "Aborted by user."
            exit 0
        fi
    fi
    
    # Header
    echo ""
    echo -e "${BOLD}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║         WillowCMS Branch Cleanup Tool                         ║${NC}"
    echo -e "${BOLD}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    
    # Run requested operation
    case $mode in
        analyze)
            analyze_branches "$mode"
            ;;
        cleanup)
            # Run analysis first if not already done
            if [[ ! -f "${CONFIG_DIR}/merged-branches.tmp" ]]; then
                log_info "Running analysis first..."
                analyze_branches "$mode"
                echo ""
            fi
            
            # Cleanup
            if $dry_run; then
                log_info "DRY-RUN MODE: No branches will be deleted"
            fi
            
            cleanup_merged_branches "$dry_run" "$interactive"
            
            if $include_stale && ! $dry_run; then
                echo ""
                cleanup_stale_branches "$dry_run"
            fi
            ;;
    esac
    
    echo ""
    log_success "Operation complete!"
    log_info "Log file: ${LOG_FILE}"
    
    # Cleanup temp files
    rm -f "${CONFIG_DIR}"/*.tmp
}

# Run main function
main "$@"
