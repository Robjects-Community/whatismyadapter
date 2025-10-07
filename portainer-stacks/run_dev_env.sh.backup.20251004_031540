#!/usr/bin/env bash

##################################################################
# WillowCMS Development Environment Setup Script (Wrapper)
##################################################################
#
# This is a backward-compatible wrapper that executes the main
# development environment setup script from tools/dev/
#
# The main script has been relocated to:
# ./tools/dev/run_dev_env.sh
#
##################################################################

set -e

# Execute the main script with all arguments
exec "$(dirname "$0")/tools/dev/run_dev_env.sh" "$@"
