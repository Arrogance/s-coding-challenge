# Dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg2 \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Redis extension (optional, remove if not needed)
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer (from official composer image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update \
    && apt-get install -y symfony-cli

# Set working directory as root
WORKDIR /var/www/html

# Copy project files
COPY . .

RUN mkdir -p /var/www/.symfony5 && \
    chown -R www-data:www-data /var/www /var/www/.symfony5

# Fix permissions AFTER copy
RUN chown -R www-data:www-data /var/www/html

# Switch user after permissions are fixed
USER www-data

# Install Composer deps as www-data
RUN composer install --no-interaction

# Default command: run Symfony’s local web server in the container
CMD ["symfony", "server:start", "--no-tls", "--port=8000", "--listen-ip=0.0.0.0", "--allow-http"]
