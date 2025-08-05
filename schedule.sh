while true
do
  php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1
  sleep 60
done