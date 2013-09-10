#!/bin/sh
# Comments all require calls in Zend Framework classes since these can cause loading more classes than necessary.
# Loading Zend Framework classes should be done using an autoloader.
cd vendor/zendframework
find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
-not -wholename '*/Application.php' -print0  | \
xargs -0 sed --regexp-extended --in-place "s#require_once[^;]*;##g"