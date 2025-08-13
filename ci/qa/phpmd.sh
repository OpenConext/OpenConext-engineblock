#!/usr/bin/env bash
set -e

cd "$(dirname "$0")/../../"

echo "====================================================="
echo "PHP Mess Detector"
echo "====================================================="
cmd=(./vendor/bin/phpmd src text ci/qa-config/phpmd.xml --exclude '*/Tests/*')
if "${cmd[@]}"
then
    echo "No issues found"
    echo
    exit 0
else
    echo
    exit 2
fi
