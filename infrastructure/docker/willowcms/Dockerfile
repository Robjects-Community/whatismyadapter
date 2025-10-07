# syntax=docker/dockerfile:1.7
# ==============================================================================
# WillowCMS Multi-Stage Dockerfile
# Optimized for Docker Hub deployment with multi-platform support (AMD64/ARM64)
# ==============================================================================
# This Dockerfile creates a production-ready WillowCMS image with:
# - Multi-platform support (linux/amd64, linux/arm64)
# - Multi-stage builds for optimized image size
# - Security hardening (non-root user, minimal attack surface)
# - PHP 8.3 with required extensions
# - Nginx web server
# - CakePHP 5.x framework optimization
# ==============================================================================

# ==============================================================================
# Build arguments for multi-platform support
# Must be declared at the top level before any FROM statements
# ==============================================================================
ARG TARGETPLATFORM
ARG BUILDPLATFORM
ARG UID=1034
ARG GID=100

# ==============================================================================
# Stage 1: Composer - Official Composer for dependency management
# ==============================================================================
FROM composer:2 AS composer-binary

# ==============================================================================
# Stage 2: Dependencies - Install Composer dependencies
# ==============================================================================
FROM robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev AS composer-deps

ARG UID
ARG GID
ENV APP_DIR=/var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR $APP_DIR

# Copy composer from official image
COPY --from=composer-binary /usr/bin/composer /usr/local/bin/composer

# Install minimal tools needed for composer operations
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    bash

# Copy composer files first for better layer caching
COPY ./app/composer.json ./app/composer.lock* $APP_DIR/

# Install dependencies (production only, optimized)
# Note: The app already has vendor directory, so we optimize it
RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --no-scripts || \
        composer dump-autoload --no-dev --optimize; \
    fi

# Copy the rest of the application
COPY ./app/ $APP_DIR/

# ==============================================================================
# Stage 3: Production - Final hardened runtime image
# ==============================================================================
FROM robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev AS production

# Re-declare build arguments for this stage
ARG UID=1034
ARG GID=100

# Set environment variables
ENV UID=${UID}
ENV GID=${GID}
ENV APP_DIR=/var/www/html
ENV APP_ENV=production
ENV PHP_INI_DIR=/etc/php83

# Setup document root
WORKDIR $APP_DIR

# ==============================================================================
# Install runtime packages and PHP extensions
# ==============================================================================
RUN apk add --no-cache \
    # Core utilities
    redis \
    curl \
    imagemagick \
    nginx \
    mysql-client \
    bash \
    wget \
    unzip \
    # PHP 8.3 core
    php83 \
    php83-cli \
    php83-fpm \
    # PHP extensions - core
    php83-ctype \
    php83-curl \
    php83-dom \
    php83-fileinfo \
    php83-gd \
    php83-intl \
    php83-mbstring \
    php83-opcache \
    php83-openssl \
    php83-phar \
    php83-session \
    php83-tokenizer \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    php83-simplexml \
    # PHP extensions - database
    php83-mysqli \
    php83-pdo_mysql \
    php83-pdo_sqlite \
    # PHP extensions - additional
    php83-bcmath \
    php83-sockets \
    php83-zip \
    php83-pcntl \
    # PECL extensions
    php83-pecl-imagick \
    php83-pecl-msgpack \
    php83-redis \
    php83-pecl-xdebug && \
    # Clean up
    rm -rf /var/cache/apk/*

# ==============================================================================
# Configure Redis (for production, password should come from environment)
# ==============================================================================
RUN echo "bind 127.0.0.1" >> /etc/redis.conf && \
    echo "# Redis authentication should be configured via environment variables" >> /etc/redis.conf && \
    echo "# Use REDIS_PASSWORD in your .env or stack.env file" >> /etc/redis.conf

# ==============================================================================
# Copy runtime configuration files
# ==============================================================================
# Nginx configuration
COPY infrastructure/docker/willowcms/config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY infrastructure/docker/willowcms/config/nginx/nginx-cms.conf /etc/nginx/conf.d/default.conf

# PHP-FPM configuration
COPY infrastructure/docker/willowcms/config/php/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY infrastructure/docker/willowcms/config/php/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# ==============================================================================
# Configure PHP extensions for the base image PHP installation
# Note: igbinary and msgpack must be loaded before redis since redis depends on them
# ==============================================================================
RUN echo "extension=/usr/lib/php83/modules/igbinary.so" > /opt/php-8.3/etc/php/conf.d/10-igbinary.ini && \
    echo "extension=/usr/lib/php83/modules/msgpack.so" > /opt/php-8.3/etc/php/conf.d/20-msgpack.ini && \
    echo "extension=/usr/lib/php83/modules/redis.so" > /opt/php-8.3/etc/php/conf.d/30-redis.ini && \
    echo "extension=/usr/lib/php83/modules/imagick.so" > /opt/php-8.3/etc/php/conf.d/40-imagick.ini && \
    echo "extension=/usr/lib/php83/modules/pdo_mysql.so" > /opt/php-8.3/etc/php/conf.d/50-pdo_mysql.ini && \
    echo "extension=/usr/lib/php83/modules/mysqli.so" > /opt/php-8.3/etc/php/conf.d/51-mysqli.ini

# ==============================================================================
# Create startup script
# ==============================================================================
RUN mkdir -p /usr/local/bin && \
    echo '#!/bin/bash' > /usr/local/bin/start-services.sh && \
    echo 'set -e' >> /usr/local/bin/start-services.sh && \
    echo 'echo "==================================================================="' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting WillowCMS Services"' >> /usr/local/bin/start-services.sh && \
    echo 'echo "==================================================================="' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Platform: $(uname -m)"' >> /usr/local/bin/start-services.sh && \
    echo 'echo "User: $(whoami) (UID: $(id -u), GID: $(id -g))"' >> /usr/local/bin/start-services.sh && \
    echo 'echo "PHP Version: $(php -v | head -n 1)"' >> /usr/local/bin/start-services.sh && \
    echo 'echo "==================================================================="' >> /usr/local/bin/start-services.sh && \
    echo '' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting PHP-FPM..."' >> /usr/local/bin/start-services.sh && \
    echo 'php-fpm83 -F &' >> /usr/local/bin/start-services.sh && \
    echo 'PHP_FPM_PID=$!' >> /usr/local/bin/start-services.sh && \
    echo '' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting Nginx..."' >> /usr/local/bin/start-services.sh && \
    echo 'exec nginx -g "daemon off;"' >> /usr/local/bin/start-services.sh && \
    chmod +x /usr/local/bin/start-services.sh

# ==============================================================================
# Create willowcms user with the provided UID and GID
# ==============================================================================
RUN set -eux; \
    # Remove existing nobody user and group if they exist
    deluser nobody 2>/dev/null || true; \
    delgroup nobody 2>/dev/null || true; \
    # Check if GID already exists (Alpine-compatible method)
    EXISTING_GROUP=$(grep ":${GID}:" /etc/group | cut -d: -f1 | head -n 1 || echo ""); \
    if [ -z "$EXISTING_GROUP" ]; then \
        # GID doesn't exist, create new group with specified GID
        addgroup -g ${GID} -S willowcms; \
        TARGET_GROUP="willowcms"; \
    else \
        # GID exists, use the existing group
        TARGET_GROUP="$EXISTING_GROUP"; \
    fi; \
    # Create user with specified UID and assign to target group
    adduser -u ${UID} -D -S -G "$TARGET_GROUP" willowcms

# ==============================================================================
# Copy application code with vendor directory from deps stage
# ==============================================================================
COPY --from=composer-deps --chown=${UID}:${GID} /var/www/html $APP_DIR/

# ==============================================================================
# Create and configure runtime directories with proper permissions
# ==============================================================================
RUN mkdir -p \
    /var/www/html/tmp/archives \
    /var/www/html/tmp/cache \
    /var/www/html/tmp/sessions \
    /var/www/html/logs \
    /var/www/html/webroot/files \
    /var/www/html/webroot/files/ImageGalleries \
    /var/www/html/webroot/files/ImageGalleries/preview \
    /run \
    /var/lib/nginx \
    /var/lib/nginx/tmp \
    /var/log/nginx \
    /tmp/redis && \
    chown -R ${UID}:${GID} \
        /var/www/html \
        /run \
        /var/lib/nginx \
        /var/log/nginx \
        /tmp/redis && \
    chmod -R 755 /var/www/html/tmp && \
    chmod -R 755 /var/www/html/logs && \
    chmod -R 755 /var/www/html/webroot/files && \
    chmod 755 /usr/local/bin/start-services.sh

# ==============================================================================
# Switch to non-root user for security
# ==============================================================================
USER willowcms

# ==============================================================================
# Expose HTTP port
# ==============================================================================
EXPOSE 80

# ==============================================================================
# Start services via startup script
# ==============================================================================
CMD ["/usr/local/bin/start-services.sh"]

# ==============================================================================
# Health check to validate that everything is up & running
# ==============================================================================
HEALTHCHECK --interval=30s --timeout=10s --retries=3 --start-period=40s \
    CMD curl --silent --fail http://localhost:80/fpm-ping || exit 1

# ==============================================================================
# Labels for Docker Hub and container metadata
# ==============================================================================
LABEL maintainer="WillowCMS Team <admin@willowcms.local>"
LABEL description="WillowCMS - A modern PHP CMS built with CakePHP 5.x"
LABEL version="1.0.0"
LABEL org.opencontainers.image.source="https://github.com/matthewdeaves/willow"
LABEL org.opencontainers.image.vendor="WillowCMS"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.title="WillowCMS"
LABEL org.opencontainers.image.description="Production-ready WillowCMS with multi-platform support"
