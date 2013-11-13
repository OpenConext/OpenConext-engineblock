#!/bin/sh
# Although due to improved autoloading strategy no longer absolutely necessary this script removes
# Embedded Zend Framework in DbPatch to prevent any type of conflicts or confusion about which Zend Framework code is used.

ROOT_DIR="$(cd -P "$(dirname $0)/../../" && pwd)"

cd $ROOT_DIR
DBPATCH_ZEND_DIR='vendor/dbpatch/dbpatch/src/Zend'
if [ -d $DBPATCH_ZEND_DIR ]; then
    echo "Removing DbPatch Zend dir to prevent conflicts or confusion with already existing Zend version"
    rm -rf $DBPATCH_ZEND_DIR
fi
