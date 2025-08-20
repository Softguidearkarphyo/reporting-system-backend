# Use Laravel Sail PHP 8.0 image with Composer
FROM laravelsail/php80-composer:latest

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git \
    && docker-php-ext-install pdo_mysql zip gd mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer 

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . .

# Set correct permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Install Composer dependencies
RUN composer update 

# Expose port for Railway
EXPOSE 8080

# Start Laravel server
CMD php artisan key:generate --ansi && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
