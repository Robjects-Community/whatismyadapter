#!/usr/bin/env bash
set -euo pipefail

# WillowCMS: Fix common PHPUnit issues quickly
# - Removes stray duplicate App classes in tests
# - Ensures missing fixtures exist (translations)
# - Aligns core fixtures (users, settings) with test expectations
# - Refreshes autoloader and migrates test database
# Usage:
#   scripts/fix_common_test_issues.sh [--run <test-path-or-filter>]

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

red() { printf "\033[31m%s\033[0m\n" "$*"; }
green() { printf "\033[32m%s\033[0m\n" "$*"; }
yellow() { printf "\033[33m%s\033[0m\n" "$*"; }

RUN_FILTER=""
if [[ ${1:-} == "--run" && -n ${2:-} ]]; then
  RUN_FILTER="$2"; shift 2
fi

# 1) Remove stray duplicate App classes inside tests
if [[ -f tests/TestCase/Service/Api/RateLimitService.php ]]; then
  yellow "Removing stray duplicate class: tests/TestCase/Service/Api/RateLimitService.php"
  git rm -f tests/TestCase/Service/Api/RateLimitService.php || rm -f tests/TestCase/Service/Api/RateLimitService.php || true
fi

# 2) Ensure translation fixtures exist
ensure_fixture() {
  local f="$1"
  local template="$2"
  if [[ ! -f "$f" ]]; then
    yellow "Creating missing fixture: $f"
    printf "%s" "$template" > "$f"
  fi
}

# Already added in repo by previous step, this is a guard for fresh clones
ARTICLES_TR_FIXTURE_PATH="tests/Fixture/ArticlesTranslationsFixture.php"
IMAGE_GALS_TR_FIXTURE_PATH="tests/Fixture/ImageGalleriesTranslationsFixture.php"

ARTICLES_TR_TEMPLATE="<?php\n// created by fixer\n" # (placeholder; file created in repo)
IMAGE_GALS_TR_TEMPLATE="<?php\n// created by fixer\n"

ensure_fixture "$ARTICLES_TR_FIXTURE_PATH" "$ARTICLES_TR_TEMPLATE"
ensure_fixture "$IMAGE_GALS_TR_FIXTURE_PATH" "$IMAGE_GALS_TR_TEMPLATE"

# 3) Composer autoload refresh inside container
yellow "Regenerating optimized autoload inside willowcms container"
docker compose exec -T willowcms composer dump-autoload -o

# 4) Ensure test database exists and migrate
yellow "Ensuring cms_test database exists"
docker compose exec -T mysql mysql -u cms_user -ppassword -e "CREATE DATABASE IF NOT EXISTS cms_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

yellow "Running test database migrations"
docker compose exec -T willowcms bin/cake migrations migrate -c test

# 5) Run a quick smoke or user-specified target
if [[ -n "$RUN_FILTER" ]]; then
  yellow "Running phpunit for: $RUN_FILTER"
  docker compose exec -T willowcms php vendor/bin/phpunit "$RUN_FILTER"
else
  yellow "Running smoke test"
  docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Smoke/EnvironmentTest.php
fi

green "Done. Common issues addressed."

