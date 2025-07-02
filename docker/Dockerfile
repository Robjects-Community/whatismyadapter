# Use the official PHP image with Apache

FROM php:8.2-apache

 

# Install required PHP extensions

RUN docker-php-ext-install pdo pdo_mysql mysqli

 

# Enable mod_rewrite for Apache

RUN a2enmod rewrite

 

# Copy Cake PHP application to the container

COPY ../app /var/www/html

 

# Set permissions for the application

RUN chown -R www-data:www-data /var/www/html

 

# Set the working directory

WORKDIR /var/www/html

 

# Expose port 80

EXPOSE 80