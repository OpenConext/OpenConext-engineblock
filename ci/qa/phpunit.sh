#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

chown -R www-data app/cache/
chmod -R 0777 /tmp/eb-fixtures

echo -e "\nInstalling database fixtures...\n"
./app/console doctrine:schema:drop --force --env=ci
./app/console doctrine:schema:create --env=ci

echo -e "\nPHPUnit legacy tests\n"
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=eb4 --coverage-text

echo -e "\nPHPUnit unit tests\n"
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=unit --coverage-text

echo -e "\nPHPUnit API acceptance tests\n"
APP_ENV=ci ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=functional --coverage-text

echo -e "\nPHPUnit integration tests\n"
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=integration --coverage-text
