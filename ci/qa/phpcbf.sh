#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nPHP CodeSniffer\n"
./vendor/bin/phpcbf --standard=ci/qa-config/phpcs.xml src

echo -e "\nPHP CodeSniffer (legacy code)\n"
./vendor/bin/phpcs --standard=ci/qa-config/phpcs-legacy.xml library
