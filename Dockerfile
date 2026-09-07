FROM php:8.3-fpm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        opcache \
        pcntl \
        pdo_mysql

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
