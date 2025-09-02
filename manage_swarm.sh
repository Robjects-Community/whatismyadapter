#!/usr/bin/env bash
# manage_swarm.sh - Docker Swarm Management Script for WillowCMS
#
# This script provides complete Docker Swarm management functionality for WillowCMS,
# mirroring all capabilities of manage.sh but adapted for Swarm deployment.
#
# === DOCKER SWARM CONCEPT MAPPING ===
#
# Compose → Swarm Command Mappings:
# - docker compose up -d             → docker stack deploy -c willow-swarm-stack.yml "willow"
# - docker compose down              → docker stack rm "willow"  
# - docker compose logs [service]    → docker service logs -f "willow_[service]"
# - docker compose ps                → docker stack services "willow"
# - docker compose exec svc cmd      → find container + docker exec (local node only)
#
# Service Names: compose service names become "STACK_NAME_service" in swarm
# Networks: overlay network managed by stack, no manual container attachment needed
# Secrets/Configs: use docker secret/config for sensitive data (documented below)
# Execution: requires container running on local node or DOCKER_CONTEXT for remote nodes
#
# === MULTI-NODE CONSIDERATIONS ===
#
# Container execution (svc_exec) requires the service task to run on the local node.
# If services run on remote nodes:
# 1. Set DOCKER_CONTEXT to target remote node: export DOCKER_CONTEXT=remote-node
# 2. OR add placement constraints in willow-swarm-stack.yml: 
#    deploy.placement.constraints: [node.role == manager]
# 3. OR use SSH context: docker context create ssh://user@remote-host
#
# === ENVIRONMENT FILES ===
#
# Environment loading priority (first found wins):
# 1. .env.swarm.local  (local overrides, gitignored)
# 2. .env.swarm        (swarm-specific settings)
# 3. env/.env.swarm    (nested env directory)
# 4. .env              (general environment)
#
# === SERVICE ACCESS URLS ===
#
# Based on willow-swarm-stack.yml port mappings:
# - WillowCMS:     http://localhost:7770
# - phpMyAdmin:    http://localhost:7771
# - Jenkins:       http://localhost:7772
# - Mailpit UI:    http://localhost:7773
# - Redis Cmd:     http://localhost:7774
# - MySQL:         localhost:7710
# - Redis:         localhost:7776
# - SMTP:          localhost:7725

set -Eeuo pipefail

# === Configuration ===

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
PROJECT_ROOT="${SCRIPT_DIR}"
STACK_FILE="${PROJECT_ROOT}/willow-swarm-stack.yml"

# Default values - can be overridden by environment or CLI
STACK_NAME="${STACK_NAME:-willow}"
DEBUG="${DEBUG:-0}"
DRY_RUN="${DRY_RUN:-0}"
INTERACTIVE="${INTERACTIVE:-1}"
FORCE="${FORCE:-0}"

# === Load Modules ===

# Load common helpers first (includes error trap)
# shellcheck source=swarm_tool_modules/swarm_common.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_common.sh"

# Load environment files in priority order
load_env_files \
    "${PROJECT_ROOT}/.env.swarm.local" \
    "${PROJECT_ROOT}/.env.swarm" \
    "${PROJECT_ROOT}/env/.env.swarm" \
    "${PROJECT_ROOT}/.env"

# Allow env files to override stack name and other settings
STACK_NAME="${STACK_NAME:-willow}"

# Load feature modules
# shellcheck source=swarm_tool_modules/swarm_docker.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_docker.sh"
# shellcheck source=swarm_tool_modules/swarm_data.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_data.sh"
# shellcheck source=swarm_tool_modules/swarm_i18n.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_i18n.sh"
# shellcheck source=swarm_tool_modules/swarm_assets.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_assets.sh"
# shellcheck source=swarm_tool_modules/swarm_system.sh
source "${PROJECT_ROOT}/swarm_tool_modules/swarm_system.sh"

# === CLI Argument Parsing ===

show_help() {
    cat << 'EOF'
manage_swarm.sh - Docker Swarm Management for WillowCMS

USAGE:
    ./manage_swarm.sh [OPTIONS] [COMMAND] [ARGS...]

OPTIONS:
    --debug                 Enable debug output  
    --dry-run              Show commands without executing
    --stack-name NAME      Override stack name (default: willow)
    --context NAME         Set Docker context for remote nodes
    --force                Skip confirmation prompts
    --non-interactive      Disable interactive prompts
    --help                 Show this help message

COMMANDS:
    # Docker Swarm Operations
    --deploy               Deploy/update the swarm stack
    --remove               Remove the swarm stack  
    --restart [SERVICE]    Restart service or entire stack
    --scale SERVICE N      Scale service to N replicas
    --logs SERVICE         Follow service logs
    --status               Show stack status
    
    # Data Management
    --db-backup            Create database backup
    --db-restore FILE      Restore database from backup
    --db-shell             Open MySQL shell
    --files-backup         Backup application files
    --files-restore FILE   Restore application files
    
    # System Operations  
    --migrate              Run database migrations
    --composer-install     Install Composer dependencies
    --tests                Run PHPUnit tests
    --cache-clear          Clear all caches
    --shell SERVICE        Open shell in service container
    --health               Perform health check
    
    # Internationalization
    --i18n-extract         Extract translatable strings
    --i18n-translate       Run AI-powered translation
    
    # Asset Management
    --assets-build         Build application assets
    --assets-publish       Publish plugin assets

EXAMPLES:
    # Interactive mode (default)
    ./manage_swarm.sh

    # Deploy stack
    ./manage_swarm.sh --deploy
    
    # Restart a service
    ./manage_swarm.sh --restart willowcms
    
    # Database operations
    ./manage_swarm.sh --db-backup
    ./manage_swarm.sh --db-restore backups/db_cms_20240101-120000.sql.gz
    
    # Open shell in willowcms container
    ./manage_swarm.sh --shell willowcms
    
    # Run with debug output
    ./manage_swarm.sh --debug --health
    
    # Use different stack name
    ./manage_swarm.sh --stack-name production --status

ENVIRONMENT:
    STACK_NAME             Default stack name (willow)
    DEBUG                  Enable debug mode (0/1)
    DRY_RUN               Dry run mode (0/1) 
    DOCKER_CONTEXT        Docker context for remote operations
    BACKUP_DIR            Backup directory (./backups)
    
    Database:
    MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD
    
    See .env.swarm.example for complete environment configuration.

NOTES:
    - Services must run on local node for shell/exec operations
    - Use --context or DOCKER_CONTEXT for remote node operations  
    - Set placement constraints in stack file to pin services locally
    - Backups are stored in ./backups/ directory
    - Log checksums follow WARP.md integrity verification requirements

EOF
}

# Global variables for CLI mode
CLI_COMMAND=""
CLI_ARGS=()

parse_cli_args() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --debug)
                DEBUG=1
                shift
                ;;
            --dry-run)
                DRY_RUN=1
                shift
                ;;
            --stack-name)
                STACK_NAME="$2"
                shift 2
                ;;
            --context)
                export DOCKER_CONTEXT="$2"
                debug "Set Docker context: $2"
                shift 2
                ;;
            --force)
                FORCE=1
                shift
                ;;
            --non-interactive)
                INTERACTIVE=0
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            # Docker operations
            --deploy)
                CLI_COMMAND="deploy"
                shift
                ;;
            --remove)
                CLI_COMMAND="remove"
                shift
                ;;
            --restart)
                CLI_COMMAND="restart"
                CLI_ARGS=("${2:-}")
                shift 2 2>/dev/null || shift
                ;;
            --scale)
                CLI_COMMAND="scale"
                CLI_ARGS=("$2" "$3")
                shift 3
                ;;
            --logs)
                CLI_COMMAND="logs"
                CLI_ARGS=("$2")
                shift 2
                ;;
            --status)
                CLI_COMMAND="status"
                shift
                ;;
            # Data operations
            --db-backup)
                CLI_COMMAND="db_backup"
                shift
                ;;
            --db-restore)
                CLI_COMMAND="db_restore"
                CLI_ARGS=("$2")
                shift 2
                ;;
            --db-shell)
                CLI_COMMAND="db_shell"
                shift
                ;;
            --files-backup)
                CLI_COMMAND="files_backup"
                shift
                ;;
            --files-restore)
                CLI_COMMAND="files_restore"
                CLI_ARGS=("$2")
                shift 2
                ;;
            # System operations
            --migrate)
                CLI_COMMAND="migrate"
                shift
                ;;
            --composer-install)
                CLI_COMMAND="composer_install"
                shift
                ;;
            --tests)
                CLI_COMMAND="tests"
                shift
                ;;
            --cache-clear)
                CLI_COMMAND="cache_clear"
                shift
                ;;
            --shell)
                CLI_COMMAND="shell"
                CLI_ARGS=("$2")
                shift 2
                ;;
            --health)
                CLI_COMMAND="health"
                shift
                ;;
            # i18n operations
            --i18n-extract)
                CLI_COMMAND="i18n_extract"
                shift
                ;;
            --i18n-translate)
                CLI_COMMAND="i18n_translate"
                shift
                ;;
            # Asset operations
            --assets-build)
                CLI_COMMAND="assets_build"
                shift
                ;;
            --assets-publish)
                CLI_COMMAND="assets_publish"
                shift
                ;;
            --exec)
                CLI_COMMAND="exec"
                CLI_ARGS=("$2")
                shift 2
                # Collect remaining arguments as command
                while [[ $# -gt 0 ]]; do
                    CLI_ARGS+=("$1")
                    shift
                done
                ;;
            *)
                log "ERROR: Unknown argument: $1"
                log "Use --help to see available options"
                exit 1
                ;;
        esac
    done
}

# === CLI Command Execution ===

execute_cli_command() {
    case "$CLI_COMMAND" in
        deploy)
            swarm_deploy
            ;;
        remove)
            swarm_remove
            ;;
        restart)
            swarm_restart "${CLI_ARGS[0]:-}"
            ;;
        scale)
            swarm_scale "${CLI_ARGS[0]}" "${CLI_ARGS[1]}"
            ;;
        logs)
            svc_logs "${CLI_ARGS[0]}"
            ;;
        status)
            swarm_services || log "Stack status: not deployed"
            ;;
        db_backup)
            db_backup
            ;;
        db_restore)
            db_restore "${CLI_ARGS[0]}"
            ;;
        db_shell)
            db_shell
            ;;
        files_backup)
            files_backup
            ;;
        files_restore)
            files_restore "${CLI_ARGS[0]}"
            ;;
        migrate)
            migrate_db
            migrate_plugins
            ;;
        composer_install)
            composer_install
            ;;
        tests)
            phpunit_run
            ;;
        cache_clear)
            cache_clear_all
            ;;
        shell)
            svc_exec "${CLI_ARGS[0]}" bash
            ;;
        health)
            health_check
            ;;
        i18n_extract)
            i18n_extract
            ;;
        i18n_translate)
            i18n_translate
            ;;
        assets_build)
            assets_build
            ;;
        assets_publish)
            assets_publish
            ;;
        exec)
            local service="${CLI_ARGS[0]}"
            local cmd=("${CLI_ARGS[@]:1}")
            svc_exec "$service" "${cmd[@]}"
            ;;
        *)
            die "Unknown CLI command: $CLI_COMMAND"
            ;;
    esac
}

# === Interactive Main Menu ===

main_menu() {
    while true; do
        clear 2>/dev/null || true
        echo "=================================================="
        echo "WillowCMS Swarm Manager"
        echo "Stack: ${STACK_NAME} | Debug: ${DEBUG} | Context: ${DOCKER_CONTEXT:-local}"
        echo "=================================================="
        echo
        
        # Show quick status
        if stack_exists; then
            local service_count; service_count=$(docker stack services "${STACK_NAME}" --format "{{.Name}}" | wc -l | tr -d ' ')
            echo "Stack Status: ${service_count} services running"
        else
            echo "Stack Status: Not deployed"
        fi
        echo
        
        echo "Main Menu:"
        echo "1) Docker/Swarm Management"
        echo "2) Data Management"  
        echo "3) Internationalization"
        echo "4) Asset Management"
        echo "5) System Operations"
        echo "6) Toggle Debug Mode (currently: ${DEBUG})"
        echo "7) Quick Actions"
        echo "0) Exit"
        echo
        read -r -p "Choose an option [0-7]: " choice
        
        case "$choice" in
            1)
                docker_menu
                ;;
            2)
                data_menu
                ;;
            3)
                i18n_menu
                ;;
            4)
                assets_menu
                ;;
            5)
                system_menu
                ;;
            6)
                if [ "$DEBUG" = "1" ]; then
                    DEBUG=0
                    log "Debug mode disabled"
                else
                    DEBUG=1  
                    log "Debug mode enabled"
                fi
                export DEBUG
                sleep 1
                ;;
            7)
                quick_actions_menu
                ;;
            0|"")
                log "Goodbye!"
                break
                ;;
            *)
                echo "Invalid option. Please choose 0-7."
                sleep 1
                ;;
        esac
    done
}

# === Quick Actions Menu ===

quick_actions_menu() {
    while true; do
        echo
        echo "=== Quick Actions ==="
        echo "1) Deploy Stack"
        echo "2) Health Check"
        echo "3) App Shell (willowcms)"
        echo "4) Database Shell"
        echo "5) View Logs (willowcms)"
        echo "6) Clear All Caches"
        echo "7) Database Backup"
        echo "8) Stack Status"
        echo "9) Back to Main Menu"
        echo
        read -r -p "Quick Action > " choice
        
        case "$choice" in
            1)
                swarm_deploy
                pause
                ;;
            2)
                health_check
                pause
                ;;
            3)
                svc_exec willowcms bash
                ;;
            4)
                db_shell
                ;;
            5)
                log "Following willowcms logs (Ctrl+C to stop)..."
                svc_logs willowcms || true
                ;;
            6)
                cache_clear_all
                pause
                ;;
            7)
                db_backup
                pause
                ;;
            8)
                swarm_services
                pause
                ;;
            9|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-9."
                ;;
        esac
    done
}

# === Main Execution ===

main() {
    # Parse command line arguments
    parse_cli_args "$@"
    
    # Enable debug mode early if requested
    if [ "$DEBUG" = "1" ]; then
        log "Debug mode enabled"
        # Optionally enable bash xtrace for very verbose debugging
        # set -x
    fi
    
    debug "Script starting with stack: $STACK_NAME"
    debug "Project root: $PROJECT_ROOT"
    debug "Stack file: $STACK_FILE"
    
    # Validate requirements
    require_bins docker
    require_swarm  
    require_stack_file "$STACK_FILE"
    
    # Validate stack file syntax
    debug "Validating stack file..."
    if ! docker compose -f "$STACK_FILE" config >/dev/null 2>&1; then
        log "WARNING: Stack file validation failed. Run: docker compose -f $STACK_FILE config"
    fi
    
    # Execute CLI command if provided, otherwise start interactive menu
    if [ -n "$CLI_COMMAND" ]; then
        debug "Executing CLI command: $CLI_COMMAND"
        execute_cli_command
    else
        debug "Starting interactive menu"
        if [ "$INTERACTIVE" = "1" ]; then
            main_menu
        else
            log "ERROR: No command specified and interactive mode disabled"
            show_help
            exit 1
        fi
    fi
}

# === Entry Point ===

# Run main function with all arguments
main "$@"
