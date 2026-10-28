#!/bin/sh
set -eu

port="${PORT:-80}"
sed -i "s/Listen 80/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

migration_attempt=1
max_migration_attempts=5

until php artisan migrate --force; do
    if [ "$migration_attempt" -ge "$max_migration_attempts" ]; then
        echo "Database migrations failed after ${max_migration_attempts} attempts." >&2
        exit 1
    fi

    echo "Database migration attempt ${migration_attempt} failed; retrying in 3 seconds." >&2
    migration_attempt=$((migration_attempt + 1))
    sleep 3
done

exec apache2-foreground
