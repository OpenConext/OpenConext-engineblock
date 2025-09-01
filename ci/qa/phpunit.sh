#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

chown -R www-data var/cache/
mkdir -p /tmp/eb-fixtures
chmod -R 0777 /tmp/eb-fixtures

echo -e "\nInstalling database fixtures...\n"
./bin/console cache:clear --env=ci --no-warmup
./bin/console doctrine:schema:drop --force --env=ci
./bin/console doctrine:schema:create --env=ci

echo -e "\nPHPUnit legacy tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=eb4 --coverage-clover coverage.xml

echo -e "\nPHPUnit unit tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=unit --coverage-clover coverage.xml

echo -e "\nPHPUnit API acceptance tests\n"
./bin/console cache:clear --env=test --no-warmup
APP_ENV=test XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=functional --coverage-clover coverage.xml

echo -e "\nPHPUnit integration tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=integration --coverage-clover coverage.xml
