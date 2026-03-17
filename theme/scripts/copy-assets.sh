#!/usr/bin/env bash
set -euo pipefail

THEME=${EB_THEME:-openconext}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$SCRIPT_DIR/.."

mkdir -p "$ROOT/../public/images" "$ROOT/../public/fonts"

# Copy base assets
cp "$ROOT/base/images/"* "$ROOT/../public/images/"
find "$ROOT/base/stylesheets/fonts/" -maxdepth 1 -type f ! -name '*.txt' -exec cp {} "$ROOT/../public/fonts/" \;

# Copy theme assets
cp "$ROOT/$THEME/images/"* "$ROOT/../public/images/"
if [ -d "$ROOT/$THEME/stylesheets/fonts" ]; then
    find "$ROOT/$THEME/stylesheets/fonts/" -maxdepth 1 -type f ! -name '*.txt' -exec cp {} "$ROOT/../public/fonts/" \;
fi
