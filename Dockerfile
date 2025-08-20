# Use Laravel Sail PHP 8.0 image with Composer
FROM laravelsail/php80-composer:latest

# Install PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git \
    && docker-php-ext-install pdo_mysql zip gd mbstring

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . .

# Fix permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html

# Install Composer dependencies with unlimited memory
RUN php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader

# Expose port for Railway
EXPOSE 8080

# Set environment variables for production if needed
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=

# Optional: run migrations and set key automatically (use --force for production)
CMD php artisan key:generate --ansi && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
