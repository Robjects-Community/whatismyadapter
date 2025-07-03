#!/bin/bash

# Set strict error handling
# -e: exit immediately if a command exits with a non-zero status.
# -u: treat unset variables as an error when substituting.
set -eu
# Attempt to set pipefail; if not available (like in dash), it won't cause an error and script continues.
(set -o pipefail 2>/dev/null) && set -o pipefail

# Global variables
CURRENT_CHOICE_FOR_SERVICE_CHECK=""

# Function to pause and wait for user input
pause() {
    echo
    read -r -p "Press [Enter] key to continue..." _
}

# Function to execute commands - dispatches to the appropriate module
execute_command() {
    local cmd_choice="$1"
    case "$cmd_choice" in
        1|2|3|4|5)
            execute_data_command "$cmd_choice"
            ;;
        6|7|8|9)
            # Convert to original numbering for i18n commands
            local i18n_choice=$((cmd_choice - 1))
            execute_i18n_command "$i18n_choice"
            ;;
        10|11|12)
            # Convert to original numbering for asset commands
            local asset_choice=$((cmd_choice - 1))
            execute_asset_command "$asset_choice"
            ;;
        13|14|15|0)
            # Convert to original numbering for system commands
            local system_choice
            case "$cmd_choice" in
                13) system_choice=11 ;;
                14) system_choice=12 ;;
                15) system_choice=13 ;;
                0) system_choice=0 ;;
            esac
            execute_system_command "$system_choice"
            ;;
        *)
            echo "Error: Invalid option '$cmd_choice'"
            return 1
            ;;
    esac
    return $?
}

# Main function that runs the program loop
main() {
    # Check for Docker and Docker Compose
    if ! command -v docker >/dev/null; then 
        echo "Error: 'docker' command not found. Please install Docker."
        exit 1
    fi
    
    if ! docker compose version >/dev/null 2>&1; then
      if command -v docker-compose >/dev/null; then 
        echo "Error: This script requires Docker Compose V2 ('docker compose'), but only found 'docker-compose' (V1). Please update or use 'docker compose'."
      else 
        echo "Error: 'docker compose' (V2+) command not found. Please ensure it's installed and in your PATH."
      fi
      exit 1
    fi

    # Check if in a docker compose project directory
    if ! docker compose config >/dev/null 2>&1; then
        echo "Error: Not a valid Docker Compose project, or 'docker-compose.yml' not found."
        echo "Please run this script from the directory containing your 'docker-compose.yml'."
        exit 1
    fi

    # Main program loop
    while true; do
        show_header
        show_menu
        read -r -p "Enter your choice [0-15]: " choice_input

        if [ -z "$choice_input" ]; then # Handle empty input
            echo "Error: No input. Please enter a number."
            pause
            continue
        fi
        
        if ! echo "$choice_input" | grep -Eq '^[0-9]+$'; then
            echo "Error: Invalid input. Please enter a number."
            pause
            continue
        fi

        # Convert to number for comparison
        local choice_num
        choice_num=$((choice_input))

        if [ "$choice_num" -lt 0 ] || [ "$choice_num" -gt 15 ]; then
            echo "Error: Please enter a number between 0 and 15."
            pause
            continue
        fi

        CURRENT_CHOICE_FOR_SERVICE_CHECK="$choice_num"
        # Skip Docker service checks for Exit (0) and Host System Update (15)
        if [ "$choice_num" -ne 0 ] && [ "$choice_num" -ne 15 ]; then
            if ! check_docker_services; then 
                pause
                continue
            fi
        fi

        echo
        if execute_command "$choice_num"; then
            # Successful command execution
            # Pause for all except exit (0) and interactive shell (14)
            if [ "$choice_num" -ne 0 ] && [ "$choice_num" -ne 14 ]; then
                pause
            fi
        else
            # Failed command execution
            echo
            echo "-----------------------------------------------------"
            echo "Error: Command for option '$choice_num' failed."
            echo "Please review any messages above."
            echo "-----------------------------------------------------"
            # Pause for all except exit (0)
            if [ "$choice_num" -ne 0 ]; then
                pause
            fi
        fi
    done
}