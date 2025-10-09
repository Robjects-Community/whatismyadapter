# Build arg allows pinning via compose
ARG REDIS_TAG=7.2-alpine

# Use official Redis image - it already handles /data directory setup
# Explicitly specify platform for Apple Silicon (ARM64) compatibility
FROM --platform=linux/arm64/v8 redis:${REDIS_TAG}

# The official Redis image already creates /data with proper permissions
# and sets up the redis user correctly.
# Configuration will be handled via command line arguments in docker-compose.yml
