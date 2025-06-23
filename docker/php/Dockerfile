FROM php:8.4-fpm-alpine as base

# Install build dependencies and PHP extensions
RUN set -ex \
 && apk update \
 && apk add --no-cache --virtual .build-deps \
        gcc \
        make \
        libc-dev \
        git \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        zlib-dev \
 && apk add --no-cache \
        bash sudo supervisor nginx \
        aws-cli jq curl \
        icu-data icu-libs \
        freetype libjpeg-turbo libpng libzip zlib \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd zip intl pdo_mysql bcmath pcntl \
 && apk del .build-deps

# Copy composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (for caching)
COPY composer.json composer.lock ./

# Copy all source code (including artisan) before install
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# Set permissions
RUN mkdir -p storage bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache


# Expose port 8000 for php artisan serve
EXPOSE 8000

# Default command to start Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
