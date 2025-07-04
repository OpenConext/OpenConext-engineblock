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

if [[ $( docker compose exec engine \
         bash -c 'test -e /setup.txt && cat /setup.txt || echo ""'
       ) != 'done' ]]
then
    echo "setup.txt not found or not done.  Please run $dir/docker-setup.sh"
    exit 1
fi

docker compose exec -T engine su openconext -c '
  export EB_THEME=skeune
  ./theme/scripts/prepare-test.js
'

docker compose exec -T cypress bash -c '
    cd /e2e/e2e
    cypress run  --browser=chrome --headless --spec "cypress/integration/skeune/**/*.spec.js,cypress/integration/shared/*.spec.js"
'

docker compose exec -T engine su openconext -c '
  export EB_THEME=openconext
  ./theme/scripts/prepare-test.js
'

docker compose exec -T cypress bash -c '
  cd /e2e/e2e
  cypress run --browser=chrome --headless --spec "cypress/integration/openconext/**/*.spec.js"
'
