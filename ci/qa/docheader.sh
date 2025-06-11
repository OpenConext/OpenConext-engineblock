#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo "====================================================="
echo "Doc header check"
echo "====================================================="
./vendor/bin/docheader check src/ tests/ library/ --exclude-dir resources --exclude-dir languages
echo
