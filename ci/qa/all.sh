#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

./ci/qa/phpmd.sh
./ci/qa/phpcs.sh
./ci/qa/docheader.sh
./ci/qa/phpunit.sh
./ci/qa/behat.sh
./ci/qa/lint.sh
