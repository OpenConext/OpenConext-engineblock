#!/usr/bin/env bash
set -e

PHPVERSION=${PRODPHP:-72}
#export COMPOSE_BAKE=true

cd "$(dirname "$0")"

rm -f .env
{
    echo "export APACHE_UID=$(id -u)";
    echo "export APACHE_GID=$(id -g)";
    echo "export COMPOSE_PROJECT_NAME=eb";
} >> .env

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    build --pull

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d

docker compose exec -T engine bash -c '
    install -d --owner=openconext --group=openconext --mode=0755 vendor
    install -d --owner=openconext --group=openconext --mode=0755 /home/openconext
    git config --global --add safe.directory /var/www/html
'

if [[ $( docker compose exec -T engine bash -c '
            test -e /setup.txt && cat /setup.txt || echo ""
    ') == 'done' ]]
then
    echo "setup has already run; nothing to do here"
    exit 0
fi

docker compose exec -T engine su openconext -c '
    export SYMFONY_ENV=ci
    test -e ./app/config/parameters.yml && rm -v ./app/config/parameters.yml
    composer install --prefer-dist --no-interaction --optimize-autoloader --ignore-platform-reqs
'

docker compose exec -T engine su openconext -c '
    ./app/console cache:clear --env=ci
'

docker compose exec -T engine su openconext -c '
    cd theme
    export CYPRESS_INSTALL_BINARY=0
    export EB_THEME=skeune
    yarn install --frozen-lockfile
    yarn build
'

docker compose exec engine bash -c '
    echo done > /setup.txt
'

exit 0
