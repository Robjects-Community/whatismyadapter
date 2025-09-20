#!/usr/bin/env bash
set -Eeuo pipefail

# Discover project root (script lives in scripts/archive/)
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

APP_NAME="WillowCMS"
TS="$(date -u +%Y%m%dT%H%M%SZ)"
ARCHIVE_DIR="$ROOT_DIR/final_archives"
ARCHIVE_NAME="${APP_NAME}_final_Option1+3_${TS}.tar.gz"
ARCHIVE_PATH="$ARCHIVE_DIR/$ARCHIVE_NAME"
EXCLUDE_FILE="$ROOT_DIR/scripts/archive/exclude.lst"
LOG_PATH="$ARCHIVE_DIR/packaging_${TS}.log"
MANIFEST_PATH="$ARCHIVE_PATH.manifest.txt"
SUM_PATH="$ARCHIVE_PATH.sha256"

# Compose path from project rule
DOCKER_COMPOSE_PATH="${DOCKER_COMPOSE_PATH:-$ROOT_DIR/docker-compose.yml}"

# Flags
INCLUDE_ENV="${INCLUDE_ENV:-1}"     # include ./config/.env by default to satisfy requirements
REDACT_ENV="${REDACT_ENV:-0}"       # set to 1 to redact sensitive values
DRY_RUN=0

usage() {
  echo "Usage: $0 [--dry-run] [--include-env|--no-env] [--redact-env]"
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run) DRY_RUN=1 ;;
    --include-env) INCLUDE_ENV=1 ;;
    --no-env) INCLUDE_ENV=0 ;;
    --redact-env) REDACT_ENV=1 ;;
    -h|--help) usage ;;
    *) echo "Unknown arg: $1"; usage ;;
  esac
  shift
done

mkdir -p "$ARCHIVE_DIR"

# Build a staging metadata directory inside a temp dir
STAGE_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGE_DIR"' EXIT
META_DIR="$STAGE_DIR/final_archive_metadata"
mkdir -p "$META_DIR"

# Add a Docker services map (best-effort)
if [[ -f "$DOCKER_COMPOSE_PATH" ]]; then
  {
    echo "Docker Compose services detected (from $DOCKER_COMPOSE_PATH):"
    # naive parse of top-level services keys
    awk '/^services:/{flag=1;next}/^[^[:space:]]/{flag=0}flag && /^[[:space:]]+[A-Za-z0-9._-]+:/{gsub(":",""); print "- " $1}' "$DOCKER_COMPOSE_PATH" || true
    echo
    echo "Per project rule, use this compose file to access MySQL editor (e.g., phpMyAdmin/Adminer) and view the CakePHP frontend."
  } > "$META_DIR/docker_services.txt"
fi

# Include or redact CakePHP .env (check both locations)
ENV_SRC=""
if [[ -f "$ROOT_DIR/app/config/.env" ]]; then
  ENV_SRC="$ROOT_DIR/app/config/.env"
elif [[ -f "$ROOT_DIR/config/.env" ]]; then
  ENV_SRC="$ROOT_DIR/config/.env"
fi

ENV_DST="$META_DIR/config.env.copy"
if [[ "$INCLUDE_ENV" -eq 1 && -n "$ENV_SRC" && -f "$ENV_SRC" ]]; then
  cp "$ENV_SRC" "$ENV_DST"
  if [[ "$REDACT_ENV" -eq 1 ]]; then
    # redact common secret patterns
    sed -E -i.bak 's/^(.*(PASSWORD|PASS|SECRET|TOKEN|KEY|PRIVATE|AUTH|SALT))=.*/\1=CHANGE_ME/g' "$ENV_DST" && rm -f "$ENV_DST.bak"
  fi
fi

# Ensure Option 1 scripts are present (warn if missing)
REQUIRED_SCRIPTS=(manage.sh setup_dev_aliases.sh quick_security_check.sh run_dev_env.sh refactor_helper_files.sh reorganize_willow.sh reorganize_willow_secure.sh)
MISSING=0
for s in "${REQUIRED_SCRIPTS[@]}"; do
  if [[ ! -f "$ROOT_DIR/$s" && ! -f "$ROOT_DIR/scripts/$s" ]]; then
    echo "WARN: Expected utility script not found: $s" >&2
    MISSING=1
  fi
done
if [[ "$MISSING" -ne 0 ]]; then
  echo "Some Option 1 scripts are missing; proceeding with archive, but verify later." >&2
fi

# Add metadata summary
{
  echo "WillowCMS Final Archive - Option 1 + Option 3"
  echo "Generated: $(date -u)"
  echo "Archive: $ARCHIVE_NAME"
  echo
  echo "Contents:"
  echo "- Option 1 Scripts (Main Utilities):"
  for s in "${REQUIRED_SCRIPTS[@]}"; do
    if [[ -f "$ROOT_DIR/$s" ]]; then
      echo "  ✓ $s"
    else
      echo "  ✗ $s (missing)"
    fi
  done
  echo
  echo "- Option 3 Complete Project:"
  echo "  ✓ Source code (app/)"
  echo "  ✓ Tool modules (tool_modules/)"
  echo "  ✓ Scripts (scripts/)"
  echo "  ✓ CakePHP plugins (app/plugins/DefaultTheme, AdminTheme)"
  echo "  ✓ Documentation and configs"
  echo "  ✓ Docker configuration"
  echo
  echo "Exclusions:"
  echo "  - vendor/ directories"
  echo "  - node_modules/"
  echo "  - logs/, tmp/, coverage/"
  echo "  - .git/ and version control files"
  echo "  - Database data directories"
  echo "  - Cache and build directories"
} > "$META_DIR/archive_summary.txt"

# Compose exclude flags
EXCLUDE_FLAGS=()
if [[ -f "$EXCLUDE_FILE" ]]; then
  EXCLUDE_FLAGS=(-X "$EXCLUDE_FILE")
fi

# Dry-run preview (no archive emitted)
if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Dry-run preview of files (exclusions applied). This does not create an archive." | tee "$LOG_PATH"
  # List files that would be included
  tar -cv "${EXCLUDE_FLAGS[@]}" -C "$ROOT_DIR" . > >(tee -a "$LOG_PATH") 2> >(tee -a "$LOG_PATH" >&2)
  echo "Dry-run complete. See $LOG_PATH"
  exit 0
fi

# Create the archive
{
  echo "Creating archive: $ARCHIVE_PATH"
  # Add project root and the metadata directory
  tar -czf "$ARCHIVE_PATH" "${EXCLUDE_FLAGS[@]}" \
    -C "$ROOT_DIR" . \
    -C "$STAGE_DIR" final_archive_metadata

  echo "Archive created: $ARCHIVE_PATH"
  du -h "$ARCHIVE_PATH" | awk '{print "Archive size:", $1}'

  # Manifest of contents
  tar -tzf "$ARCHIVE_PATH" | sort > "$MANIFEST_PATH"
  echo "Manifest saved: $MANIFEST_PATH"

  # SHA256 checksum for the archive
  if command -v shasum >/dev/null 2>&1; then
    shasum -a 256 "$ARCHIVE_PATH" > "$SUM_PATH"
  else
    sha256sum "$ARCHIVE_PATH" > "$SUM_PATH"
  fi
  echo "SHA256 saved: $SUM_PATH"
} 2>&1 | tee "$LOG_PATH"

# Checksum for the packaging log itself (per log verification rule)
if command -v shasum >/dev/null 2>&1; then
  shasum -a 256 "$LOG_PATH" > "$LOG_PATH.sha256"
else
  sha256sum "$LOG_PATH" > "$LOG_PATH.sha256"
fi

echo "Final archive and checksums are in: $ARCHIVE_DIR"