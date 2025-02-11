#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nPHP CodeSniffer\n"
./vendor/bin/phpcbf --standard=ci/qa-config/phpcs.xml src
