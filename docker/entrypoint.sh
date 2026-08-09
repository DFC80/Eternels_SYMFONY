#!/bin/bash
set -e

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=db;port=3306;dbname=gaming_association', 'root', 'root');" 2>/dev/null; do
    sleep 2
done
echo "MySQL ready."

php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --no-warmup --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

# Re-own all var/ files as www-data so Apache can write cache/logs
chown -R www-data:www-data var/

exec "$@"
