#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nPHP CodeSniffer\n"
./vendor/bin/phpcs --report=full --standard=ci/qa-config/phpcs.xml --warning-severity=0 --extensions=php src

echo -e "\nPHP CodeSniffer (legacy code)\n"
./vendor/bin/phpcs --standard=ci/qa-config/phpcs-legacy.xml --warning-severity=0 --extensions=php -s library
