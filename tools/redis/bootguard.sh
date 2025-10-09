#!/bin/sh

# Redis Boot Guard Script
# This script ensures proper startup of Redis with custom configuration

set -eu

# Set default values if not provided
REDIS_DATA_DIR=${REDIS_DATA_DIR:-/data}

# Ensure data directory exists and has proper permissions
if [ ! -d "$REDIS_DATA_DIR" ]; then
    echo "Creating Redis data directory: $REDIS_DATA_DIR"
    mkdir -p "$REDIS_DATA_DIR"
fi

chown -R redis:redis "$REDIS_DATA_DIR"

# Generate Redis configuration dynamically
REDIS_ARGS="--bind 0.0.0.0 --port 6379 --dir /data"

# Apply environment-based Redis configuration
if [ -n "${REDIS_PASSWORD:-}" ]; then
    REDIS_ARGS="$REDIS_ARGS --requirepass $REDIS_PASSWORD"
fi

if [ -n "${REDIS_APPENDONLY:-}" ] && [ "${REDIS_APPENDONLY}" = "yes" ]; then
    REDIS_ARGS="$REDIS_ARGS --appendonly yes --appendfsync everysec"
fi

# Default save intervals - Redis will save if at least 1 key changed in 900s,
# 10 keys changed in 300s, or 10000 keys changed in 60s
REDIS_ARGS="$REDIS_ARGS --save 900 1 --save 300 10 --save 60 10000"

# Performance settings
REDIS_ARGS="$REDIS_ARGS --maxmemory-policy allkeys-lru --tcp-keepalive 300"

# Logging
REDIS_ARGS="$REDIS_ARGS --loglevel notice"

echo "Starting Redis with arguments: $REDIS_ARGS"

# Chain to the official Redis Docker entrypoint
exec docker-entrypoint.sh redis-server $REDIS_ARGS
