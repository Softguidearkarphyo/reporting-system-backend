FROM ghcr.io/laravel/sail/php:8.4

# Install cron
RUN apt-get update && apt-get install -y cron

# Copy Laravel files
WORKDIR /var/www/html
COPY . .

# Copy cron job definition
COPY docker/laravel-cron /etc/cron.d/laravel-cron

# Give permissions
RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job
RUN crontab /etc/cron.d/laravel-cron

# Start cron and php-fpm
CMD cron && php-fpm

