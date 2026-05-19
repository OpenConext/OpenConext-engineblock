#!/usr/bin/env bash
set -e

if [ ! -f "composer.json" ]; then
  echo "composer.json not found" >&2
  exit 1
fi

# Extract simple fields (works for standard composer.json formatting)
NAME=$(grep -m1 '"name"' composer.json | sed -E 's/.*"name"[[:space:]]*:[[:space:]]*"([^"]+)".*/\1/')
DESCRIPTION=$(grep -m1 '"description"' composer.json | sed -E 's/.*"description"[[:space:]]*:[[:space:]]*"([^"]*)".*/\1/')
VERSION=$(grep -m1 '"version"' composer.json | sed -E 's/.*"version"[[:space:]]*:[[:space:]]*"([^"]*)".*/\1/')

DATE=$(date -u +"%Y-%m-%dT%H:%M:%S.%3NZ")

printf '{\n'
printf '  "name": "%s",\n' "$NAME"
printf '  "description": "%s",\n' "$DESCRIPTION"
printf '  "version": "%s",\n' "$VERSION"
printf '  "time": "%s"\n' "$DATE"
printf '}\n'
