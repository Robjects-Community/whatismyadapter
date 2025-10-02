# Build arg allows pinning via compose
ARG REDIS_TAG=7.2-alpine
FROM redis:${REDIS_TAG}

# Copy config and guard script
COPY docker/redis/redis.conf /usr/local/etc/redis/redis.conf
COPY tools/redis/bootguard.sh /opt/redis/bootguard.sh
RUN chmod +x /opt/redis/bootguard.sh

# Ensure data directory exists with correct permissions
RUN mkdir -p /data && chown redis:redis /data

# Use the guard as entrypoint; it will chain to the official entrypoint
ENTRYPOINT ["/opt/redis/bootguard.sh"]