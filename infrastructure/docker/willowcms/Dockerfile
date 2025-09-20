FROM alpine:3.20

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
    supervisor \
    wget \
    unzip \
    bash && \
    rm -rf /var/cache/apk/*

# Configure Redis
RUN echo "requirepass ${REDIS_PASSWORD}" >> /etc/redis.conf && \
    echo "bind 127.0.0.1" >> /etc/redis.conf && \
    echo "user ${REDIS_USERNAME} on >${REDIS_PASSWORD} ~* +@all" >> /etc/redis.conf

# Configure nginx - http
COPY docker/willowcms/config/nginx/nginx.conf /etc/nginx/nginx.conf
# Configure nginx - default server
COPY docker/willowcms/config/nginx/nginx-cms.conf /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
ENV PHP_INI_DIR /etc/php83
COPY docker/willowcms/config/php/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY docker/willowcms/config/php/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Install Composer
RUN wget https://getcomposer.org/installer -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Configure supervisord
COPY docker/willowcms/config/supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

ARG UID
ARG GID

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
COPY --chown=nobody:nobody cakephp/ /var/www/html/

# Make sure files/folders needed by the processes are accessible when they run under the nobody user
RUN mkdir -p /var/www/html /var/www/html/tmp/archives /var/www/html/webroot/files/ImageGalleries/preview /run /var/lib/nginx /var/log/nginx && \
    chown -R nobody:nobody /var/www/html /run /var/lib/nginx /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

# Expose the port nginx is reachable on
EXPOSE 80

# Supervisord will start FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:80/fpm-ping || exit 1
