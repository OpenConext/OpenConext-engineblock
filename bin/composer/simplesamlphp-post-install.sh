#!/bin/sh
ROOT_DIR=$(realpath `dirname $0`/../../)

# Applies various changes to simplesamlphp, this should be ran when composer has installed it in vendor

cd $ROOT_DIR

# Add/override SimpleSamlPhp config
cp config/simplesamlphp/config/* vendor/simplesamlphp/simplesamlphp/config/

# Add/override SimpleSamlPhp metadata
cp config/simplesamlphp/metadata/* vendor/simplesamlphp/simplesamlphp/metadata/

# Delete unused config, metadata and modules
rm -rf vendor/simplesamlphp/simplesamlphp/config/acl.php \
       vendor/simplesamlphp/simplesamlphp/config/authmemcookie.php \
       vendor/simplesamlphp/simplesamlphp/config/cas-ldap.php \
       vendor/simplesamlphp/simplesamlphp/config/config-login-auto.php \
       vendor/simplesamlphp/simplesamlphp/config/config-login-feide.php \
       vendor/simplesamlphp/simplesamlphp/config/ldapmulti.php \
       vendor/simplesamlphp/simplesamlphp/config/ldap.php \
       vendor/simplesamlphp/simplesamlphp/config/translation.php \
       vendor/simplesamlphp/simplesamlphp/metadata/adfs-idp-hosted.php \
       vendor/simplesamlphp/simplesamlphp/metadata/adfs-sp-remote.php \
       vendor/simplesamlphp/simplesamlphp/metadata/saml20-idp-hosted.php \
       vendor/simplesamlphp/simplesamlphp/metadata/saml20-sp-remote.php \
       vendor/simplesamlphp/simplesamlphp/metadata/shib13-idp-hosted.php \
       vendor/simplesamlphp/simplesamlphp/metadata/shib13-idp-remote.php \
       vendor/simplesamlphp/simplesamlphp/metadata/shib13-sp-hosted.php \
       vendor/simplesamlphp/simplesamlphp/metadata/shib13-sp-remote.php \
       vendor/simplesamlphp/simplesamlphp/metadata/wsfed-idp-remote.php \
       vendor/simplesamlphp/simplesamlphp/metadata/wsfed-sp-hosted.php