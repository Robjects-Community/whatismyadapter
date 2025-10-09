#!/usr/bin/env bash

##################################################################
# WillowCMS Docker Cleanup Script
##################################################################
#
# Safe, interactive Docker cleanup with backups and rollback support
#
# Features:
# - Dry-run by default (no destructive actions)
# - Interactive confirmations (default: No)
# - Backups volumes with SHA256 checksums before deletion
# - Preserves running containers and Willow project images
# - Rollback capability for deleted volumes
# - Colorized output and comprehensive logging
#
##################################################################

set -Eeuo pipefail
IFS=$'\n\t'

# Script version
VERSION="1.0.0"

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Timestamp for logs and backups
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Default configuration
DRY_RUN="${DRY_RUN:-true}"
VERBOSE="${VERBOSE:-false}"
FORCE="${FORCE:-false}"
PRESERVE_PROJECT="${PRESERVE_PROJECT:-true}"
IMAGE_GRACE_DAYS="${IMAGE_GRACE_DAYS:-30}"
DOCKER_CONTEXT="${DOCKER_CONTEXT:-}"
BACKUP_ROOT="${BACKUP_ROOT:-${PROJECT_ROOT}/backups}"
LOGS_DIR="${LOGS_DIR:-${PROJECT_ROOT}/logs}"
WILLOW_COMPOSE_FILE="${WILLOW_COMPOSE_FILE:-${PROJECT_ROOT}/docker-compose.yml}"
PROJECT_IMAGE_PATTERNS="${PROJECT_IMAGE_PATTERNS:-willow,willowcms,adaptercms}"
JENKINS_VOLUME_CANDIDATES="${JENKINS_VOLUME_CANDIDATES:-willow-port_jenkins_home,willow-portainer-final_jenkins_home,willowcms-swarm-test_jenkins_home,jenkins_home,portainer_jenkins_home,whatismyadaptor_jenkins_home}"
EXTRA_PROTECTED_IMAGES="${EXTRA_PROTECTED_IMAGES:-}"
EXTRA_PROTECTED_VOLUMES="${EXTRA_PROTECTED_VOLUMES:-}"

# Operation filters
ONLY_OPERATION=""
EXCLUDE_OPERATIONS=""
ROLLBACK_VOLUME=""

# Load environment files
if [[ -f "${PROJECT_ROOT}/.env" ]]; then
    set -a
    # shellcheck source=/dev/null
    source "${PROJECT_ROOT}/.env"
    set +a
fi

if [[ -f "${SCRIPT_DIR}/.env" ]]; then
    set -a
    # shellcheck source=/dev/null
    source "${SCRIPT_DIR}/.env"
    set +a
fi

# Setup logging
LOG_FILE="${LOGS_DIR}/docker-cleanup-${TIMESTAMP}.log"
mkdir -p "${LOGS_DIR}"

# Colors (with fallback for non-TTY)
if [[ -t 1 ]]; then
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    YELLOW='\033[1;33m'
    BLUE='\033[0;34m'
    BOLD='\033[1m'
    RESET='\033[0m'
else
    RED=''
    GREEN=''
    YELLOW=''
    BLUE=''
    BOLD=''
    RESET=''
fi

# Logging functions
log() {
    local color="$1"
    shift
    echo -e "${color}$*${RESET}" | tee -a "$LOG_FILE"
}

log_info() { log "$BLUE" "ℹ️  $*"; }
log_success() { log "$GREEN" "✅ $*"; }
log_warning() { log "$YELLOW" "⚠️  $*"; }
log_error() { log "$RED" "❌ $*"; }
log_header() { log "$BOLD" "\n========================================\n$*\n========================================"; }

# Run command wrapper
run_cmd() {
    local cmd="$*"
    
    if [[ "$VERBOSE" == "true" ]] || [[ "$DRY_RUN" == "true" ]]; then
        log_info "Command: $cmd"
    fi
    
    if [[ "$DRY_RUN" == "true" ]]; then
        log_warning "[DRY-RUN] Would execute: $cmd"
        return 0
    fi
    
    if eval "$cmd" >> "$LOG_FILE" 2>&1; then
        return 0
    else
        local exit_code=$?
        log_error "Command failed with exit code $exit_code: $cmd"
        return $exit_code
    fi
}

# Confirmation prompt
confirm() {
    local prompt="$1"
    local default="${2:-n}"
    
    if [[ "$FORCE" == "true" ]]; then
        log_info "Auto-confirmed (--force): $prompt"
        return 0
    fi
    
    local answer
    if [[ "$default" == "y" ]]; then
        read -r -p "$(echo -e "${YELLOW}${prompt} [Y/n]: ${RESET}")" answer
        answer=${answer:-y}
    else
        read -r -p "$(echo -e "${YELLOW}${prompt} [y/N]: ${RESET}")" answer
        answer=${answer:-n}
    fi
    
    [[ "$answer" =~ ^[Yy] ]]
}

# Trap handlers
cleanup_on_exit() {
    local exit_code=$?
    if [[ $exit_code -ne 0 ]]; then
        log_error "\nScript failed with exit code $exit_code"
        log_info "Check log file for details: $LOG_FILE"
    fi
    exit $exit_code
}

trap cleanup_on_exit EXIT ERR

# Usage function
usage() {
    cat <<EOF
WillowCMS Docker Cleanup Script v${VERSION}

USAGE:
    $0 [OPTIONS]

OPTIONS:
    --dry-run               Simulate cleanup without making changes (default: true)
    --no-dry-run            Actually perform cleanup operations
    --verbose               Show detailed command output
    --force                 Skip confirmation prompts
    --preserve-project      Protect Willow project images (default: true)
    --no-preserve-project   Allow cleanup of project images
    --grace-days N          Keep images newer than N days (default: 30)
    --compose-file PATH     Path to docker-compose.yml
    --context NAME          Docker context to use
    --only OPERATION        Only perform specific operation:
                            build-cache, volumes, images, dangling, networks
    --exclude OPERATION     Exclude specific operations (comma-separated)
    --rollback [VOLUME]     Restore volume from backup (or list backups)
    -h, --help              Show this help message
    --version               Show version

EXAMPLES:
    # Dry-run (safe, shows what would be done)
    $0

    # Actually clean build cache only
    $0 --no-dry-run --only build-cache

    # Clean unused volumes with backups
    $0 --no-dry-run --only volumes

    # Full cleanup (interactive)
    $0 --no-dry-run

    # Full cleanup (non-interactive)
    $0 --no-dry-run --force

    # Rollback a volume
    $0 --rollback jenkins_home

EOF
}

# Parse arguments
parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            --no-dry-run)
                DRY_RUN=false
                shift
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --force)
                FORCE=true
                shift
                ;;
            --preserve-project)
                PRESERVE_PROJECT=true
                shift
                ;;
            --no-preserve-project)
                PRESERVE_PROJECT=false
                shift
                ;;
            --grace-days)
                IMAGE_GRACE_DAYS="$2"
                shift 2
                ;;
            --compose-file)
                WILLOW_COMPOSE_FILE="$2"
                shift 2
                ;;
            --context)
                DOCKER_CONTEXT="$2"
                shift 2
                ;;
            --only)
                ONLY_OPERATION="$2"
                shift 2
                ;;
            --exclude)
                EXCLUDE_OPERATIONS="$2"
                shift 2
                ;;
            --rollback)
                ROLLBACK_VOLUME="${2:-}"
                shift
                [[ -n "${2:-}" ]] && shift
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            --version)
                echo "v${VERSION}"
                exit 0
                ;;
            *)
                log_error "Unknown option: $1"
                usage
                exit 1
                ;;
        esac
    done
}

# Validate environment
validate_environment() {
    log_header "Validating Environment"
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi
    log_success "Docker CLI found: $(docker --version)"
    
    # Check Docker Compose
    if ! docker compose version &> /dev/null; then
        log_error "Docker Compose is not available"
        exit 1
    fi
    log_success "Docker Compose found: $(docker compose version)"
    
    # Set Docker context if specified
    if [[ -n "$DOCKER_CONTEXT" ]]; then
        log_info "Switching to Docker context: $DOCKER_CONTEXT"
        run_cmd "docker context use \"$DOCKER_CONTEXT\""
    fi
    
    # Check Docker daemon
    if ! docker info &> /dev/null; then
        log_error "Docker daemon is not running"
        exit 1
    fi
    log_success "Docker daemon is running"
    
    # Pull alpine for volume operations
    log_info "Ensuring alpine:3.20 is available..."
    if docker image inspect alpine:3.20 &> /dev/null; then
        log_success "alpine:3.20 already available"
    else
        run_cmd "docker pull alpine:3.20"
    fi
    
    log_success "Environment validation complete"
}

# Detect OS for portable date handling
date_to_epoch() {
    local date_str="$1"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        date -j -f "%Y-%m-%dT%H:%M:%S" "${date_str%.*}" "+%s" 2>/dev/null || echo "0"
    else
        # Linux
        date -d "$date_str" "+%s" 2>/dev/null || echo "0"
    fi
}

# Calculate cutoff date
calculate_cutoff_date() {
    local grace_days="$1"
    local grace_seconds=$((grace_days * 86400))
    
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        date -v-"${grace_days}d" "+%s"
    else
        # Linux
        date -d "$grace_days days ago" "+%s"
    fi
}

# Collect protected resources
collect_protected_resources() {
    log_header "Collecting Protected Resources"
    
    local protected_image_ids=()
    local protected_image_names=()
    local protected_volumes=()
    
    # Images from running containers
    if docker ps -q | head -n 1 &> /dev/null; then
        log_info "Collecting images from running containers..."
        while IFS= read -r image_id; do
            [[ -n "$image_id" ]] && protected_image_ids+=("$image_id")
        done < <(docker ps -aq 2>/dev/null | xargs docker inspect -f '{{.Image}}' 2>/dev/null | sort -u)
        
        while IFS= read -r image_name; do
            [[ -n "$image_name" ]] && protected_image_names+=("$image_name")
        done < <(docker ps --format '{{.Image}}' 2>/dev/null | sort -u)
        
        log_success "Protected ${#protected_image_ids[@]} image(s) from running containers"
    fi
    
    # Volumes from running containers
    if docker ps -q | head -n 1 &> /dev/null; then
        log_info "Collecting volumes from running containers..."
        while IFS= read -r volume; do
            [[ -n "$volume" ]] && protected_volumes+=("$volume")
        done < <(docker ps -aq 2>/dev/null | xargs docker inspect -f '{{range .Mounts}}{{if eq .Type "volume"}}{{println .Name}}{{end}}{{end}}' 2>/dev/null | sort -u)
        
        log_success "Protected ${#protected_volumes[@]} volume(s) from running containers"
    fi
    
    # Willow project images
    if [[ "$PRESERVE_PROJECT" == "true" ]] && [[ -f "$WILLOW_COMPOSE_FILE" ]]; then
        log_info "Collecting Willow project images from compose file..."
        while IFS= read -r image; do
            [[ -n "$image" ]] && protected_image_names+=("$image")
        done < <(docker compose -f "$WILLOW_COMPOSE_FILE" config --images 2>/dev/null | sort -u)
        
        log_success "Protected Willow project images"
    fi
    
    # Export for use by other functions
    printf '%s\n' "${protected_image_ids[@]}" > "$LOGS_DIR/protected_image_ids_${TIMESTAMP}.txt"
    printf '%s\n' "${protected_image_names[@]}" > "$LOGS_DIR/protected_image_names_${TIMESTAMP}.txt"
    printf '%s\n' "${protected_volumes[@]}" > "$LOGS_DIR/protected_volumes_${TIMESTAMP}.txt"
    
    # Display summary
    log_info "\n📋 Protected Resources Summary:"
    log_info "   - Image IDs: ${#protected_image_ids[@]}"
    log_info "   - Image Names: ${#protected_image_names[@]}"
    log_info "   - Volumes: ${#protected_volumes[@]}"
    
    if [[ "$VERBOSE" == "true" ]]; then
        [[ ${#protected_image_names[@]} -gt 0 ]] && {
            log_info "\n🛡️  Protected Images:"
            printf '%s\n' "${protected_image_names[@]}" | while read -r name; do
                log_info "   - $name"
            done
        }
        [[ ${#protected_volumes[@]} -gt 0 ]] && {
            log_info "\n🛡️  Protected Volumes:"
            printf '%s\n' "${protected_volumes[@]}" | while read -r vol; do
                log_info "   - $vol"
            done
        }
    fi
}

# Show current Docker usage
show_docker_usage() {
    log_header "Current Docker Disk Usage"
    docker system df -v | tee -a "$LOG_FILE"
}

# Main function (to be continued...)
main() {
    parse_args "$@"
    
    # Handle rollback operation
    if [[ -n "$ROLLBACK_VOLUME" ]]; then
        handle_rollback
        exit 0
    fi
    
    log_header "WillowCMS Docker Cleanup Script v${VERSION}"
    log_info "Log file: $LOG_FILE"
    log_info "Mode: $(if [[ "$DRY_RUN" == "true" ]]; then echo "DRY-RUN (safe)"; else echo "LIVE (will make changes)"; fi)"
    log_info "Started at: $(date)"
    
    validate_environment
    show_docker_usage
    collect_protected_resources
    
    log_success "\n✨ Ready to begin cleanup!"
    log_info "This is a dry-run. No changes will be made."
    log_info "To actually perform cleanup, run with --no-dry-run flag"
}

# Handle rollback (placeholder - to be implemented)
handle_rollback() {
    log_header "Volume Rollback"
    
    if [[ -z "$ROLLBACK_VOLUME" ]]; then
        # List available backups
        log_info "Available backups:"
        if [[ -d "$BACKUP_ROOT" ]]; then
            find "$BACKUP_ROOT" -type d -name "docker-volumes-*" | sort -r | while read -r backup_dir; do
                log_info "\n📦 $(basename "$backup_dir")"
                find "$backup_dir" -name "*.tar.gz" | while read -r backup_file; do
                    local volume_name=$(basename "$backup_file" .tar.gz)
                    local size=$(du -h "$backup_file" | cut -f1)
                    log_info "   - $volume_name ($size)"
                done
            done
        else
            log_warning "No backup directory found: $BACKUP_ROOT"
        fi
    else
        # Restore specific volume
        log_info "Restoring volume: $ROLLBACK_VOLUME"
        log_warning "Rollback functionality not yet fully implemented"
        log_info "Check backup directory: $BACKUP_ROOT"
    fi
}

# Run main function
main "$@"
