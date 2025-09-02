#!/usr/bin/env bash
# swarm_docker.sh - Docker Swarm stack and service management operations

# === Non-interactive Command Functions ===

swarm_deploy() {
    log "Deploying/updating Docker Swarm stack..."
    stack_deploy
}

swarm_remove() {
    if ! stack_exists; then
        log "Stack ${STACK_NAME} does not exist"
        return 1
    fi
    log "Removing Docker Swarm stack..."
    stack_rm
}

swarm_restart() {
    local service="${1:-}"
    if [ -n "$service" ]; then
        log "Restarting service: $service"
        docker service update --force "$(svc_fqn "$service")"
    else
        log "Restarting entire stack..."
        if confirm "This will restart the entire stack. Continue? [y/N]"; then
            stack_rm
            log "Waiting for stack to be removed..."
            sleep 10
            stack_deploy
        fi
    fi
}

swarm_scale() {
    local service="$1"
    local replicas="$2"
    [ -n "$service" ] || die "Service name required"
    [ -n "$replicas" ] || die "Replica count required"
    
    log "Scaling service $service to $replicas replicas..."
    docker service scale "$(svc_fqn "$service")"="$replicas"
}

swarm_update_image() {
    local service="$1"
    local image="$2"
    [ -n "$service" ] || die "Service name required"
    [ -n "$image" ] || die "Image name required"
    
    log "Updating service $service to image: $image"
    docker service update --image "$image" --update-order start-first --update-parallelism 1 --update-delay 10s "$(svc_fqn "$service")"
}

swarm_rollback() {
    local service="$1"
    [ -n "$service" ] || die "Service name required"
    
    log "Rolling back service: $service"
    docker service rollback "$(svc_fqn "$service")"
}

swarm_services() {
    log "Listing services in stack: ${STACK_NAME}"
    stack_services
}

swarm_tasks() {
    local service="$1"
    [ -n "$service" ] || die "Service name required"
    
    log "Listing tasks for service: $service"
    svc_ps "$service"
}

swarm_prune() {
    if confirm "This will remove all unused Docker resources. Continue? [y/N]"; then
        log "Pruning Docker system..."
        docker system prune -af --volumes
    fi
}

# === Service Management Helpers ===

service_restart_rolling() {
    local service="$1"
    [ -n "$service" ] || die "Service name required"
    
    log "Performing rolling restart of service: $service"
    docker service update --force "$(svc_fqn "$service")"
}

service_update_config() {
    local service="$1"
    [ -n "$service" ] || die "Service name required"
    
    echo "Current service configuration:"
    docker service inspect "$(svc_fqn "$service")" --pretty
    echo
    read -r -p "Enter update command (e.g., --env KEY=VALUE): " update_args
    
    if [ -n "$update_args" ]; then
        log "Updating service $service with: $update_args"
        # shellcheck disable=SC2086
        docker service update $update_args "$(svc_fqn "$service")"
    fi
}

# === Interactive Menu ===

docker_menu() {
    while true; do
        echo
        echo "=== Docker/Swarm Management (${STACK_NAME}) ==="
        echo "1) Deploy/Update Stack"
        echo "2) Remove Stack"
        echo "3) Stack Status"
        echo "4) Follow App Logs (willowcms)"
        echo "5) Follow Service Logs"
        echo "6) Restart Service"
        echo "7) Scale Service"
        echo "8) Update Service Image"
        echo "9) Rollback Service"
        echo "10) List Services"
        echo "11) View Service Tasks"
        echo "12) Service Configuration"
        echo "13) Prune System (cleanup)"
        echo "14) Back to Main Menu"
        echo
        read -r -p "Docker/Swarm > " choice
        
        case "$choice" in
            1)
                log "Deploying/updating stack..."
                swarm_deploy
                pause
                ;;
            2)
                if confirm "Remove stack ${STACK_NAME}? This will stop all services. [y/N]"; then
                    swarm_remove
                fi
                pause
                ;;
            3)
                log "Stack status:"
                if stack_exists; then
                    stack_services
                    echo
                    log "Detailed service information:"
                    docker stack ps "${STACK_NAME}" --no-trunc
                else
                    log "Stack ${STACK_NAME} does not exist"
                fi
                pause
                ;;
            4)
                log "Following willowcms logs (Ctrl+C to stop)..."
                svc_logs "willowcms" || true
                ;;
            5)
                read -r -p "Enter service name: " service_name
                if [ -n "$service_name" ]; then
                    log "Following $service_name logs (Ctrl+C to stop)..."
                    svc_logs "$service_name" || true
                fi
                ;;
            6)
                read -r -p "Enter service name to restart: " service_name
                if [ -n "$service_name" ]; then
                    if confirm "Restart service $service_name? [y/N]"; then
                        swarm_restart "$service_name"
                    fi
                fi
                pause
                ;;
            7)
                read -r -p "Enter service name: " service_name
                read -r -p "Enter number of replicas: " replicas
                if [ -n "$service_name" ] && [ -n "$replicas" ]; then
                    if [[ "$replicas" =~ ^[0-9]+$ ]]; then
                        swarm_scale "$service_name" "$replicas"
                    else
                        log "ERROR: Replica count must be a number"
                    fi
                fi
                pause
                ;;
            8)
                read -r -p "Enter service name: " service_name
                read -r -p "Enter new image (repo:tag): " image_name
                if [ -n "$service_name" ] && [ -n "$image_name" ]; then
                    if confirm "Update $service_name to $image_name? [y/N]"; then
                        swarm_update_image "$service_name" "$image_name"
                    fi
                fi
                pause
                ;;
            9)
                read -r -p "Enter service name to rollback: " service_name
                if [ -n "$service_name" ]; then
                    if confirm "Rollback service $service_name? [y/N]"; then
                        swarm_rollback "$service_name"
                    fi
                fi
                pause
                ;;
            10)
                swarm_services
                pause
                ;;
            11)
                read -r -p "Enter service name: " service_name
                if [ -n "$service_name" ]; then
                    swarm_tasks "$service_name"
                fi
                pause
                ;;
            12)
                read -r -p "Enter service name: " service_name
                if [ -n "$service_name" ]; then
                    service_update_config "$service_name"
                fi
                pause
                ;;
            13)
                swarm_prune
                pause
                ;;
            14|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-14."
                ;;
        esac
    done
}

# === Stack Validation ===

validate_stack_file() {
    local stack_file="$1"
    log "Validating stack file: $stack_file"
    
    if ! docker compose -f "$stack_file" config >/dev/null 2>&1; then
        log "ERROR: Stack file validation failed"
        log "Run: docker compose -f $stack_file config"
        return 1
    fi
    
    log "Stack file validation passed"
    return 0
}

# === Backup and Restore Stack State ===

backup_stack_config() {
    local backup_file="backups/stack_config_$(date '+%Y%m%d_%H%M%S').json"
    mkdir -p "$(dirname "$backup_file")"
    
    if stack_exists; then
        log "Backing up stack configuration to: $backup_file"
        docker stack services "${STACK_NAME}" --format json > "$backup_file"
        log "Stack configuration backed up successfully"
    else
        log "No stack to backup (${STACK_NAME} does not exist)"
        return 1
    fi
}
