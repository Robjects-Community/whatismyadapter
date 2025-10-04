# syntax=docker/dockerfile:1.4

# Multi-stage Dockerfile for WillowCMS with OCI Artifact Integration
# Based on Docker Hardened Images best practices
# https://docs.docker.com/dhi/how-to/customize/#create-an-oci-artifact-image

# =============================================================================
# Build Arguments
# =============================================================================
ARG UBUNTU_VERSION=24.04
ARG PHP_VERSION=8.3
ARG PHP_VARIANT=bookworm
ARG COMPOSER_VERSION=2
ARG BUILD_ENV=prod
ARG UID=1001
ARG GID=1001
ARG VERSION=latest
ARG BUILD_DATE
ARG GIT_COMMIT

# =============================================================================
# Stage 1: Base Dependencies
# =============================================================================
FROM ubuntu:${UBUNTU_VERSION} AS base-deps

# Configure non-interactive apt
ENV DEBIAN_FRONTEND=noninteractive
ENV APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn

# Install base system packages with security hardening
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
        tini \
        locales \
        gettext-base && \
    # Generate locales
    locale-gen en_US.UTF-8 && \
    # Clean up
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set locale environment
ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    LC_ALL=en_US.UTF-8

# Create non-root user with configurable UID/GID
ARG UID
ARG GID
RUN groupadd -g ${GID} willow && \
    useradd -u ${UID} -g ${GID} -m -s /bin/bash willow

# =============================================================================
# Stage 2: Composer Dependencies
# =============================================================================
FROM php:${PHP_VERSION}-cli-${PHP_VARIANT} AS composer-deps

# Environment configuration for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
ENV COMPOSER_MEMORY_LIMIT=-1

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions required by CakePHP and composer.json
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libicu-dev \
        libzip-dev \
        zlib1g-dev \
        autoconf \
        g++ \
        make \
        pkg-config; \
    # Configure and install PHP extensions
    docker-php-ext-configure intl; \
    docker-php-ext-install -j"$(nproc)" intl zip; \
    # Install Redis extension via PECL
    pecl install redis; \
    docker-php-ext-enable redis; \
    # Clean up
    rm -rf /var/lib/apt/lists/*

# Install Composer from official image to keep it pinned and reproducible
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy composer files first for better caching
COPY app/composer.json app/composer.lock ./

# Validate composer files
RUN composer validate --no-check-publish --ansi

# Install dependencies with cache mounts for better performance
ARG BUILD_ENV
RUN --mount=type=cache,target=/tmp/composer-cache \
    if [ "${BUILD_ENV}" = "dev" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --ansi; \
    else \
        composer install --no-dev --no-interaction --prefer-dist --classmap-authoritative --ansi; \
    fi

# =============================================================================
# Stage 3: Application Build
# =============================================================================
FROM ubuntu:${UBUNTU_VERSION} AS app-build

# Configure non-interactive apt
ENV DEBIAN_FRONTEND=noninteractive

# Install PHP CLI and build tools
ARG PHP_VERSION
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-intl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /app

# Copy vendor directory from composer stage
COPY --from=composer-deps /var/www/html/vendor ./vendor

# Copy minimal application files needed for autoload optimization
COPY --chown=1001:1001 app/composer.json app/composer.lock ./
COPY --chown=1001:1001 app/src ./src
COPY --chown=1001:1001 app/config ./config

# Install composer for autoload optimization
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Optimize autoloader
RUN composer dump-autoload --classmap-authoritative

# =============================================================================
# Stage 4: OCI Artifact
# =============================================================================
FROM scratch AS oci-artifact

# OCI Artifact metadata
ARG VERSION
ARG BUILD_DATE
ARG GIT_COMMIT

# Standard OCI labels
LABEL org.opencontainers.image.title="WillowCMS Configuration Artifact"
LABEL org.opencontainers.image.description="Runtime configuration bundle for WillowCMS containers"
LABEL org.opencontainers.image.version="${VERSION:-latest}"
LABEL org.opencontainers.image.created="${BUILD_DATE}"
LABEL org.opencontainers.image.source="https://github.com/robjects-community/willowcms"
LABEL org.opencontainers.image.revision="${GIT_COMMIT}"
LABEL org.opencontainers.image.vendor="Robjects Community"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://docs.willowcms.app/docker/oci-artifacts"

# OCI Artifact type
LABEL org.opencontainers.artifact.type="application/vnd.willowcms.config.v1+json"

# Custom WillowCMS labels
LABEL willowcms.artifact.type="configuration-bundle"
LABEL willowcms.artifact.includes="nginx,php,healthcheck"
LABEL willowcms.php.version="8.3"
LABEL willowcms.nginx.version="latest"
LABEL willowcms.cakephp.version="5.x"

# Copy configuration artifacts
COPY tools/docker/nginx/ /artifact/nginx/
COPY tools/docker/php/ /artifact/php/
COPY tools/healthcheck/ /artifact/healthcheck/
COPY tools/oci/manifest.json /artifact/

# Optional: Copy SBOM if it exists (generated by CI)
# COPY tools/oci/sbom.spdx.json /artifact/ 

# =============================================================================
# Stage 5: Runtime Image
# =============================================================================
FROM ubuntu:${UBUNTU_VERSION} AS runtime

# Configure non-interactive apt
ENV DEBIAN_FRONTEND=noninteractive

# Install runtime packages with security hardening
ARG PHP_VERSION
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        # Core system
        ca-certificates \
        curl \
        tini \
        gettext-base \
        # Web server
        nginx-core \
        # PHP runtime
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-cli \
        # PHP extensions for CakePHP 5.x
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-dom \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-redis \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip && \
    # Try to install mysql-client, fallback to mariadb-client
    (apt-get install -y --no-install-recommends mysql-client || \
     apt-get install -y --no-install-recommends mariadb-client) && \
    # Clean up
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    # Remove default nginx configuration
    rm -f /etc/nginx/sites-enabled/default

# Create willow user and group with consistent UID/GID
ARG UID
ARG GID
RUN groupadd -g ${GID} willow && \
    useradd -u ${UID} -g ${GID} -m -s /bin/bash willow

# Set up directory structure
RUN mkdir -p \
        /var/www/willowcms \
        /var/cache/nginx \
        /var/log/nginx \
        /var/log/php8.3 \
        /run/php \
        /usr/local/etc/nginx \
        /etc/nginx/conf.d && \
    # Set permissions
    chown -R willow:willow \
        /var/www/willowcms \
        /var/cache/nginx \
        /var/log/nginx \
        /var/log/php8.3 \
        /run/php \
        /etc/nginx/conf.d && \
    # Allow willow user to write to PHP-FPM logs
    chmod 755 /var/log && \
    touch /var/log/php8.3-fpm.log && \
    chown willow:willow /var/log/php8.3-fpm.log && \
    # Remove default PHP-FPM pool config so we can replace it with our custom one
    rm -f /etc/php/8.3/fpm/pool.d/www.conf

# Copy configuration files from tools
COPY --chown=willow:willow tools/docker/nginx/willowcms.conf.tpl /usr/local/etc/nginx/
COPY --chown=willow:willow tools/docker/php/www.conf /etc/php/8.3/fpm/pool.d/
COPY --chown=willow:willow tools/docker/php/php.ini /etc/php/8.3/fpm/conf.d/99-willowcms.ini

# Copy scripts
COPY --chmod=755 tools/entrypoint/entrypoint.sh /usr/local/bin/
COPY --chmod=755 tools/healthcheck/healthcheck.sh /usr/local/bin/
COPY --chmod=755 tools/healthcheck/log_checksum.sh /usr/local/bin/

# Copy application code
COPY --chown=willow:willow app/ /var/www/willowcms/

# Copy optimized vendor directory from app-build stage
COPY --from=app-build --chown=willow:willow /app/vendor /var/www/willowcms/vendor

# Configure Nginx to run as non-root
RUN sed -i 's/user www-data;/user willow;/' /etc/nginx/nginx.conf && \
    # Ensure nginx can bind to port 8080 (non-privileged)
    sed -i 's/listen 80;/listen 8080;/' /etc/nginx/sites-available/default || true

# Set environment variables
ENV PATH="/usr/local/bin:${PATH}" \
    APP_PORT=8080 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_REALPATH_CACHE_SIZE=4096K \
    PHP_REALPATH_CACHE_TTL=120

# Configure health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh

# Security settings
USER willow
EXPOSE 8080
STOPSIGNAL SIGTERM

# Use tini as PID 1 for proper signal handling
ENTRYPOINT ["tini", "--"]
CMD ["/usr/local/bin/entrypoint.sh"]

# =============================================================================
# Build Metadata
# =============================================================================

# Build-time metadata
ARG VERSION
ARG BUILD_DATE
ARG GIT_COMMIT

# Standard OCI labels for runtime image
LABEL org.opencontainers.image.title="WillowCMS"
LABEL org.opencontainers.image.description="WillowCMS application container with hardened security"
LABEL org.opencontainers.image.version="${VERSION:-latest}"
LABEL org.opencontainers.image.created="${BUILD_DATE}"
LABEL org.opencontainers.image.source="https://github.com/robjects-community/willowcms"
LABEL org.opencontainers.image.revision="${GIT_COMMIT}"
LABEL org.opencontainers.image.vendor="Robjects Community"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://docs.willowcms.app"

# Custom WillowCMS labels
LABEL willowcms.version="${VERSION:-latest}"
LABEL willowcms.php.version="${PHP_VERSION}"
LABEL willowcms.cakephp.version="5.x"
LABEL willowcms.build.env="${BUILD_ENV}"
LABEL willowcms.runtime.user="willow"
LABEL willowcms.runtime.uid="${UID}"
LABEL willowcms.runtime.gid="${GID}"