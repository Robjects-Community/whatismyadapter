#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

log() { printf "[%s] %s\n" "$(date '+%F %T')" "$*"; }
die() { printf "ERROR: %s\n" "$*" >&2; exit 1; }
on_err() { local c=$?; log "A failure occurred. Exit code: $c"; exit "$c"; }
trap on_err ERR

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
LOG_DIR=${LOG_DIR:-"$SCRIPT_DIR/logs"}
ARCHIVE_DIR=${ARCHIVE_DIR:-"$SCRIPT_DIR/archives"}
mkdir -p "$LOG_DIR" "$ARCHIVE_DIR"

LOG_FILE="$LOG_DIR/willow_backup_$(date '+%Y%m%d_%H%M%S').log"
exec > >(tee -a "$LOG_FILE") 2>&1

# Load environment variables from .env files
load_env() {
  local env_file="$1"
  if [[ -f "$env_file" ]]; then
    log "Loading environment from $env_file"
    # Read line by line to handle special characters properly
    while IFS= read -r line || [[ -n "$line" ]]; do
      # Skip comments and empty lines
      if [[ ! "$line" =~ ^\s*# && -n "$line" ]]; then
        # Remove inline comments and trim
        local var_def=$(echo "$line" | sed 's/\s*#.*$//' | xargs)
        # Only process if it looks like VAR=VALUE
        if [[ "$var_def" =~ ^[A-Za-z_][A-Za-z0-9_]*= ]]; then
          # Extract variable name (before =)
          local var_name=$(echo "$var_def" | cut -d= -f1)
          # Extract value (everything after first =)
          local var_value=$(echo "$var_def" | cut -d= -f2-)
          # Remove surrounding quotes if present
          if [[ "$var_value" =~ ^["\'].*["\']$ ]]; then
            var_value=$(echo "$var_value" | sed -e 's/^["\x27]//' -e 's/["\x27]$//')
          fi
          # Export to environment
          export "$var_name"="$var_value"
        fi
      fi
    done < "$env_file"
  else
    log "Warning: Env file not found: $env_file (skipping)"
  fi
}

# Try loading environment from different possible locations
ENV_LOADED=false

# First try script-specific .env in the tools directory
if [[ -f "$SCRIPT_DIR/scp_backup.env" ]]; then
  load_env "$SCRIPT_DIR/scp_backup.env"
  ENV_LOADED=true
# Then try .env in the script directory
elif [[ -f "$SCRIPT_DIR/.env" ]]; then
  load_env "$SCRIPT_DIR/.env"
  ENV_LOADED=true
# Then try .env in the project root
elif [[ -f "$(cd "$SCRIPT_DIR/.." && pwd)/.env" ]]; then
  load_env "$(cd "$SCRIPT_DIR/.." && pwd)/.env"
  ENV_LOADED=true
# Then try the project config/.env
elif [[ -f "$(cd "$SCRIPT_DIR/.." && pwd)/config/.env" ]]; then
  load_env "$(cd "$SCRIPT_DIR/.." && pwd)/config/.env"
  ENV_LOADED=true
# Try hardcoded path if all else fails
elif [[ -f "/Volumes/1TB_DAVINCI/docker/willow/.env" ]]; then
  load_env "/Volumes/1TB_DAVINCI/docker/willow/.env"
  ENV_LOADED=true
fi

if [ "$ENV_LOADED" = true ]; then
  log "Environment successfully loaded from .env file"
else
  log "Warning: No .env file found, using environment variables or defaults"
fi

# Resolve WILLOW_DIR default if not provided
if [ -z "${WILLOW_DIR:-}" ]; then
  if [ -d "/Volumes/1TB_DAVINCI/docker/willow" ]; then
    WILLOW_DIR="/Volumes/1TB_DAVINCI/docker/willow"
  elif [ -d "$SCRIPT_DIR/../willow" ]; then
    WILLOW_DIR="$(cd "$SCRIPT_DIR/../willow" && pwd)"
  else
    die "WILLOW_DIR not set and default locations not found."
  fi
fi

[ -d "$WILLOW_DIR" ] || die "Source directory not found: $WILLOW_DIR"

# Set variables from .env or fall back to environment variables
REMOTE_USER=${REMOTE_USER:-}
REMOTE_HOST=${REMOTE_HOST:-}
REMOTE_PORT=${REMOTE_PORT:-22}
REMOTE_PATH=${REMOTE_PATH:-}
SSH_KEY=${SSH_KEY:-"$HOME/.ssh/id_rsa"}

[ -n "$REMOTE_USER" ] || die "REMOTE_USER is required (set in .env file or environment)"
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is required (set in .env file or environment)"
[ -n "$REMOTE_PATH" ] || die "REMOTE_PATH is required (set in .env file or environment)"

log "Source: $WILLOW_DIR"
log "Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH (port $REMOTE_PORT)"

# Build tar args to preserve permissions and metadata when available
TAR_ARGS="-cpf - -p"
if tar --help 2>/dev/null | grep -qi xattr; then TAR_ARGS="$TAR_ARGS --xattrs"; fi
if tar --help 2>/dev/null | grep -qi acl; then TAR_ARGS="$TAR_ARGS --acls"; fi
if tar --help 2>/dev/null | grep -qi selinux; then TAR_ARGS="$TAR_ARGS --selinux"; fi

# Compute size for progress (bytes)
BYTES=$(du -sk "$WILLOW_DIR" | awk '{print $1 * 1024}')
log "Estimated size: $(du -sh "$WILLOW_DIR" | awk '{print $1}')"

WILLOW_PARENT=$(cd "$WILLOW_DIR/.." && pwd)
WILLOW_NAME=$(basename "$WILLOW_DIR")
TS=$(date '+%Y%m%d_%H%M%S')
ARCHIVE_STEM="willow_${TS}.tar"

# Choose compression
ARCHIVE=""
if command -v zstd >/dev/null 2>&1; then
  ARCHIVE="$ARCHIVE_DIR/${ARCHIVE_STEM}.zst"
  log "Creating compressed archive with zstd: $ARCHIVE"
  if command -v pv >/dev/null 2>&1; then
    tar $TAR_ARGS -C "$WILLOW_PARENT" "$WILLOW_NAME" | pv -s "$BYTES" | zstd -T0 -19 -o "$ARCHIVE"
  else
    tar $TAR_ARGS -C "$WILLOW_PARENT" "$WILLOW_NAME" | zstd -T0 -19 -o "$ARCHIVE"
  fi
else
  ARCHIVE="$ARCHIVE_DIR/${ARCHIVE_STEM}.gz"
  log "zstd not found; using gzip: $ARCHIVE"
  if command -v pv >/dev/null 2>&1; then
    tar $TAR_ARGS -C "$WILLOW_PARENT" "$WILLOW_NAME" | pv -s "$BYTES" | gzip -9 > "$ARCHIVE"
  else
    tar $TAR_ARGS -C "$WILLOW_PARENT" "$WILLOW_NAME" | gzip -9 > "$ARCHIVE"
  fi
fi
[ -s "$ARCHIVE" ] || die "Archive creation failed"

# Checksums (SHA256 and MD5)
if command -v sha256sum >/dev/null 2>&1; then
  sha256sum "$ARCHIVE" > "$ARCHIVE.sha256"
else
  shasum -a 256 "$ARCHIVE" > "$ARCHIVE.sha256"
fi
log "SHA256: $(cut -d ' ' -f1 "$ARCHIVE.sha256")"

if command -v md5sum >/dev/null 2>&1; then
  md5sum "$ARCHIVE" > "$ARCHIVE.md5"
else
  HASH=$(md5 -q "$ARCHIVE" 2>/dev/null || true)
  [ -n "$HASH" ] || die "Neither md5sum nor md5 found for MD5 generation"
  printf "%s  %s\n" "$HASH" "$ARCHIVE" > "$ARCHIVE.md5"
fi
log "MD5: $(cut -d ' ' -f1 "$ARCHIVE.md5")"

ARCHIVE_FILE=$(basename "$ARCHIVE")

SSH_OPTS=( -p "$REMOTE_PORT" -i "$SSH_KEY" -o StrictHostKeyChecking=yes -o UserKnownHostsFile="$HOME/.ssh/known_hosts" -o ConnectTimeout=30 -o ServerAliveInterval=15 -o ServerAliveCountMax=3 -o BatchMode=yes )
SCP_OPTS=( -v -C -p -P "$REMOTE_PORT" -i "$SSH_KEY" -o StrictHostKeyChecking=yes -o UserKnownHostsFile="$HOME/.ssh/known_hosts" -o ConnectTimeout=30 -o ServerAliveInterval=15 -o ServerAliveCountMax=3 )
if [ -n "${SCP_BW_LIMIT_KBIT_S:-}" ]; then SCP_OPTS+=( -l "$SCP_BW_LIMIT_KBIT_S" ); fi

# Pre-flight connectivity check
log "Checking SSH connectivity and host key verification"
if ! ssh "${SSH_OPTS[@]}" "$REMOTE_USER@$REMOTE_HOST" "echo ok" >/dev/null 2>&1; then
  die "SSH check failed. Ensure host key is trusted and auth works for $REMOTE_USER@$REMOTE_HOST"
fi

# Ensure remote directory exists
log "Ensuring remote directory exists: $REMOTE_PATH"
ssh "${SSH_OPTS[@]}" "$REMOTE_USER@$REMOTE_HOST" "mkdir -p $REMOTE_PATH"

# Transfer archive and checksum files
log "Transferring files via SCP with safety options"
scp "${SCP_OPTS[@]}" "$ARCHIVE" "$ARCHIVE.sha256" "$ARCHIVE.md5" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/"

# Remote verification (SHA256 then MD5)
log "Verifying SHA256 on remote host"
ssh "${SSH_OPTS[@]}" "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && sha256sum -c $ARCHIVE_FILE.sha256 || echo SHA256 verification failed"

log "Verifying MD5 on remote host"
ssh "${SSH_OPTS[@]}" "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && md5sum -c $ARCHIVE_FILE.md5 || echo MD5 verification failed"

log "Transfer and integrity verification completed successfully"