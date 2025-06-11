#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../


CURRENT_ENV="${SYMFONY_ENV:-dev}"

if [ "${CURRENT_ENV}" != "ci" ]
then
  echo "The engineblock instance should run in ci mode."
  exit 1
fi

echo "====================================================="
echo "Installing database fixtures..."
echo "====================================================="
./app/console doctrine:schema:drop --force --env=ci
./app/console doctrine:schema:create --env=ci

echo "====================================================="
echo "Preparing frontend assets"
echo "====================================================="
EB_THEME=skeune ./theme/scripts/prepare-test.js > /dev/null

chown -R www-data app/cache/
chmod -R 0777 /tmp/eb-fixtures

echo "====================================================="
echo "Run the Behat tests"
echo "====================================================="
./vendor/bin/behat -c ./tests/behat-ci.yml --suite default -vv --format progress --strict $@

# TODO!
#echo -e "\nBehat tests (with selenium and headless Chrome)\n"
#./vendor/bin/behat -c ./tests/behat-ci.yml --suite selenium -vv --format progress --strict
