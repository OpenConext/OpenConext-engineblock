#!/bin/sh
ROOT_DIR=$(realpath `dirname $0`/../../)

# Applies various changes to shindig, this should be ran when composer has installed it in vendor

cd $ROOT_DIR

# Add/override shindig config
cp config/shindig/config/* vendor/apache/shindig/config/

# Delete test files etc. to match exactly to version that was in library before converting to a dependency
rm -rf vendor/apache/shindig/build.xml \
    vendor/apache/shindig/certs \
    vendor/apache/shindig/docs \
    vendor/apache/shindig/external/dbunit.bat \
    vendor/apache/shindig/external/dbunit.php \
    vendor/apache/shindig/external/jsmin-php \
    vendor/apache/shindig/external/resources \
    vendor/apache/shindig/external/Zend \
    vendor/apache/shindig/.htaccess \
    vendor/apache/shindig/index.php \
    vendor/apache/shindig/LICENSE \
    vendor/apache/shindig/NOTICE \
    vendor/apache/shindig/phpunit.xml.dist \
    vendor/apache/shindig/pom.xml \
    vendor/apache/shindig/README \
    vendor/apache/shindig/.svn \
    vendor/apache/shindig/test

#!/bin/sh
# Loading Apache Shindig classes should be done using composer autoloader to make sure correct classes are loaded
# So remove all require statements for src and external files
cd vendor/apache
find . -name '*.php' \
-print0  | \
xargs -0 sed --regexp-extended --in-place "s#require(_once)?\ '[^;]*;##g"