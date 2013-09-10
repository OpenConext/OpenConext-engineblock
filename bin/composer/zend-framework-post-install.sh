#!/bin/sh
# Comments all require calls in Zend Framework classes since these can cause loading more classes than necessary.
# Loading Zend Framework classes should be done using an autoloader.
# the regex will replace all paths
# - starting with include, include_once, require and require_once
# - followed by anything except a semicolon
# - ending on .php'; or .php"; or.php'); or .php");
# ending in a semicolon or a (double) quote and a semicolon

cd vendor/zendframework
find . -name '*.php' -print0  | \
xargs -0 sed --regexp-extended --in-place "s#(include|require)_once[^;]*\.php['\"][)]?;##g"