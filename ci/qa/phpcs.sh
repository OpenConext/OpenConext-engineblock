#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo "====================================================="
echo "PHP CodeSniffer"
echo "====================================================="

./vendor/bin/phpcs -p --report=full --standard=ci/qa-config/phpcs.xml --warning-severity=0 --extensions=php src

echo
echo "====================================================="
echo "PHP CodeSniffer (legacy code)"
echo "====================================================="
./vendor/bin/phpcs -p --standard=ci/qa-config/phpcs-legacy.xml --warning-severity=0 --extensions=php -s library

echo
echo "====================================================="
echo "PHP CodeBeautifier"
echo "====================================================="
./vendor/bin/phpcbf -p --standard=ci/qa-config/phpcs.xml src
