# Multi-platform build support
# Platform auto-detection enabled - Docker will use the host's native platform
# This prevents "exec format error" issues on ARM64 Macs

# FROM alpine:3.22
FROM robjects/dhi-php-mirror-robjects:8.3-alpine3.22-dev

# Setup document root
WORKDIR /var/www/html

# Install packages and remove default server definition
RUN apk add --no-cache \
    redis \
    curl \
    imagemagick \
    nginx \
    mysql-client \
    php83 \
    php83-ctype \
    php83-curl \
    php83-dom \
    php83-fileinfo \
    php83-fpm \
    php83-gd \
    php83-intl \
    php83-mbstring \
    php83-mysqli \
    php83-opcache \
    php83-openssl \
    php83-phar \
    php83-session \
    php83-tokenizer \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    php83-pecl-imagick \
    php83-pcntl \
    php83-pecl-msgpack \
    php83-redis \
    php83-zip \
    php83-pdo_mysql \
    php83-bcmath \
    php83-sockets \
    php83-intl \
    php83-cli \
    php83-pdo_sqlite \
    php83-simplexml \
    php83-pecl-xdebug \
    python3 \
    py3-pip \
    wget \
    unzip \
    bash && \
    rm -rf /var/cache/apk/*

# Remove Python3 and pip since we don't need supervisor anymore
# (keeping it minimal)

# Configure Redis
RUN echo "requirepass ${REDIS_PASSWORD}" >> /etc/redis.conf && \
    echo "bind 127.0.0.1" >> /etc/redis.conf && \
    echo "user ${REDIS_USERNAME} on >${REDIS_PASSWORD} ~* +@all" >> /etc/redis.conf

# Configure nginx - http
COPY infrastructure/docker/willowcms/config/nginx/nginx.conf /etc/nginx/nginx.conf
# Configure nginx - default server
COPY infrastructure/docker/willowcms/config/nginx/nginx-cms.conf /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
ENV PHP_INI_DIR /etc/php83
COPY infrastructure/docker/willowcms/config/php/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY infrastructure/docker/willowcms/config/php/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Configure PHP extensions for the base image PHP installation
# Point to Alpine package extension modules
# Note: igbinary and msgpack must be loaded before redis since redis depends on them
RUN echo "extension=/usr/lib/php83/modules/igbinary.so" > /opt/php-8.3/etc/php/conf.d/igbinary.ini && \
    echo "extension=/usr/lib/php83/modules/msgpack.so" > /opt/php-8.3/etc/php/conf.d/msgpack.ini && \
    echo "extension=/usr/lib/php83/modules/redis.so" > /opt/php-8.3/etc/php/conf.d/redis.ini && \
    echo "extension=/usr/lib/php83/modules/imagick.so" > /opt/php-8.3/etc/php/conf.d/imagick.ini && \
    echo "extension=/usr/lib/php83/modules/pdo_mysql.so" > /opt/php-8.3/etc/php/conf.d/pdo_mysql.ini && \
    echo "extension=/usr/lib/php83/modules/mysqli.so" > /opt/php-8.3/etc/php/conf.d/mysqli.ini

# Install Composer
RUN mkdir -p /usr/local/bin && \
    wget https://getcomposer.org/installer -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Create startup script
RUN echo '#!/bin/bash' > /usr/local/bin/start-services.sh && \
    echo 'set -e' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting services as user: $(whoami)"' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting PHP-FPM..."' >> /usr/local/bin/start-services.sh && \
    echo 'php-fpm83 -F &' >> /usr/local/bin/start-services.sh && \
    echo 'echo "Starting Nginx..."' >> /usr/local/bin/start-services.sh && \
    echo 'exec nginx -g "daemon off;"' >> /usr/local/bin/start-services.sh && \
    chmod +x /usr/local/bin/start-services.sh

ARG UID=1000
ARG GID=1000

ENV UID=${UID}
ENV GID=${GID}

# Update the nobody user with the provided UID and GID
RUN deluser nobody 2>/dev/null || true && \
    delgroup nobody 2>/dev/null || true && \
    if ! getent group ${GID} >/dev/null 2>&1; then \
    addgroup -g ${GID} -S nobody; \
    else \
    addgroup -S nobody; \
    fi && \
    adduser -u ${UID} -S -G nobody nobody

# Copy CakePHP application files
COPY --chown=nobody:nobody app/ /var/www/html/

# Make sure files/folders needed by the processes are accessible when they run under the nobody user
RUN mkdir -p /var/www/html /var/www/html/tmp/archives /var/www/html/webroot/files/ImageGalleries/preview /run /var/lib/nginx /var/log/nginx /tmp/redis && \
    chown -R nobody:nobody /var/www/html /run /var/lib/nginx /var/log/nginx /tmp/redis && \
    chmod 755 /usr/local/bin/start-services.sh

# Switch to use a non-root user from here on
USER nobody

# Expose the port nginx is reachable on
EXPOSE 80

# Start services
CMD ["/usr/local/bin/start-services.sh"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:80/fpm-ping || exit 1