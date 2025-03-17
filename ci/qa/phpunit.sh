#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

chown -R www-data app/cache/
chmod -R 0777 /tmp/eb-fixtures

echo -e "\nInstalling database fixtures...\n"
./app/console doctrine:schema:drop --force --env=ci
./app/console doctrine:schema:create --env=ci

echo -e "\nPHPUnit legacy tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=eb4 --coverage-clover coverage.xml

echo -e "\nPHPUnit unit tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=unit --coverage-clover coverage.xml

echo -e "\nPHPUnit API acceptance tests\n"
APP_ENV=ci XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=functional --coverage-clover coverage.xml

echo -e "\nPHPUnit integration tests\n"
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=integration --coverage-clover coverage.xml
