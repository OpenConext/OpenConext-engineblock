#!/usr/bin/env bash
set -e

dir=$(dirname "$0")
cd "$dir"

PHPVERSION=${PRODPHP:-72}

docker compose \
    -f docker-compose.yml \
    -f docker-compose-php${PHPVERSION}.yml \
    up -d
echo

if [[ $( docker compose exec -T engine \
         bash -c 'test -e /setup.txt && cat /setup.txt || echo ""'
       ) != 'done' ]]
then
    echo "setup.txt not found or not done.  Please run $dir/docker-setup.sh"
    exit 1
fi

docker compose exec -T engine ./ci/qa/all.sh
