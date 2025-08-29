#!/bin/bash

# Execute internationalization commands (options 5-8)
execute_i18n_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        5)
            echo "Extracting i18n Messages..."
            docker compose exec willowcms bin/cake i18n extract \
                --paths /var/www/html/src,/var/www/html/plugins,/var/www/html/templates
            ;;
        6)
            echo "Loading Default i18n..."
            docker compose exec willowcms bin/cake load_default18n
            ;;
        7)
            echo "Running i18n Translation..."
            docker compose exec willowcms bin/cake translate_i18n
            ;;
        8)
            echo "Generating PO Files..."
            docker compose exec willowcms bin/cake generate_po_files
            ;;
        *)
            echo "Error: Invalid internationalization option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}