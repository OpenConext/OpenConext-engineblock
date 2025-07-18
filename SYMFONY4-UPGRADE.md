# Upgrading to Symfony 4

This document outlines the steps taken to upgrade OpenConext EngineBlock from Symfony 3.4 to Symfony 4.4.

## Changes Made

1. Updated composer.json:
   - Added symfony/flex
   - Replaced symfony/symfony with individual Symfony 4.4 components
   - Updated dependencies to be compatible with Symfony 4
   - Updated composer scripts and directory configuration

2. Created new directory structure:
   - src/Kernel.php (replaces app/AppKernel.php)
   - config/bundles.php (replaces registerBundles() in AppKernel)
   - config/packages/* (configuration files for each bundle)
   - config/services.yaml (service configuration)
   - config/routes.yaml (route configuration)
   - public/index.php (replaces web/app.php)
   - bin/console (replaces app/console)
   - .env (environment variables)

## Todo

In the upgrade major changes have been made. I have fixed everything untill a point where engine debug works. I am
certain a lot of functionality is still broken and needs to be updated. We need to turrowly check every functionality and
repair the changes were possible. I already implemented the changes in a way so upgrading to symfony 5.5 and 6.4 will run
more smoothly then this upgrade.

1. Functionality
    - Create a list of all functionalities and check all features
        - [x] Engine homepage
        - [x] Engine sp debug
        - [x] login with sp (manage, profile)
        - [x] Push from manage
        - [x] show WAYF screen
        - [ ] feature ...
        - [ ] etc ...

2. Tests: I completely ignored all tests for now, im not familiar with behat so i do not intent to fix those.
    - Check unit tests
    - Check functional tests

Please add as you go.

## Good to know
The project devconf wont work as is. There are many breaking changes since the configuration changed a lot.

update engineblock `appconf.conf`

```
DocumentRoot /var/www/html/public
ServerName  engine
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

<Directory "/var/www/html/public">
    Require all granted
    Options -MultiViews
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</Directory>

Header always set X-Content-Type-Options "nosniff"

SetEnv HTTPS on
#SetEnv ENGINEBLOCK_ENV dev
#SetEnv SYMFONY_ENV dev

RewriteEngine On
# We support only GET/POST
RewriteCond %{REQUEST_METHOD} !^(POST|GET|DELETE)$
RewriteRule .* - [R=405,L]

# Set the php application handler so mod_php interpets the files
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

ExpiresActive on
ExpiresByType font/* "access plus 1 year"
ExpiresByType image/* "access plus 6 months"
ExpiresByType text/css "access plus 1 year"
ExpiresByType text/js "access plus 1 year"
```

update `docker-compose.override.yml`

```
---
# This configuration uses a sub-mount for ./engine/parameters.yml
# Make sure to NEVER write to parameters.yml in ${ENGINE_CODE_PATH} after starting
# the container. It will destroy the sub-mount!!
services:
  engine:
    image: ghcr.io/openconext/openconext-basecontainers/${ENGINE_PHP_IMAGE:-php72-apache2-node14-composer2:latest}
    volumes:
      - ${ENGINE_CODE_PATH}:/var/www/html
      - ./engine/parameters.yml:/var/www/html/config/packages/parameters.yml
      - ./engine/appconf.conf:/etc/apache2/sites-enabled/appconf.conf
    environment:
      - APP_ENV=${APP_ENV:-dev}
      - SYMFONY_ENV=${APP_ENV:-dev}
      - APP_DEBUG=1
    healthcheck:
      test: ["CMD", "true"]
      interval: 10s
```

It would maybe be a good idea to create a separate branch for this.
