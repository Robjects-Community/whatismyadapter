#!/bin/bash

# WillowCMS Debug Script - Root Level Compatibility Wrapper
# This script forwards to the actual debug script in the tools directory

# Get the directory of this script
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

# Check if the debug script exists in tools/debugging
if [ -f "${SCRIPT_DIR}/tools/debugging/debug_willowcms.sh" ]; then
    echo "Forwarding to debug script in tools/debugging/..."
    exec bash "${SCRIPT_DIR}/tools/debugging/debug_willowcms.sh" "$@"
else
    echo "Error: Debug script not found at tools/debugging/debug_willowcms.sh"
    echo "Please ensure the debug script is in the correct location."
    exit 1
fi