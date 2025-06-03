#!/bin/bash

set -e

PHPVERSION=${PRODPHP:-72}

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d

echo "Lint twig..."
docker compose exec -T engine.dev.openconext.local bash -c '
    app/console lint:twig theme/
'

echo "Lint frontend assets..."
docker compose exec -T engine.dev.openconext.local bash -c '
    cd theme
    yarn lint
'

exit 0
