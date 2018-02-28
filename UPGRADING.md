# UPGRADE NOTES

## 5.2 -> 5.7

### Database migration tooling
Doctrine Migrations is now the only tool used to manage database schema changes. If your deployment scripts call
`bin/migrate` or `bin/dbpatch.php`, those calls can be removed.

NOTE: Upgrade from EB4.x is no longer supported. Upgrade to EB<=5.6 BEFORE upgrading to 5.7.

https://www.pivotaltracker.com/story/show/148324779

### Removed metadata backends
The Push API is the only supported method of provisioning metadata to EngineBlock. EngineBlock always reads metadata
from the `sso_provider_roles_eb5` table. This means INI configuration regarding metadata repositories can be removed
from your configuration:

    metadataRepository.*
    metadataRepositories[*]
    serviceRegistry.*

If those options are not removed from the configuration, they will be ignored.

https://www.pivotaltracker.com/story/show/154839908

### Configuration of hostname is required
The INI setting 'hostname' was previously optional. EngineBlock used the Host header from the request (HTTP_HOST) when
omitted. Starting from EB5.7, the hostname setting is required and must be present in application.ini or
/etc/openconext/engineblock.ini.

Make sure your INI file contains the hostname setting before deploying EB5.7.

    hostname = engine.example.org

For the development VM:

    hostname = engine.vm.openconext.org

For the demo environment:

    hostname = engine.demo.openconext.org

### Removal of legacy INI settings

The following INI settings are inherited from EB4.x and were never used in EB5.x. They can be safely removed from
configuration files and will be ignored otherwise.

    auth.simplesamlphp.*
    engine.simplesamlphp.*
    defaults.subheader
    defaults.layout
    logger.factory
    logger.conf.handlers
    logger.conf.handler.syslog.factory
    logger.conf.handler.syslog.conf.formatter.factory
    logger.conf.handler.fingers_crossed.factory
    logger.conf.handler.fingers_crossed.conf.handler
    logger.conf.handler.fingers_crossed.conf.activation_strategy.factory
    logger.conf.processor.request_id.factory
    logger.conf.processor.session_id.factory
    error.module
    error.controller
    error.action
    symfony.logPath
    symfony.cachePath

### Migration to Twig as template render engine
All .phtml templates (Zend_Layout) have been rewritten to Twig templates. Any custom theme should be rewritten to
utilize Twig templates. 

https://www.pivotaltracker.com/story/show/155358923

Also see the upgraded [theme wiki][eb-wiki-theme-development] page.

### Changes to translation files

The following files have been renamed:

 - languages/nl.php -> languages/messages.nl.php
 - languages/en.php -> languages/messages.en.php

The placeholder format for translations has been changed from sprintf-style to symfony-style. The following command can
be used to effortlessly convert the placeholders in your existing translation files:

      sed 's,%\([sd]\),%arg1%,;s,%\([sd]\),%arg2%,;s,%\([sd]\),%arg3%,;s,%\([0-9]\)$[sd],%arg\1%,g' -i languages/*.php

Above command will replace placeholders like '%s' and '%d' with named arguments %arg1% through %arg3%, and placeholders
like '%x$s' and '%x$d' with named arguments '%argx%'.

The following translation messages are not used by EB5.x and can be removed from the translation files:

 - english
 - dutch
 - back
 - attribute
 - authentication\_urls
 - idp\_selection\_title
 - idp\_selection\_subheader
 - idp\_selection\_desc
 - our\_suggestion
 - no\_access
 - no\_access\_more\_info
 - no\_results
 - error\_header
 - edit
 - done
 - remove
 - cookie\_not\_set
 - footer
 - close\_question
 - sorry
 - form\_description
 - deleteuser\_success\_header
 - deleteuser\_success\_subheader
 - deleteuser\_success\_desc
 - external\_link
 - consent\_header
 - consent\_subheader
 - consent\_intro
 - consent\_idp\_provides
 - consent\_sp\_is\_provided
 - consent\_terms\_of\_service
 - consent\_accept
 - consent\_decline
 - consent\_notice
 - consent\_header\_info
 - consent\_sp\_idp\_info
 - consent\_aggregated\_attributes\_info
 - sp\_terms\_of\_service
 - name\_id
 - error\_authorization\_policy\_violation\_name
 - error\_group\_oauth
 - error\_group\_oauth\_desc
 - info\_mail\_link

## 5.x -> 5.2

### Consent API
Engineblock's Consent API, which is used by [OpenConext-Profile][op-pro]
 no longer exposes the most recent date of consent given (last use on).
 Instead, it offers only the first consent given (consent given on).

## 4.x -> 5.0

### General

The bulk of the changes between the 4.x and 5.x releases are non-functional. A start has been made to migrate the
 application from the custom framework to [Symfony][sf]. This means that the 4.x application is wrapped within a
 new symfony based application that will eventually replace the current application, while keeping functionality
 intact.

### Backwards Compatibility Breaks

#### Profile extracted to OpenConext-Profile

The Profile component has been replaced by a new application, [OpenConext-Profile][op-pro]. This can be deployed using
 the [OpenConext-Deploy][op-dep] repository (at the moment of writing, using the `engineblock5-centos7` branch).
 The profile functionality has been completely removed from EngineBlock.

#### Different Entrypoint

In the 4.x range, the entrypoint for the EngineBlock application was the `www` folder, with per component (`api`,
 `authentication` and `profile` a different folder containing an `index.php`. In the new setup, with Profile removed,
 a single entrypoint is used: `web/app.php` (in development `web/app_dev.php`). Please ensure that the virtual hosts or
 server blocks are updated accordingly. An example can be found [here][eb-vhost].

#### PHP Versions

As of version 5.0.0-alpha4 the minimum PHP version requirement has been upped to 5.6. This to ensure using a 
[safe and supported version of PHP][php1] to run the platform on. This is a strict requirement - components may no
longer work correctly with a version below PHP 5.6

#### UUID Library

The custom UUID implementation has been replaced with [Ramsey UUID][uuid]. If you used the `Surfnet_Zend_Uuid` anywhere
 please replace this with `\Ramsey\Uuid\Uuid`.

#### Configuration File Location

Configuration file location was moved from `/etc/surfconext/engineblock.ini` to `/etc/openconext/engineblock.ini`
 All other references to `surfconext` have been replaced with `openconext`. Using [OpenConext-Deploy][op-dep] should
 help with any issues that could possibly stem from this change.

#### Configuration

Part of the configuration is now also used in `.yml` configuration. In order to have access to the required
 parameters when booting, `app/config/ini_parameter.yml` must be present. This file is created automatically
 by composer during installation, but can be recreated manually using `bin/composer/dump-required-ini-params.sh`.
 Also, please ensure that after unpacking a release, before using the installation, `composer prepare-env --env=prod`
 is run so that all requirements for a fully functioning application are met (correct configuration, a warmed cache, etc) 

##### Removed Configuration

- `subjectIdAttribute` Due to the migration from LDAP to database storage for registered users, the configuration 
  `subjectIdAttribute` has been removed. The equivalent of the `collabpersonid` configuration value will now always be used.
  
##### Added Configuration

Several feature toggles have been added, and several users can now be configured.

- `engineblock.feature.ldap_integration` see below for more information
- `engineApi.features.metadataPush` controls the availability of the metadata push api. Setting this to `1` will enable it.  
   Only available for the user with the role `ROLE_API_USER_JANUS` - can be configured through the `api.users.janus.username`
   and `api.users.janus.password` settings, which must authenticate using [HTTP Basic Authentication][basic-auth]
- `engineApi.features.consentListing` controls the availability of the consent listing API. Setting this to `1` will enable it.  
   Only available for the user with the role `ROLE_API_USER_PROFILE` - can be configured through the `api.users.profile.username`
   and `api.users.profile.password` settings, which must authenticate using [HTTP Basic Authentication][basic-auth]
- `engineApi.features.metadatApi` controls the availability of the metadata read API. Setting this to `1` will enable it.  
   Only available for the user with the role `ROLE_API_USER_PROFILE` - can be configured through the `api.users.profile.username`
   and `api.users.profile.password` settings, which must authenticate using [HTTP Basic Authentication][basic-auth]
   
For [Attribute Aggregation][op-aag] usage, which can be enabled per service provider by setting the `attributeAggregationRequired`
 configuration value to true, the following configuration has been added:
 
- `attribute_aggregation.base_url` the base_url of the attribute aggregation service
- `attribute.aggregation.username` the username used to authenticate with the attribute aggregation service
- `attribute.aggregation.password` the password used to authenticate with the attribute aggregation service

##### Database Configuration
The Database configuration changed from master/slave to single host to allow for multi-master clustering. This means the `.ini`
configuration has changed from (**this will be ignored now**):
```ini
database.master1.dsn = "mysql:host=123.456.78.90;port=3307;dbname=engineblock"
database.master1.password = password
database.master1.user = username
database.slave1.dsn = "mysql:host=123.456.78.91;port=3307;dbname=engineblock"
database.slave1.password = password
database.slave1.user = username
database.masters[] = master1
database.slaves[] = slave1
```

To:
```ini
database.host = 123.456.78.90
database.port = 3307
database.user = username
database.password = password
database.dbname = engineblock
```

When a master/slave setup is still required, this can be configured by creating a `config_local.yml` in `app/config`.
This file will be loaded automatically if present and will allow overriding any configuration present. Do note that in
order to be able to load the file `app/console cache:clear --env={current_env_as_in_vhost}` must be executed once.
More information on how to configure Doctrine can be found in [the bundle documentation][doct1] and the [configuration
reference documentation][doct2]. Before considering using a master/slave setup, please review [this documentation][doct3]
as to when a master or slave is used for a connection.

#### Login Tracking

EngineBlock 4.x tracks logins in the log_login table of the database. In EngineBlock 5.0 this has been
replaced wih logging to syslog. This to reduce write load on the database. The log statements are written
to syslog using the specific ident `EBAUTH` as can be seen in the `app/config/logging.yml` file.

For each login a message akin to the following is being logged:
```
Apr  1 11:49:00 apps EBAUTH[4861]: {"channel":"authentication","level":"INFO","message":"login granted","context":{"login_stamp":"2016-04-01T11:49:00+02:00","user_id":"urn:collab:person:engine-test-stand.openconext.org:test145950413313365","sp_entity_id":"https:\/\/engine.vm.openconext.org\/functional-testing\/No%20ARP\/metadata","idp_entity_id":"https:\/\/engine.vm.openconext.org\/functional-testing\/TestIdp\/metadata","key_id":null},"extra":{"session_id":"8zxzInsnhMWUOLJpXyz9uxX2TCa","request_id":"56fe440bc31ea"}}
```

#### User Storage

The users are now stored in the database, and optionally also in the LDAP (to facilitate a graceful rollover).
 You are advised to perform a migration as soon as possible, as LDAP functionality is scheduled to be removed in the
 coming releases.

[doct1]: http://symfony.com/doc/master/bundles/DoctrineBundle/index.html
[doct2]: http://symfony.com/doc/2.7/reference/configuration/doctrine.html
[doct3]: http://www.doctrine-project.org/api/dbal/2.3/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html
[php1]: http://php.net/supported-versions.php
[op-dep]: https://github.com/OpenConext/OpenConext-deploy
[op-pro]: https://github.com/OpenConext/OpenConext-profile
[op-aag]: https://github.com/OpenConext/OpenConext-attribute-aggregation
[eb-vhost]: https://github.com/OpenConext/OpenConext-deploy/blob/0a0d4aa9805716a31ac54578b3f0963b5c1fcea0/roles/engineblock5/templates/engine.conf.j2
[basic-auth]: https://en.wikipedia.org/wiki/Basic_access_authentication
[uuid]: https://github.com/ramsey/uuid
[sf]: https://symfony.com
[eb-wiki-theme-development]: https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#theme-development
