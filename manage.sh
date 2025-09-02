#!/bin/bash

# Debug mode flag
DEBUG_MODE=false

# Function to show usage information
show_usage() {
    cat << EOF
WillowCMS Management Script

USAGE:
    $0 [OPTIONS]

OPTIONS:
    -d, --debug     Enable debug output
    -h, --help      Show this help message

DESCRIPTION:
    Interactive management tool for WillowCMS development and maintenance tasks.
    Provides menu-driven access to data management, internationalization,
    asset management, and system operations.

EXAMPLES:
    $0              # Run in normal mode
    $0 --debug      # Run with debug output enabled
    $0 -d           # Run with debug output enabled (short form)

EOF
}

# Parse command line arguments for debug flag
for arg in "$@"; do
    case $arg in
        --debug|-d)
            DEBUG_MODE=true
            shift
            ;;
        --help|-h)
            show_usage
            exit 0
            ;;
        *)
            echo "Error: Unknown option '$arg'"
            echo "Use --help to see available options."
            exit 1
            ;;
    esac
done

# Debug output function
debug_output() {
    if [ "$DEBUG_MODE" = true ]; then
        echo "DEBUG (manage.sh): $1"
    fi
}

# Export DEBUG_MODE and debug_output function for use by modules
export DEBUG_MODE
export -f debug_output

# Output debug information only if debug flag is set
if [ "$DEBUG_MODE" = true ]; then
    debug_output "Script using shell: $SHELL (effective shell is bash)"
    debug_output "Path to bash: $(which bash)" # Should be /bin/bash or /usr/bin/bash
    debug_output "Bash version: $(bash --version | head -n 1)"
    debug_output "Path to grep: $(which grep)" # Critical: Is this GNU grep?
    debug_output "Grep version: $(grep --version | head -n 1)" # Critical
    debug_output "PATH variable in script: $PATH"
    debug_output "--- End of initial script debug (manage.sh) ---"
fi

# Load all modules
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
MODULE_DIR="${SCRIPT_DIR}/tool_modules"

# Check if module directory exists
if [ ! -d "$MODULE_DIR" ]; then
    echo "Error: Module directory not found at: $MODULE_DIR"
    exit 1
fi

# Source each module
source "${MODULE_DIR}/common.sh"
source "${MODULE_DIR}/ui.sh"
source "${MODULE_DIR}/service_checks.sh"
source "${MODULE_DIR}/data_management.sh"
source "${MODULE_DIR}/internationalization.sh"
source "${MODULE_DIR}/asset_management.sh"  # New module
source "${MODULE_DIR}/system.sh"
source "${MODULE_DIR}/docker_management.sh"  # Docker management module

# Start the main function
main
