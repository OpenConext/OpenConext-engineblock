#!/bin/bash

set -e

PHPVERSION=${PRODPHP:-72}
#export COMPOSE_BAKE=true

docker compose \
    -f docker-compose.yml \
    -f "docker-compose-php${PHPVERSION}.yml" \
    up -d

if [[ $( docker compose exec -T engine.dev.openconext.local \
         bash -c 'test -e /setup.txt && cat /setup.txt || echo ""'
       ) != 'done' ]]
then
    echo "setup.txt not found or not done.  Please run tests-setup.sh"
    exit 1
fi

echo
echo "PHP Mess Detector"
docker compose exec -T engine.dev.openconext.local bash -c '
    ./vendor/bin/phpmd src text ci/qa-config/phpmd.xml --exclude */Tests/*
'

echo "PHP CodeSniffer..."
docker compose exec -T engine.dev.openconext.local bash -c '
    ./vendor/bin/phpcs --report=full --standard=ci/qa-config/phpcs.xml --warning-severity=0 --extensions=php src
'

echo "PHP CodeSniffer (legacy code)"
docker compose exec -T engine.dev.openconext.local bash -c '
    ./vendor/bin/phpcs --standard=ci/qa-config/phpcs-legacy.xml --warning-severity=0 --extensions=php -s library
'

echo "Doc header check..."
docker compose exec -T engine.dev.openconext.local bash -c '
    ./vendor/bin/docheader check src/ tests/ library/ --exclude-dir resources --exclude-dir languages
'

exit 0
