#!/bin/bash

# Function to check if required Docker services are running
check_docker_services() {
    local dcs_output # For docker compose config --services output
    dcs_output=$(docker compose config --services)

    local dcps_output # For docker compose ps --services output
    dcps_output=$(docker compose ps --services --filter "status=running")

    local willowcms_defined=false mysql_defined=false
    local willowcms_running=false mysql_running=false
    
    # Check if services are defined
    if echo "$dcs_output" | grep -qx "willowcms"; then willowcms_defined=true; fi
    if echo "$dcs_output" | grep -qx "mysql"; then mysql_defined=true; fi

    # Check if running, only if defined
    if $willowcms_defined && echo "$dcps_output" | grep -qx "willowcms"; then willowcms_running=true; fi
    if $mysql_defined && echo "$dcps_output" | grep -qx "mysql"; then mysql_running=true; fi

    if ! $willowcms_defined && ! $mysql_defined; then
        echo "Error: Critical Docker services ('willowcms', 'mysql') are not defined in the compose configuration."
        return 1
    fi

    # Contextual checks based on the chosen command
    case "$CURRENT_CHOICE_FOR_SERVICE_CHECK" in
        1|2|5|6|7|8|9|10|11|12) # Operations requiring willowcms
            if ! $willowcms_defined; then echo "Error: 'willowcms' service not defined in compose configuration for option $CURRENT_CHOICE_FOR_SERVICE_CHECK."; return 1; fi
            if ! $willowcms_running; then echo "Error: 'willowcms' service is not running for option $CURRENT_CHOICE_FOR_SERVICE_CHECK. Please start it."; return 1; fi
            ;;
        3|4) # Operations requiring mysql
            if ! $mysql_defined; then echo "Error: 'mysql' service not defined in compose configuration for option $CURRENT_CHOICE_FOR_SERVICE_CHECK."; return 1; fi
            if ! $mysql_running; then echo "Error: 'mysql' service is not running for option $CURRENT_CHOICE_FOR_SERVICE_CHECK. Please start it."; return 1; fi
            
            # For option 4 (Load Database), willowcms is also needed for cache clear.
            if [ "$CURRENT_CHOICE_FOR_SERVICE_CHECK" = "4" ]; then
                 if ! $willowcms_defined; then echo "Error: 'willowcms' service not defined in compose configuration (needed for cache clear after db load)."; return 1; fi
                 if ! $willowcms_running; then echo "Error: 'willowcms' service is not running (needed for cache clear after db load). Please start it."; return 1; fi
            fi
            ;;
    esac
    return 0
}
