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
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    ./app/console doctrine:schema:drop --force --env=ci &&
    ./app/console doctrine:schema:create --env=ci
'

echo "PHPUnit legacy tests..."
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=eb4 --no-coverage
'

echo "PHPUnit unit tests"
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=unit --no-coverage
'

echo "PHPUnit API acceptance tests"
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=functional --no-coverage
'

echo "PHPUnit integration tests"
docker compose exec -T --user www-data  engine.dev.openconext.local bash -c '
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=integration --no-coverage
'

exit 0

# running all tests suites simultaneously results in a number of tests failing
# 1) OpenConext\EngineBlockBundle\Tests\ExecutionTimeTrackerTest::exceeding_of_execution_time_is_determined_correctly
# 2) OpenConext\EngineBlockBundle\Tests\ExecutionTimeTrackerTest::there_is_no_time_remaining_until_a_given_time_that_is_the_same_as_the_current_execution_time
# 3) OpenConext\EngineBlockBundle\Tests\ExecutionTimeTrackerTest::there_is_no_time_remaining_until_a_given_time_that_is_shorter_than_the_current_execution_time

echo
echo  "Code coverage..."
docker compose exec -T --user www-data engine.dev.openconext.local bash -c '
    export APP_ENV=ci
    export SYMFONY_ENV=ci
    export XDEBUG_MODE=coverage
    ./app/console doctrine:schema:drop --force --env=ci
    ./app/console doctrine:schema:create --env=ci
    sleep 3
    ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --coverage-clover coverage.xml
'

exit 0
