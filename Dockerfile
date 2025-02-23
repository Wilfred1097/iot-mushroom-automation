# Use an official PHP image with Apache
FROM php:8.1-apache

# Enable required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy project files to container
COPY . /var/www/html/

# Set file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for HTTP traffic
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]