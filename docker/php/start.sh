#!/bin/sh

echo "Waiting for db to be ready..."
until bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done

php bin/console cache:clear --no-warmup
php bin/console cache:pool:clear cache.app
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:warmup

chmod 0777 images

php-fpm