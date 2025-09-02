#!/usr/bin/env bash
# swarm_system.sh - System operations: composer, migrations, testing, health checks, and shells

# === Composer Operations ===

composer_install() {
    log "Installing Composer dependencies..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader'; then
        log "Composer dependencies installed successfully"
    else
        die "Composer install failed"
    fi
}

composer_update() {
    log "Updating Composer dependencies..."
    
    if confirm "This will update all Composer dependencies. Continue? [y/N]"; then
        if svc_exec willowcms sh -c 'cd /var/www/html && composer update --no-interaction --optimize-autoloader'; then
            log "Composer dependencies updated successfully"
        else
            die "Composer update failed"
        fi
    else
        log "Composer update cancelled"
    fi
}

composer_audit() {
    log "Auditing Composer dependencies for security issues..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && composer audit'; then
        log "Dependency audit completed"
    else
        log "Warning: Dependency audit found issues or command not available"
    fi
}

composer_outdated() {
    log "Checking for outdated Composer dependencies..."
    
    svc_exec willowcms sh -c 'cd /var/www/html && composer outdated'
}

# === Database Migrations ===

migrate_db() {
    log "Running database migrations..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake migrations migrate'; then
        log "Database migrations completed successfully"
    else
        die "Database migrations failed"
    fi
}

rollback_db() {
    log "Rolling back database migrations..."
    
    if confirm "This will rollback the last migration. Continue? [y/N]"; then
        if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake migrations rollback'; then
            log "Database rollback completed successfully"
        else
            die "Database rollback failed"
        fi
    else
        log "Database rollback cancelled"
    fi
}

migration_status() {
    log "Checking migration status..."
    
    svc_exec willowcms sh -c 'cd /var/www/html && bin/cake migrations status'
}

migrate_plugins() {
    log "Running plugin migrations..."
    
    # Migrate AdminTheme plugin
    if svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake --help migrations | grep -q "\-p"' 2>/dev/null; then
        log "Migrating AdminTheme plugin..."
        svc_exec willowcms sh -c 'cd /var/www/html && bin/cake migrations migrate -p AdminTheme' || log "AdminTheme migrations not available"
        
        log "Migrating DefaultTheme plugin..."
        svc_exec willowcms sh -c 'cd /var/www/html && bin/cake migrations migrate -p DefaultTheme' || log "DefaultTheme migrations not available"
    else
        log "Plugin migration support not available"
    fi
}

# === Testing ===

phpunit_run() {
    log "Running PHPUnit tests..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && vendor/bin/phpunit --colors=always'; then
        log "All tests passed"
    else
        log "Some tests failed - check output above"
        return 1
    fi
}

phpunit_coverage() {
    log "Running PHPUnit tests with coverage..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && env XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html tmp/coverage'; then
        log "Tests with coverage completed"
        log "Coverage report available in tmp/coverage/"
    else
        log "Coverage tests failed or Xdebug not available"
    fi
}

phpunit_specific() {
    local test_file="$1"
    [ -n "$test_file" ] || die "Test file path required"
    
    log "Running specific test: $test_file"
    
    svc_exec willowcms sh -c "cd /var/www/html && vendor/bin/phpunit --colors=always $test_file"
}

# === Code Quality ===

code_style_check() {
    log "Checking code style with PHP CodeSniffer..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && vendor/bin/phpcs --colors -p'; then
        log "Code style check passed"
    else
        log "Code style issues found - run code style fix to auto-correct"
        return 1
    fi
}

code_style_fix() {
    log "Fixing code style with PHP Code Beautifier..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && vendor/bin/phpcbf --colors -p'; then
        log "Code style fixes applied"
    else
        log "Some code style issues could not be automatically fixed"
    fi
}

static_analysis() {
    log "Running static analysis with PHPStan..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && vendor/bin/phpstan analyze'; then
        log "Static analysis passed"
    else
        log "Static analysis found issues"
        return 1
    fi
}

run_all_checks() {
    log "Running all code quality checks..."
    
    local failed=0
    
    log "1/3 Code Style Check..."
    code_style_check || failed=1
    
    log "2/3 Static Analysis..."
    static_analysis || failed=1
    
    log "3/3 Tests..."
    phpunit_run || failed=1
    
    if [ $failed -eq 0 ]; then
        log "All checks passed ✓"
    else
        log "Some checks failed ✗"
        return 1
    fi
}

# === Application Management ===

app_shell() {
    log "Opening interactive shell in willowcms container..."
    svc_exec willowcms bash
}

app_cake_shell() {
    log "Starting CakePHP shell..."
    svc_exec willowcms sh -c 'cd /var/www/html && bin/cake'
}

create_admin_user() {
    log "Creating admin user..."
    
    read -r -p "Username: " username
    read -r -p "Email: " email
    read -r -s -p "Password: " password
    echo
    
    if [ -n "$username" ] && [ -n "$email" ] && [ -n "$password" ]; then
        if svc_exec willowcms sh -c "cd /var/www/html && bin/cake create_user -u \"$username\" -e \"$email\" -p \"$password\" -a 1"; then
            log "Admin user created successfully"
        else
            log "Failed to create admin user"
        fi
    else
        log "All fields are required"
    fi
}

# === Health Checks ===

health_check() {
    log "Performing health check..."
    
    echo "=== Stack Status ==="
    if stack_exists; then
        docker stack services "${STACK_NAME}"
        echo
        docker stack ps "${STACK_NAME}" --no-trunc
    else
        log "Stack ${STACK_NAME} does not exist"
        return 1
    fi
    
    echo
    echo "=== WillowCMS Service Details ==="
    if service_status "willowcms"; then
        echo
        svc_ps "willowcms"
    else
        log "WillowCMS service not found"
    fi
    
    echo
    echo "=== Database Connectivity ==="
    if svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake check_table_exists settings' 2>/dev/null; then
        log "✓ Database connection OK"
    else
        log "✗ Database connection failed"
    fi
    
    echo
    echo "=== Application Status ==="
    local cid; cid="$(svc_pick_container willowcms)" || true
    if [ -n "$cid" ]; then
        log "✓ WillowCMS container running: $cid"
        
        # Check if web server is responding
        if docker exec "$cid" curl -f http://localhost/ >/dev/null 2>&1; then
            log "✓ Web server responding"
        else
            log "✗ Web server not responding"
        fi
    else
        log "✗ No WillowCMS container found locally"
    fi
}

service_logs_all() {
    log "Showing logs for all services..."
    
    if stack_exists; then
        local services
        services=$(docker stack services "${STACK_NAME}" --format "{{.Name}}" | sed "s/^${STACK_NAME}_//")
        
        for service in $services; do
            echo "=== Logs for $service ==="
            docker service logs --tail 10 "$(svc_fqn "$service")" 2>/dev/null || log "No logs available for $service"
            echo
        done
    else
        log "Stack ${STACK_NAME} does not exist"
    fi
}

# === Maintenance ===

app_maintenance_on() {
    log "Enabling maintenance mode..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && touch tmp/maintenance.lock'; then
        log "Maintenance mode enabled"
        log "Application will show maintenance page to users"
    else
        log "Failed to enable maintenance mode"
    fi
}

app_maintenance_off() {
    log "Disabling maintenance mode..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && rm -f tmp/maintenance.lock'; then
        log "Maintenance mode disabled"
    else
        log "Failed to disable maintenance mode (may already be off)"
    fi
}

# === Interactive Menu ===

system_menu() {
    while true; do
        echo
        echo "=== System Operations (${STACK_NAME}) ==="
        echo "1) Composer Install"
        echo "2) Composer Update"
        echo "3) Database Migrations"
        echo "4) Database Rollback"
        echo "5) Migration Status"
        echo "6) Run Tests (PHPUnit)"
        echo "7) Test Coverage"
        echo "8) Code Style Check"
        echo "9) Code Style Fix"
        echo "10) Static Analysis"
        echo "11) Run All Checks"
        echo "12) App Shell (bash)"
        echo "13) CakePHP Shell"
        echo "14) Create Admin User"
        echo "15) Health Check"
        echo "16) View All Logs"
        echo "17) Maintenance Mode Toggle"
        echo "18) Back to Main Menu"
        echo
        read -r -p "System > " choice
        
        case "$choice" in
            1)
                composer_install
                pause
                ;;
            2)
                composer_update
                pause
                ;;
            3)
                migrate_db
                migrate_plugins
                pause
                ;;
            4)
                rollback_db
                pause
                ;;
            5)
                migration_status
                pause
                ;;
            6)
                phpunit_run
                pause
                ;;
            7)
                phpunit_coverage
                pause
                ;;
            8)
                code_style_check
                pause
                ;;
            9)
                code_style_fix
                pause
                ;;
            10)
                static_analysis
                pause
                ;;
            11)
                run_all_checks
                pause
                ;;
            12)
                app_shell
                ;;
            13)
                app_cake_shell
                ;;
            14)
                create_admin_user
                pause
                ;;
            15)
                health_check
                pause
                ;;
            16)
                service_logs_all
                pause
                ;;
            17)
                echo "Current maintenance status:"
                if svc_exec_quiet willowcms test -f /var/www/html/tmp/maintenance.lock 2>/dev/null; then
                    echo "  Maintenance mode: ON"
                    if confirm "Disable maintenance mode? [y/N]"; then
                        app_maintenance_off
                    fi
                else
                    echo "  Maintenance mode: OFF"
                    if confirm "Enable maintenance mode? [y/N]"; then
                        app_maintenance_on
                    fi
                fi
                pause
                ;;
            18|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-18."
                ;;
        esac
    done
}

# === Non-interactive Commands ===

# For CLI usage
run_composer_install() { composer_install; }
run_composer_update() { composer_update; }
run_migrate() { migrate_db; migrate_plugins; }
run_tests() { phpunit_run; }
run_health_check() { health_check; }
