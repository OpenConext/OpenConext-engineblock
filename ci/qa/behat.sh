#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../


CURRENT_ENV="${APP_ENV:-dev}"

if [ "${CURRENT_ENV}" != "ci" ]
then
  echo "The engineblock instance should run in ci mode."
  exit 1
fi

echo -e "\nInstalling database fixtures...\n"
./bin/console doctrine:schema:drop --force --env=ci
./bin/console doctrine:schema:create --env=ci

echo -e "\nPreparing frontend assets\n"
rm -rf var/cache/ci
EB_THEME=skeune ./theme/scripts/prepare-test.js > /dev/null
chown -R www-data var/cache/
chmod -R 0777 /tmp/eb-fixtures

echo -e "\nRun the Behat tests\n"
./vendor/bin/behat -c ./tests/behat.yml --suite default -vv --format pretty --strict

echo -e "\nBehat tests (with headless Chrome)\n"
./vendor/bin/behat -c ./tests/behat.yml --suite functional -vv --format pretty --strict
