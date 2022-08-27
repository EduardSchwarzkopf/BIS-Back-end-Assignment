FROM docker.io/bitnami/laravel:9

RUN apt-get update && apt-get install -y cron

USER 1000

RUN (crontab -l ; echo "* * * * * cd /app && php artisan model:prune >> /dev/null 2>&1") | crontab