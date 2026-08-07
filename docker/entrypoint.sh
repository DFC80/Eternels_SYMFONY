#!/bin/bash
set -e

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=db;port=3306;dbname=gaming_association', 'root', 'root');" 2>/dev/null; do
    sleep 2
done
echo "MySQL ready."

php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

exec "$@"
