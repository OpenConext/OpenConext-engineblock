#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

chown -R www-data app/cache/
mkdir -p /tmp/eb-fixtures
chmod -R 0777 /tmp/eb-fixtures

echo "====================================================="
echo "Installing database fixtures..."
echo "====================================================="
./app/console doctrine:schema:drop --force --env=ci
./app/console doctrine:schema:create --env=ci

echo
echo "====================================================="
echo "PHPUnit legacy tests"
echo "====================================================="
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=eb4 --coverage-text

echo
echo "====================================================="
echo  "PHPUnit unit tests"
echo "====================================================="
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=unit --coverage-text

echo
echo "====================================================="
echo "PHPUnit API acceptance tests"
echo "====================================================="
APP_ENV=ci ./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=functional --coverage-text

echo
echo "====================================================="
echo -e "PHPUnit integration tests"
echo "====================================================="
./vendor/bin/phpunit --configuration=./tests/phpunit.xml --testsuite=integration --coverage-text
echo


