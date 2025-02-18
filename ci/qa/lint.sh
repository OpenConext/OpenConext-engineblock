#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nTwig lint\n"
app/console lint:twig theme/

cd theme

echo -e "\nLint frontend assets\n"
yarn lint

cd -
