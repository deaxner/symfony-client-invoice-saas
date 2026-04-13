#!/bin/sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

mkdir -p var/cache var/log

php bin/console cache:clear --no-warmup >/dev/null 2>&1 || true

exec "$@"
