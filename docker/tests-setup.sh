#!/bin/bash

set -e

PHPVERSION=${PRODPHP:-72}
#export COMPOSE_BAKE=true

ls -la .
ls -la ..

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d --build

mkdir -p ../tmp ../vendor
ls -la ..
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    ls -la
    git config --global --add safe.directory /var/www/html
'

docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export SYMFONY_ENV=ci;
    test -e ./app/config/parameters.yml && rm -v ./app/config/parameters.yml;
    composer install --prefer-dist -n -o --ignore-platform-reqs;
'

docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    ./app/console cache:clear --env=ci;
'

docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    cd theme;
    export CYPRESS_INSTALL_BINARY=0;
    export EB_THEME=skeune;
    yarn install --frozen-lockfile &&
    yarn build
'

docker compose exec -T engine.dev.openconext.local bash -c '
    echo done > /setup.txt
'


exit 0
