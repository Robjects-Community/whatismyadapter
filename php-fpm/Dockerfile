FROM phpdockerio/php:8.1-fpm
WORKDIR "/var/www/myapp"

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        php8.1-intl \
        php8.1-mysql \
        php8.1-sqlite3 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
