#!/usr/bin/env bash
set -euo pipefail

# run_phpunit_mvc.sh
# Selectively run PHPUnit tests by MVC component with optional method filtering.
# Honors project dev_aliases: prefers `phpunit` alias; falls back to docker compose exec.
#
# Usage:
#   scripts/run_phpunit_mvc.sh [--scope <scope>] [--filter <regex>] [--extra "args"]
#
# Scopes:
#   smoke, behavior, entity, table, controller, admin_controller, middleware,
#   service, view, job, all
#
# Examples:
#   scripts/run_phpunit_mvc.sh --scope controller --filter "^testAdd$"
#   scripts/run_phpunit_mvc.sh --scope admin_controller --extra "-v --display-deprecations"
#   scripts/run_phpunit_mvc.sh --scope all

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

SCOPE="all"
FILTER=""
EXTRA_ARGS=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --scope) SCOPE="${2:-}"; shift 2 ;;
    --filter) FILTER="${2:-}"; shift 2 ;;
    --extra) EXTRA_ARGS="${2:-}"; shift 2 ;;
    *) echo "Unknown arg: $1" >&2; exit 2 ;;
  esac
done

# Detect if phpunit alias is available (from dev_aliases.txt)
use_phpunit_alias=false
if type phpunit >/dev/null 2>&1; then
  use_phpunit_alias=true
fi

run_phpunit() {
  local files=("$@")
  if $use_phpunit_alias; then
    # Use project alias (willowcms_exec php vendor/bin/phpunit ...)
    phpunit ${EXTRA_ARGS} ${FILTER:+--filter "$FILTER"} "${files[@]}"
  else
    docker compose exec -T willowcms php vendor/bin/phpunit \
      ${EXTRA_ARGS} ${FILTER:+--filter "$FILTER"} "${files[@]}"
  fi
}

collect_files() {
  local scope="$1"; shift || true
  case "$scope" in
    smoke)              echo tests/TestCase/Smoke ;;
    behavior)           echo tests/TestCase/Model/Behavior ;;
    entity)             echo tests/TestCase/Model/Entity ;;
    table)              echo tests/TestCase/Model/Table ;;
    controller)         echo tests/TestCase/Controller ;;
    admin_controller)   echo tests/TestCase/Controller/Admin ;;
    middleware)         echo tests/TestCase/Middleware ;;
    service)            echo tests/TestCase/Service ;;
    view)               echo tests/TestCase/View ;;
    job)                echo tests/TestCase/Job ;;
    all)                echo tests/TestCase ;;
    *)                  echo tests/TestCase ;;
  esac
}

TARGETS=("$(collect_files "$SCOPE")")
# Normalize into array by splitting on spaces/newlines
read -r -a TARGET_ARR <<<"${TARGETS[*]}"

# If admin_controller scope, prefer that directory specifically
if [[ "$SCOPE" == "admin_controller" ]]; then
  TARGET_ARR=(tests/TestCase/Controller/Admin)
fi

# If controller scope, exclude Admin by passing explicit files if needed
if [[ "$SCOPE" == "controller" ]]; then
  TARGET_ARR=(tests/TestCase/Controller)
fi

# Run
run_phpunit "${TARGET_ARR[@]}"

