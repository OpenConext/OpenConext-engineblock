#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nDoc header check\n"
./vendor/bin/docheader check src/ tests/ library/ --exclude-dir resources --exclude-dir languages
