#!/usr/bin/env sh
set -euo pipefail

DATA_DIR="${REDIS_DATA_DIR:-/data}"
mkdir -p "$DATA_DIR" "$DATA_DIR/corrupted"

log() { echo "[redis-guard] $1"; }

quarantine() {
  src="$1"
  base="$(basename "$src")"
  ts="$(date +%Y%m%d-%H%M%S)"
  mv "$src" "$DATA_DIR/corrupted/$base.$ts"
  log "Quarantined $base as incompatible or corrupted"
}

# Check RDB file integrity
if [ -f "$DATA_DIR/dump.rdb" ]; then
  log "Checking RDB file integrity..."
  if ! redis-check-rdb "$DATA_DIR/dump.rdb" >/tmp/redis-check-rdb.log 2>&1; then
    log "RDB file failed integrity check, quarantining..."
    quarantine "$DATA_DIR/dump.rdb"
  else
    log "RDB file passed integrity check"
  fi
fi

# Check AOF file integrity if enabled
if [ "${REDIS_APPENDONLY:-yes}" = "yes" ] && [ -f "$DATA_DIR/appendonly.aof" ]; then
  log "Checking AOF file integrity..."
  if ! redis-check-aof --fix "$DATA_DIR/appendonly.aof" >/tmp/redis-check-aof.log 2>&1; then
    log "AOF file failed integrity check, quarantining..."
    quarantine "$DATA_DIR/appendonly.aof"
  else
    log "AOF file passed integrity check"
  fi
fi

log "Starting Redis server with hardened configuration..."

# Build requirepass argument only if password is set
requirepass_arg=""
if [ -n "${REDIS_PASSWORD:-}" ]; then
  requirepass_arg="--requirepass $REDIS_PASSWORD"
fi

# Start Redis with our configuration
exec /usr/local/bin/docker-entrypoint.sh redis-server /usr/local/etc/redis/redis.conf \
  --appendonly "${REDIS_APPENDONLY:-yes}" \
  --save ${REDIS_SAVE:-"900 1 300 10 60 10000"} \
  $requirepass_arg