#!/bin/bash

set -e

PHPVERSION=${PRODPHP:-72}
#export COMPOSE_BAKE=true

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d

if [[ $( docker compose exec -T --user www-data engine.dev.openconext.local \
         bash -c 'test -e /setup.txt && cat /setup.txt || echo ""'
       ) != 'done' ]]
then
    echo "setup.txt not found or not done.  Please run tests-setup.sh"
    exit 1
fi

echo
echo  "Installing database fixtures..."
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export APP_ENV=ci;
    export SYMFONY_ENV=ci
    ./app/console doctrine:schema:drop --force --env=ci &&
    ./app/console doctrine:schema:create --env=ci
'

echo "Preparing frontend assets..."
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export EB_THEME=skeune
    ./theme/scripts/prepare-test.js
'

#echo "Behat tests"
#docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
#    ./vendor/bin/behat -c ./tests/behat-ci.yml --suite default -vv --format progress --strict
#'

echo "Behat tests (with selenium and headless Chrome)"
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    ./vendor/bin/behat -c ./tests/behat-ci.yml --suite selenium -vv --format progress --strict
'


exit 0
