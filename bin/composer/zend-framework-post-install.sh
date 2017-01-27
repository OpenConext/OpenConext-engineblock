#!/bin/sh
# Comments all require calls in Zend Framework classes since these can cause loading more classes than necessary.
# Loading Zend Framework classes should be done using an autoloader.
# the regex will replace all paths
# - starting with include, include_once, require and require_once
# - followed by anything except a semicolon
# - ending on .php'; or .php"; or.php'); or .php");
# ending in a semicolon or a (double) quote and a semicolon

ROOT_DIR="$(cd -P "$(dirname $0)/../../" && pwd)"

cd $ROOT_DIR

cd vendor/zendframework

# BSD's "-i" requires an extension for backups.
# To prevent ".php-e" files we explicitly use ".bak" and remove the backups when we're done
# See: http://stackoverflow.com/a/4247319
find . -name '*.php' -print0  | \
xargs -0 sed -E -i '.bak' -e "s#(include|require)_once[^;]*\.php['\"][)]?;##g"
find . -name '*.php.bak' -print0  | \
xargs -0 rm -f
