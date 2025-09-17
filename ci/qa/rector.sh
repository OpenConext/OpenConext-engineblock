#!/usr/bin/env sh

# Ensure we run from project root
cd "$(dirname "$0")/../../" || exit 1
./vendor/bin/rector --config=ci/qa-config/rector.php "$@"
