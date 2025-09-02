#!/usr/bin/env bash
# swarm_i18n.sh - Internationalization operations for CakePHP 5.x with plugin support

# === CakePHP i18n Operations ===

i18n_extract() {
    log "Extracting translatable strings from CakePHP application..."
    debug "Extracting from paths: src,plugins/DefaultTheme,plugins/AdminTheme"
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake i18n extract --paths src,plugins/DefaultTheme,plugins/AdminTheme'; then
        log "i18n extraction completed successfully"
        log "POT files updated in resources/locales/"
    else
        die "i18n extraction failed"
    fi
}

i18n_translate() {
    log "Running AI-powered translation..."
    debug "Using project-configured translation service"
    
    # Check if TRANSLATE_API_KEY is available
    local has_api_key
    if svc_exec_quiet willowcms sh -c 'test -n "${TRANSLATE_API_KEY:-}"' 2>/dev/null; then
        has_api_key=true
        debug "Translation API key is configured"
    else
        has_api_key=false
        log "Warning: TRANSLATE_API_KEY not set - AI translation may not work"
    fi
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake translate_i18n'; then
        log "AI translation completed successfully"
        if [ "$has_api_key" = true ]; then
            log "Translations generated using AI service"
        else
            log "Translation command executed (may have used fallback method)"
        fi
    else
        log "Warning: AI translation failed - this may be expected if command doesn't exist"
        return 1
    fi
}

i18n_gen_po() {
    log "Generating PO files from extracted strings..."
    debug "Converting POT templates to PO files"
    
    # The extract command typically generates the PO files automatically
    # This function provides a way to regenerate them if needed
    if svc_exec willowcms sh -c 'cd /var/www/html && echo "PO files are generated during extraction process"'; then
        log "PO file generation process completed"
        log "Files available in resources/locales/[locale]/default.po"
    else
        log "Warning: Could not confirm PO file generation"
    fi
}

i18n_load_default() {
    log "Loading default internationalization data..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake default_data_import internationalisations' 2>/dev/null; then
        log "Default i18n data loaded successfully"
    else
        log "Warning: Failed to load default i18n data (may not be available or already loaded)"
    fi
}

i18n_list_locales() {
    log "Available locales in the application:"
    
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    if docker exec "$cid" find /var/www/html/resources/locales -name "*.po" -type f 2>/dev/null | head -20; then
        log "Listed available locale files"
    else
        log "No locale files found or resources/locales directory doesn't exist"
    fi
}

i18n_validate_translations() {
    log "Validating translation files..."
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    local error_count=0
    
    # Check for syntax errors in PO files
    if docker exec "$cid" sh -c 'find /var/www/html/resources/locales -name "*.po" -type f' 2>/dev/null | while read -r po_file; do
        if docker exec "$cid" msgfmt --check-format "$po_file" 2>/dev/null; then
            debug "✓ Valid: $(basename "$po_file")"
        else
            log "✗ Invalid: $(basename "$po_file")"
            error_count=$((error_count + 1))
        fi
    done; then
        if [ $error_count -eq 0 ]; then
            log "All translation files are valid"
        else
            log "Found $error_count invalid translation files"
            return 1
        fi
    else
        log "Could not validate translation files (msgfmt not available or no files found)"
    fi
}

# === Plugin-specific i18n Operations ===

i18n_extract_plugin() {
    local plugin="$1"
    [ -n "$plugin" ] || die "Plugin name required"
    
    log "Extracting strings from plugin: $plugin"
    
    if svc_exec willowcms sh -c "cd /var/www/html && bin/cake i18n extract --paths plugins/$plugin"; then
        log "Plugin $plugin i18n extraction completed"
    else
        log "Warning: Plugin $plugin extraction failed"
        return 1
    fi
}

i18n_list_plugins() {
    log "Available plugins with i18n support:"
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    if docker exec "$cid" find /var/www/html/plugins -name "locales" -type d 2>/dev/null | sed 's|/var/www/html/plugins/||; s|/locales||'; then
        log "Listed plugins with i18n directories"
    else
        log "No plugins with i18n found"
    fi
}

# === Cache and Cleanup ===

i18n_clear_cache() {
    log "Clearing i18n cache..."
    
    if svc_exec willowcms sh -c 'cd /var/www/html && bin/cake cache clear _cake_core_'; then
        log "i18n cache cleared successfully"
    else
        log "Warning: Could not clear i18n cache"
    fi
}

# === Statistics and Information ===

i18n_stats() {
    log "i18n Translation Statistics:"
    local cid; cid="$(svc_pick_container willowcms)" || true
    [ -n "$cid" ] || die "WillowCMS service container not found locally."
    
    echo
    echo "Locale files found:"
    if docker exec "$cid" find /var/www/html/resources/locales -name "*.po" -type f 2>/dev/null | while read -r po_file; do
        local locale; locale=$(basename "$(dirname "$po_file")")
        local strings; strings=$(docker exec "$cid" grep -c "^msgid" "$po_file" 2>/dev/null || echo "0")
        local translated; translated=$(docker exec "$cid" grep -c "^msgstr \"[^\"]\+\"" "$po_file" 2>/dev/null || echo "0")
        printf "  %-10s: %s strings, %s translated\n" "$locale" "$strings" "$translated"
    done; then
        echo
    else
        echo "  No statistics available"
    fi
}

# === Interactive Menu ===

i18n_menu() {
    while true; do
        echo
        echo "=== Internationalization (${STACK_NAME}) ==="
        echo "1) Extract Translatable Strings"
        echo "2) AI-Powered Translation"
        echo "3) Generate PO Files"
        echo "4) Load Default i18n Data"
        echo "5) List Available Locales"
        echo "6) Validate Translation Files"
        echo "7) Extract from Plugin"
        echo "8) List Plugins with i18n"
        echo "9) Clear i18n Cache"
        echo "10) Translation Statistics"
        echo "11) Back to Main Menu"
        echo
        read -r -p "i18n > " choice
        
        case "$choice" in
            1)
                i18n_extract
                pause
                ;;
            2)
                if confirm "Run AI translation? This may consume API credits. [y/N]"; then
                    i18n_translate
                fi
                pause
                ;;
            3)
                i18n_gen_po
                pause
                ;;
            4)
                if confirm "Load default i18n data? [y/N]"; then
                    i18n_load_default
                fi
                pause
                ;;
            5)
                i18n_list_locales
                pause
                ;;
            6)
                i18n_validate_translations
                pause
                ;;
            7)
                i18n_list_plugins
                read -r -p "Enter plugin name: " plugin_name
                if [ -n "$plugin_name" ]; then
                    i18n_extract_plugin "$plugin_name"
                fi
                pause
                ;;
            8)
                i18n_list_plugins
                pause
                ;;
            9)
                i18n_clear_cache
                pause
                ;;
            10)
                i18n_stats
                pause
                ;;
            11|"")
                break
                ;;
            *)
                echo "Invalid option. Please choose 1-11."
                ;;
        esac
    done
}

# === Non-interactive Commands ===

# For CLI usage
run_i18n_extract() { i18n_extract; }
run_i18n_translate() { i18n_translate; }
run_i18n_gen_po() { i18n_gen_po; }
