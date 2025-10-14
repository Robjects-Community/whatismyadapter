#!/bin/bash

# Debug command dispatcher
execute_debug_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        25)
            echo "Running WillowCMS Debug Script..."
            echo "==============================================="
            if [ -f "tools/debugging/debug_willowcms.sh" ]; then
                bash tools/debugging/debug_willowcms.sh
            else
                echo "Error: Debug script not found at tools/debugging/debug_willowcms.sh"
                return 1
            fi
            ;;
        *)
            echo "Error: Invalid debug option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}