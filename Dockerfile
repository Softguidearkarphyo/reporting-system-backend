FROM sail-8.4/app

# Add proper cron user (optional but for safety)
RUN useradd -u 1001 -m -s /bin/bash cron

# Install cron silently
RUN apt-get update -qq && apt-get install -y -qq cron > /dev/null

# Copy cronjob into /etc/cron.d (do NOT use crontab)
COPY cronjobs /etc/cron.d/cronjobs

# Set permissions
RUN chmod 0644 /etc/cron.d/cronjobs

# Copy artisan scheduler log script (optional if you want loop-based method)
COPY schedule.sh /var/www/html/schedule.sh
RUN chmod +x /var/www/html/schedule.sh

# Create log file (optional)
RUN touch /var/log/cron.log && chown root:root /var/log/cron.log

# Start cron
CMD ["cron", "-f"]

