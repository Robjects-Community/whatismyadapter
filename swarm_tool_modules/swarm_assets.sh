#!/usr/bin/env bash
# swarm_assets.sh - Asset management operations: build, publish, cache clearing for CakePHP

# === Asset Build Operations ===

assets_build() {
    log "Building application assets..."
    
    # Build frontend assets if package.json exists
    log "Checking for Node.js assets..."
    if svc_exec_quiet willowcms test -f /var/www/html/package.json 2>/dev/null; then
        log "Found package.json, building Node.js assets..."
        if svc_exec willowcms sh -c 'cd /var/www/html && npm ci && npm run build'; then
            log "Node.js assets built successfully"
        else
            log "Warning: Node.js asset build failed"
        fi
    else
        debug "No package.json found, skipping Node.js asset build"
    fi
    
    # Build PHP autoloader
    log "Optimizing PHP autoloader..."
    if svc_exec willowcms sh -c 'cd /var/www/html && composer dumpautoload -o --no-interaction'; then
        log "PHP autoloader optimized successfully"
    else
        log "Warning: PHP autoloader optimization failed"
    fi
    
    # Build plugin assets
    log "Building plugin assets..."
    assets_build_plugins
}

assets_build_plugins() {
    log "Building assets for plugins..."
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    # Check for plugin-specific builds
    local plugins
    if plugins=$(docker exec "$cid" find /var/www/html/plugins -maxdepth 1 -type d -name "*Theme" 2>/dev/null); then
        echo "$plugins" | while read -r plugin_path; do
            local plugin_name; plugin_name=$(basename "$plugin_path")
            debug "Checking plugin: $plugin_name"
            
            # Check if plugin has package.json
            if docker exec "$cid" test -f "$plugin_path/package.json" 2>/dev/null; then
                log "Building assets for plugin: $plugin_name"
                if docker exec "$cid" sh -c "cd $plugin_path && npm ci && npm run build" 2>/dev/null; then
                    log "✓ Plugin $plugin_name assets built"
                else
                    log "⚠ Plugin $plugin_name asset build failed"
                fi
            fi
        done
    else
        debug "No theme plugins found for asset building"
    fi
}

# === Asset Publishing ===

assets_publish() {
    log "Publishing plugin assets..."
    
    # Copy plugin assets to webroot using CakePHP command
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake plugin assets copy --overwrite'; then
        log "Plugin assets published successfully"
    else
        log "Warning: Plugin asset publishing failed (command may not exist)"
    fi
    
    # Manual asset publishing for themes
    assets_publish_themes
}

assets_publish_themes() {
    log "Publishing theme assets..."
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    # Publish DefaultTheme assets
    if docker exec "$cid" test -d /var/www/html/plugins/DefaultTheme/webroot 2>/dev/null; then
        log "Publishing DefaultTheme assets..."
        if docker exec "$cid" sh -c 'cp -r /var/www/html/plugins/DefaultTheme/webroot/* /var/www/html/webroot/ 2>/dev/null'; then
            log "✓ DefaultTheme assets published"
        else
            debug "DefaultTheme assets already up to date or no assets to copy"
        fi
    fi
    
    # Publish AdminTheme assets
    if docker exec "$cid" test -d /var/www/html/plugins/AdminTheme/webroot 2>/dev/null; then
        log "Publishing AdminTheme assets..."
        if docker exec "$cid" sh -c 'cp -r /var/www/html/plugins/AdminTheme/webroot/* /var/www/html/webroot/ 2>/dev/null'; then
            log "✓ AdminTheme assets published"
        else
            debug "AdminTheme assets already up to date or no assets to copy"
        fi
    fi
}

# === Cache Management ===

cache_clear_all() {
    log "Clearing all application caches..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake cache clear_all'; then
        log "All caches cleared successfully"
    else
        log "Warning: Cache clearing failed"
        return 1
    fi
}

cache_clear_model() {
    log "Clearing model cache..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake cache clear _cake_model_'; then
        log "Model cache cleared successfully"
    else
        log "Warning: Model cache clearing failed"
    fi
}

cache_clear_core() {
    log "Clearing core cache..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake cache clear _cake_core_'; then
        log "Core cache cleared successfully"
    else
        log "Warning: Core cache clearing failed"
    fi
}

cache_warmup() {
    log "Warming up application caches..."
    
    # This would depend on specific cache warmup commands in WillowCMS
    if svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake --help | grep -q warmup' 2>/dev/null; then
        if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake cache warmup'; then
            log "Cache warmup completed"
        else
            log "Warning: Cache warmup failed"
        fi
    else
        log "Cache warmup command not available, performing basic operations..."
        # Perform some basic operations to warm caches
        svc_exec_quiet willowcms sh -c 'cd /var/www/html && bin/cake routes' >/dev/null 2>&1 || true
        log "Basic cache operations completed"
    fi
}

# === Asset Optimization ===

assets_optimize() {
    log "Optimizing application assets..."
    
    # Optimize images if imageoptim or similar is available
    assets_optimize_images
    
    # Minify and compress assets
    assets_minify
    
    # Set proper permissions
    assets_fix_permissions
}

assets_optimize_images() {
    log "Optimizing images..."
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    # This is a placeholder for image optimization
    # Could use imageoptim, tinypng, or other tools if available
    if docker exec "$cid" which optipng >/dev/null 2>&1; then
        log "Optimizing PNG images..."
        docker exec "$cid" find /var/www/html/webroot -name "*.png" -exec optipng -quiet {} \; 2>/dev/null || true
    else
        debug "optipng not available, skipping PNG optimization"
    fi
    
    if docker exec "$cid" which jpegoptim >/dev/null 2>&1; then
        log "Optimizing JPEG images..."
        docker exec "$cid" find /var/www/html/webroot -name "*.jpg" -o -name "*.jpeg" -exec jpegoptim --quiet {} \; 2>/dev/null || true
    else
        debug "jpegoptim not available, skipping JPEG optimization"
    fi
    
    log "Image optimization completed"
}

assets_minify() {
    log "Minifying assets..."
    
    # This would typically be handled by the build process (webpack, gulp, etc.)
    # but can also be done server-side if needed
    log "Asset minification should be handled by build process (npm run build)"
}

assets_fix_permissions() {
    log "Fixing asset permissions..."
    
    if svc_exec willowcms sh -c 'chmod -R 755 /var/www/html/webroot && chown -R nginx:nginx /var/www/html/webroot'; then
        log "Asset permissions fixed"
    else
        log "Warning: Could not fix asset permissions"
    fi
}

# === Development Tools ===

assets_watch() {
    log "Starting asset watch mode..."
    log "This will watch for changes and rebuild assets automatically"
    
    if svc_exec_quiet willowcms test -f /var/www/html/package.json 2>/dev/null; then
        log "Starting Node.js watch process..."
        # This runs in foreground, user can Ctrl+C to stop
        svc_exec willowcms sh -c 'cd /var/www/html && npm run watch' || log "Watch mode ended"
    else
        log "No package.json found, cannot start watch mode"
    fi
}

assets_dev_server() {
    log "Starting development server..."
    
    if svc_exec_quiet willowcms test -f /var/www/html/package.json 2>/dev/null; then
        log "Starting development server..."
        svc_exec willowcms sh -c 'cd /var/www/html && npm run dev' || log "Development server stopped"
    else
        log "No package.json found, cannot start development server"
    fi
}

# === Information and Statistics ===

assets_info() {
    log "Asset Information:"
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    echo
    echo "Webroot directory size:"
    docker exec "$cid" du -sh /var/www/html/webroot 2>/dev/null || echo "  Cannot calculate size"
    
    echo
    echo "Asset files by type:"
    if docker exec "$cid" find /var/www/html/webroot -type f 2>/dev/null | head -100; then
        echo
        echo "File type summary:"
        docker exec "$cid" find /var/www/html/webroot -type f 2>/dev/null | sed 's/.*\.//' | sort | uniq -c | sort -nr | head -10
    else
        echo "  Cannot list asset files"
    fi
}

# === Interactive Menu ===

assets_menu() {
    while true; do
        echo
        echo "=== Asset Management (${STACK_NAME}) ==="
        echo "1) Build Assets"
        echo "2) Publish Plugin Assets"
        echo "3) Clear All Caches"
        echo "4) Clear Model Cache"
        echo "5) Clear Core Cache"
        echo "6) Warm Up Caches"
        echo "7) Optimize Assets"
        echo "8) Watch Assets (dev mode)"
        echo "9) Start Dev Server"
        echo "10) Asset Information"
        echo "11) Fix Permissions"
        echo "12) Back to Main Menu"
        echo
        read -r -p "Assets > " choice
        
        case "$choice" in
            1)
                assets_build
                pause
                ;;
            2)
                assets_publish
                pause
                ;;
            3)
                cache_clear_all
                pause
                ;;
            4)
                cache_clear_model
                pause
                ;;
            5)
                cache_clear_core
                pause
                ;;
            6)
                cache_warmup
                pause
                ;;
            7)
                if confirm "Optimize all assets? This may take some time. [y/N]"; then
                    assets_optimize
                fi
                pause
                ;;
            8)
                log "Starting watch mode. Press Ctrl+C to stop."
                assets_watch
                ;;
            9)
                log "Starting development server. Press Ctrl+C to stop."
                assets_dev_server
                ;;
            10)
                assets_info
                pause
                ;;
            11)
                assets_fix_permissions
                pause
                ;;
            12|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-12."
                ;;
        esac
    done
}

# === Non-interactive Commands ===

# For CLI usage
run_assets_build() { assets_build; }
run_assets_publish() { assets_publish; }
run_cache_clear() { cache_clear_all; }
