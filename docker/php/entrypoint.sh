#!/bin/sh
set -e

cd /var/www

if [ "${INSTALL_COMPOSER_DEPS:-0}" = "1" ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -f vendor/autoload.php ]; then
    i=0
    while [ ! -f vendor/autoload.php ]; do
        i=$((i + 1))
        if [ "$i" -gt 60 ]; then
            echo "vendor/autoload.php not found"
            exit 1
        fi
        sleep 2
    done
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

if [ -f artisan ]; then
    php artisan optimize:clear >/dev/null 2>&1 || true
fi

exec docker-php-entrypoint "$@"
