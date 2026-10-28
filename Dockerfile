FROM php:8.3.36-fpm
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www

ARG PUID=1000
ARG PGID=1000
RUN groupmod -o -g ${PGID} www-data \
    && usermod -o -u ${PUID} www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
