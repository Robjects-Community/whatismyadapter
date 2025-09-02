#!/usr/bin/env bash
# swarm_common.sh - Core utilities and helpers for Docker Swarm management

set -Eeuo pipefail

# === Logging and Debugging ===

log() { printf '[%s] %s\n' "$(date '+%F %T')" "$*" >&2; }
debug() { [ "${DEBUG:-0}" = "1" ] && log "DEBUG: $*" || true; }
die() { log "ERROR: $*"; exit 1; }

# === Requirements Validation ===

require_bins() { 
    for b in "$@"; do 
        command -v "$b" >/dev/null || die "Missing binary: $b"; 
    done; 
}

require_swarm() {
    local state; state="$(docker info --format '{{.Swarm.LocalNodeState}}' 2>/dev/null || true)"
    [ "$state" = "active" ] || die "Docker Swarm is not active on this context. Run: docker swarm init (or switch context)."
    debug "Docker Swarm is active"
}

require_stack_file() { 
    [ -f "$1" ] || die "Stack file not found: $1"; 
    debug "Stack file found: $1"
}

# === Environment Loading ===

# Merge simple KEY=VALUE env files in priority order
load_env_files() {
    for f in "$@"; do
        [ -f "$f" ] || continue
        debug "Loading env file: $f"
        while IFS='=' read -r k v; do
            [[ "$k" =~ ^#|^$ ]] && continue
            # Skip readonly variables
            case "$k" in
                UID|EUID|PPID|BASH_*|_|PWD|OLDPWD|HOME|USER|LOGNAME)
                    debug "Skipping readonly/system variable: $k"
                    continue
                    ;;
            esac
            # Remove quotes if present
            v="${v%\"}"
            v="${v#\"}"
            v="${v%\'}"
            v="${v#\'}"
            export "$k"="$v" 2>/dev/null || debug "Could not set $k (may be readonly)"
            debug "Set $k=$v"
        done < <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$f")
    done
}

# === Service Management Helpers ===

# Build fully-qualified service name (FQN) in swarm
svc_fqn() { 
    [ -z "${1:-}" ] && die "svc_fqn requires service name"
    printf '%s_%s\n' "${STACK_NAME}" "$1"
}

# Return a container ID for a running task of this service on this node (best-effort)
svc_pick_container() {
    local service_fqn; service_fqn="$(svc_fqn "$1")"
    docker ps --filter "label=com.docker.swarm.service.name=${service_fqn}" --format '{{.ID}}' | head -n 1
}

# Exec a command in a service's container (local task only)
svc_exec() {
    local svc="$1"; shift || true
    local cid; cid="$(svc_pick_container "$svc")"
    if [ -z "$cid" ]; then
        log "ERROR: No running container found on this node for service: $svc"
        log "Service FQN: $(svc_fqn "$svc")"
        log ""
        log "Troubleshooting tips:"
        log "1. Check if service is running: docker service ps $(svc_fqn "$svc")"
        log "2. If service runs on remote node, use: export DOCKER_CONTEXT=<remote-context>"
        log "3. Or add placement constraint to pin service to this node in stack file"
        die "Service container not accessible"
    fi
    debug "Executing in container $cid: $*"
    docker exec -it "$cid" "$@"
}

# Non-interactive exec for scripted operations
svc_exec_quiet() {
    local svc="$1"; shift || true
    local cid; cid="$(svc_pick_container "$svc")"
    [ -n "$cid" ] || die "No running container found on this node for service: $svc (FQN: $(svc_fqn "$svc"))"
    debug "Executing quietly in container $cid: $*"
    docker exec "$cid" "$@"
}

# Service logs
svc_logs() { 
    debug "Following logs for service: $(svc_fqn "$1")"
    docker service logs -f --no-task-ids "$(svc_fqn "$1")"
}

# Service task list
svc_ps() { 
    debug "Listing tasks for service: $(svc_fqn "$1")"
    docker service ps --no-trunc "$(svc_fqn "$1")"
}

# === Stack Operations ===

stack_deploy() { 
    log "Deploying stack: $STACK_NAME using $STACK_FILE"
    if [ "${DRY_RUN:-0}" = "1" ]; then
        log "DRY RUN: docker stack deploy -c \"${STACK_FILE}\" \"${STACK_NAME}\""
        return 0
    fi
    docker stack deploy -c "${STACK_FILE}" "${STACK_NAME}"
}

stack_rm() { 
    log "Removing stack: $STACK_NAME"
    if [ "${DRY_RUN:-0}" = "1" ]; then
        log "DRY RUN: docker stack rm \"${STACK_NAME}\""
        return 0
    fi
    docker stack rm "${STACK_NAME}"
}

stack_exists() {
    docker stack ls --format '{{.Name}}' | grep -q "^${STACK_NAME}$"
}

# === User Interaction ===

confirm() {
    local prompt="${1:-Are you sure? [y/N]}"
    if [ "${FORCE:-0}" = "1" ]; then
        debug "Auto-confirming due to FORCE=1: $prompt"
        return 0
    fi
    read -r -p "$prompt " ans
    [[ "$ans" =~ ^[Yy]$ ]]
}

pause() {
    if [ "${INTERACTIVE:-1}" = "1" ]; then
        echo
        read -r -p "Press [Enter] key to continue..." _
    fi
}

# === Error Handling ===

# Error trap that shows line number and command
error_handler() {
    local exit_code=$?
    local line_number=$1
    log "ERROR: Command failed at line $line_number (exit code: $exit_code)"
    log "Command: $BASH_COMMAND"
    if [ "${DEBUG:-0}" = "1" ]; then
        log "Call stack:"
        local i=0
        while caller $i; do ((i++)); done
    fi
    exit $exit_code
}

# Set up error trap
trap 'error_handler $LINENO' ERR

# === Utility Functions ===

# Check if a command exists
has_command() {
    command -v "$1" >/dev/null 2>&1
}

# Wait for a service to be ready
wait_for_service() {
    local service="$1"
    local timeout="${2:-60}"
    local interval="${3:-5}"
    local count=0
    
    log "Waiting for service $service to be ready (timeout: ${timeout}s)..."
    
    while [ $count -lt $((timeout / interval)) ]; do
        if svc_pick_container "$service" >/dev/null 2>&1; then
            log "Service $service is ready"
            return 0
        fi
        log "Waiting... ($((count * interval))s elapsed)"
        sleep "$interval"
        count=$((count + 1))
    done
    
    die "Service $service failed to become ready within ${timeout} seconds"
}

# List all services in the stack
stack_services() {
    if stack_exists; then
        docker stack services "${STACK_NAME}"
    else
        log "Stack ${STACK_NAME} does not exist"
        return 1
    fi
}

# Get service status
service_status() {
    local service="$1"
    local fqn; fqn="$(svc_fqn "$service")"
    if docker service ls --format '{{.Name}}' | grep -q "^${fqn}$"; then
        docker service ls --filter "name=${fqn}" --format "table {{.Name}}\t{{.Mode}}\t{{.Replicas}}\t{{.Image}}\t{{.Ports}}"
    else
        log "Service $service ($fqn) not found"
        return 1
    fi
}
