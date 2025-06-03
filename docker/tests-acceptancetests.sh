#!/bin/bash

set -e

PHPVERSION=${PRODPHP:-72}
#export COMPOSE_BAKE=true

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d

if [[ $( docker compose exec -T engine.dev.openconext.local \
         bash -c 'test -e /setup.txt && cat /setup.txt || echo ""'
       ) != 'done' ]]
then
    echo "setup.txt not found or not done.  Please run tests-setup.sh"
    exit 1
fi

echo
echo  "Installing database fixtures..."
docker compose exec -T engine.dev.openconext.local bash -c '
    export APP_ENV=ci;
    export SYMFONY_ENV=ci
    ./app/console doctrine:schema:drop --force --env=ci &&
    ./app/console doctrine:schema:create --env=ci
    chmod -R 0777 /tmp/eb-fixtures
'

echo "Preparing frontend assets..."
docker compose exec -T engine.dev.openconext.local bash -c '
    export EB_THEME=skeune
    umask 000
    ./theme/scripts/prepare-test.js
    # prepare-test.js secretly clears the cache, so we need to make everything wtiable again
    ls -la /var/www/html/app/cache/ci
'

echo "Behat tests"
docker compose exec -T engine.dev.openconext.local bash -c '
    ls -la /var/www/html/app/logs/ci
    rm -f /var/www/html/app/logs/ci/*
    ls -la /var/www/html/app/logs/ci
    ls -la /tmp
    ls -la /tmp/eb-fixtures
    ps aux
    id
    ./vendor/bin/behat -c ./tests/behat-ci.yml --suite default -vv --format progress --strict
'

echo "Behat tests (with selenium and headless Chrome)"
docker compose exec -T engine.dev.openconext.local bash -c '
    ls -la /var/www/html/app/logs/ci
    rm -f /var/www/html/app/logs/ci/*
    ls -la /var/www/html/app/logs/ci
    ./vendor/bin/behat -c ./tests/behat-ci.yml --suite selenium -vv --format progress --strict
'


exit 0
