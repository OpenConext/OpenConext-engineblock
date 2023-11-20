# UPGRADE NOTES

## 6.13 -> 6.14
Previously the SAML EntityID of the EngineBlock SP that was used to do Stepup (SFO) authentications to the Stepup-Gateway 
always was https://<engineblock.sever.domain.name>/authentication/stepup/metadata. For these authentication the default
EngineBlock key is always used for signing.

If you'd like to key-rollover the StepUp entity (baked into EngineBlock).
The key used to sign the SAML AuthnRequests from this SP is the engineblock default key.

To facilitate a rolling configuration update I want the SP entityID that is used for Stepup to be configurable so that at the same time that the engineblock default key is updated, this entityID can be changed. This then allows two entities, with two different keys, to be configured in the Stepup-Gateway.

There are two new parameters that configure this behavior.

1. `feature_stepup_sfo_override_engine_entityid` [bool] enables/disables the feature. Default: disabled
2. `stepup.sfo.override_engine_entityid` [string] should be set with the Entity ID you'd like to use for the stepup EntityId. Default: ''

The feature flag was added mainly to aid our test suite to easily test this feature.

By default this feature is disabled and the default Entity Id is used for the StepUp entity.

## 6.12 -> 6.13

Some translatable strings have been changed and "raw" use of HTML in
translations mostly reduced.

If you override translation strings, the following strings have been
replaced:

* `serviceprovider_link`, `terms_of_service_link` and `logout_information_link`. They are now replaced by separate `serviceprovider_link_text` (the words) and `serviceprovider_link_target` (the URL to link to), same for the other variants.
* `request_access_instructions` is split into `request_access_instructions_head` (the heading line) and `request_access_instructions_text` (the body text).

If you've overridden other translatable strings, note that use of HTML may not be possible
anymore where it was before, and you would need to remove it.

## 6.7 -> 6.8
The consent removal feature was introduced in release 6.8. This means that consent that is deleted from the profile
application will result in the soft deletion of the consent row for that person, for the service they requested the
consent removal of.

In order to work with this feature, the latest database migration must be installed on your database(s) containing the
`consent` table. This should be as simple as running `app/console doctrine:migrations:migrate`. Or executing
`Version20220425090852` manually.

## 6.2 > 6.3
# Removal of incorrect schacHomeOrganization ARP alias

If your federation relies on the release of the urn:oid:1.3.6.1.4.1.1466.115.121.1.15 sHO alias in the ARP. Then please
put the entry back manually. This config can be found in `application/configs/attributes.json` near line 330.

The following alias can be added back (or review the git history):

```
     "urn:oid:1.3.6.1.4.1.25178.1.2.9": "urn:mace:terena.org:attribute-def:schacHomeOrganization",
-->  "urn:oid:1.3.6.1.4.1.1466.115.121.1.15": "urn:mace:terena.org:attribute-def:schacHomeOrganization",
     "urn:mace:terena.org:attribute-def:schacHomeOrganization": {
         # Snipped config section for the sake of brevity
     }
```

## 6.1 > 6.2

# Remove legacy application.ini configuration
The legacy application.ini configuration support is removed in favour of the Symfony configuration.
From now on the `application/configs/application.ini` and the `/etc/openconext/engineblock.ini` are not being evaluated anymore.
The legacy configuration (`app/config/ini_parameters.yml`) which was generated from those files is now merged into the `parameters.yml`.

If you're using OpenConext-Deploy, Ansible is configured to do this for you and will generate a `parameters.yml` for you.

If you don't use OpenConext-Deploy you should manually add the `ini_parameters.yml` variables to the`parameters.yml` and you're good to go.

In version 6.2 the unused `response_processing_service_binding` column is removed from the `sso_provider_roles_eb5` table.

# Remove Symfony bootstrap cache
Among the cleanup tasks some unused scripts have been removed from the project.
 1. The bootstrap.cache.php file, previously used by Symfony was removed
 2. config/cli-config.php (once used to run Doctrine interactions) was removed

If you happen to use these files please migrate your scripts to using the new standard.


## 6.0 > 6.1
In version 6.1 the special EngineBlock entities in the roles table aren't used anymore in favor of static entities.
You could now remove the entities below from manage and then execute a metadata push.
* engine.{{ base_domain}./authentication/sp/metadata
* engine.{{ base_domain}//authentication/idp/metadata


## 5.x > 6.0
In version 6 EngineBlock dropped PHP 5.6 support. The amount of backwards breaking changes was kept to a minimum. For example we did not yet upgrade to Symfony 4. But we did use some PHP 7.2 features like the Throwable interface.

Expect more significant upgrades from a PHP 7 standpoint in the near future.

### For OpenConext-deploy users
Use an OpenConext-deploy release containing [this revision](https://github.com/OpenConext/OpenConext-deploy/commit/21e75357b1802f346aea0077b8081393865c6112). No release has been tagged after adding the PHP 7.2 support.

### For non-OpenConext-deploy users
In order to upgrade your EngineBlock instance, simply upgrade your PHP version to 7.2 and install the following PHP extensions for that version:
- fpm (if you are using php-fpm)
- cli
- pecl-apcu
- pecl-apcu-bc
- curl
- mbstring
- mysql
- soap
- xml
- gd
- xdebug (if you plan to do some development)

Upgrade your webserver to use PHP 7.2 for your EngineBlock application.

### Changed Stepup LoA Configuration

In engineblock.ini, change this form:
```ini
stepup.loa.mapping[http://example.org/assurance/loa2] = "https://gateway.tld/authentication/loa2"
stepup.loa.mapping[http://example.org/assurance/loa3] = "https://gateway.tld/authentication/loa3"
```
to this form:
```ini
stepup.loa.mapping.1.engineblock = "http://example.org/assurance/loa1"
stepup.loa.mapping.1.gateway = "https://gateway.tld/authentication/loa1"
stepup.loa.mapping.2.engineblock = "http://example.org/assurance/loa2"
stepup.loa.mapping.2.gateway = "https://gateway.tld/authentication/loa3"
stepup.loa.mapping.3.engineblock = "http://example.org/assurance/loa3"
stepup.loa.mapping.3.gateway = "https://gateway.tld/authentication/loa3"
```


## 5.11 > 5.12.0

### Serialised column cleanup
In version 5.12.0 multiple `coin` columns are serialised into a single column with serialised data.
This change will allow easier maintenance because then no migrations are needed when adding or removing a `coin`.
There is no migration to populate the old `coin` columns to the new serialised column while this is needed to let EB function properly with the new changes.
Therefore you should push the data from Manage after you have updated the codebase and ran the `Version20190703235333`.

Be aware that you need to be logged in into manage to push the data after updating the codebase and database schema.

In order to let this work you need to do the following:
 1. Login into manage
 1. Update codebase
 1. Run migrations
 1. Push metadata

## 5.10 -> 5.11

### Improved error feedback
The 5.11 release solely improves the error pages. Some new features have been made available to improve the error pages. Most notable, and requiring changes to the parameters.yml, is the possibility to provide custom Wiki links for specific error pages. You are now also able to display an IdP support button, for IdP related error pages.

The parameters.yml.dist file goes into more detail on how to configure this.

## 5.9 -> 5.10.0

### Add the possibility to sign a SAML response
According to saml2int 0.2, the assertion element must be directly signed, which we do. This suffices for most SP's. However, some SP's require the outer Response element to be signed directly. If configured to do so for that SP, Engineblock will sign the outer response element in addition to the signed Assertion element.

Therefore if the option `metadata:coin:sign_response` on the SP is set the response will be signed. Also a migration `Version20190425205743` is added to add the required column.

To support rolling-updates this migration needs to be executed before updating the code and before the rolling update column cleanup migrations as described below.

### Metadata push memory configurable
The memory used in php for the metadata push is now configurable with the `engineblock.metadata_push_memory_limit` configuration option.

### Rolling update column cleanup
The features from 5.8.3 to support rolling updates have been dropped as they are no longer used.
Running the migrations `Version20180910134145` and `Version20180910175453` will take care of this.
Be aware that the logic used to update the columns are also dropped in this release. So you first have to update the
code and after that you should run the migrations to drop the columns.

The features were introduced in 5.8 but were partially reverted to support rolling updates in 5.8.3 because in
these features table columns were dropped. To be able to role back to a version before 5.8.3 while maintaining database
integrity both version were kept. Currently the columns aren't used anymore so therefore the old implementations could
be dropped completely.

## 5.7 -> 5.8

### Stored metadata incompatibility
Metadata pushed to EngineBlock in earlier versions (EB<5.8) is not compatible with this version. A metadata push is
required after upgrading to EB 5.8.
In order to upgrade from 5.7 to 5.8 you need to go to 5.8.3 to prevent backwards compatibility
breaking changes. From version 5.8.3 rolling updates are supported.

### New user data deprovision API

A new API for deprovisioning user data can be enabled by configuring the following INI settings:

    engineApi.features.deprovision = 1
    engineApi.users.deprovision.username = "..."
    engineApi.users.deprovision.password = "..."

Please note that these settings for now are mandatory! For Engine to work correctly specify a username and password.

### Consent screen additions
The consent screen can now show an IdP provided message. To render this information correctly two new configuration
parameters where introduced:

1. `defaults.logo` The logo of the suite engine block is configured for. This logo isrendered in the NameID section on the consent screen but might be used in other situations.
2. `openconext.supportUrlNameId` A link to the support page giving more information on the NameID strategies available in the OpenConext platform.

### Who's Janus?
All references to Janus have been removed from the EngineBlock codebase and has been substituted with metadata push.
This also includes the configuration settings. Be sure to set the correct values in the INI settings for the push
mechanism to work.

```
; old
engineApi.users.janus.username = "..."
engineApi.users.janus.password = "..."

; new
engineApi.users.metadataPush.username = "..."
engineApi.users.metadataPush.password = "..."
```

### Attribute aggregation required setting
The attribute aggregation was enabled/disabled explicitly in previous releases of Engineblock. This value was based on a
feature flag set in the metadata repository (Manage). This is no longer required as we can distill whether or not this
feature should be enabled based on the existence (or lack of) source indications on the attribute values.

This changes means that a column was dropped from the `sso_provider_roles_eb5` schema. Running the `Version20180724175453`
migration takes care of this. Leaving the column in the database should not prove problematic for the time being.

### RequesterId required setting
The use of a RequesterId can now be enforced on a trusted proxy. To do so, a new metadata flag can be set in the metadata
repository (Manage).

This change means that a column was added from the `sso_provider_roles_eb5` schema. Running the `Version20180804090135`
migration takes care of this.

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

### Stored metadata incompatibility
Metadata pushed to EngineBlock in earlier versions (EB<5.7) is not compatible with this version. A metadata push is
required after upgrading to EB 5.7.

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

### New configuration parameter openconext.supportUrl

A new INI parameter `openconext.supportUrl` has been added. It is currently only used on the 'about OpenConext' slide-in
on the consent page.

It defaults to "https://www.example.org/support" and should contain a URL to a support page for users to read more about
the platform.

### Migration to Twig as template render engine
All .phtml templates (Zend_Layout) have been rewritten to Twig templates. Any custom theme should be rewritten to
utilize Twig templates.

https://www.pivotaltracker.com/story/show/155358923

Also see the upgraded [theme wiki][eb-wiki-theme-development] page.

### Changes to translation files

The following files have been renamed:

 - languages/nl.php -> languages/messages.nl.php
 - languages/en.php -> languages/messages.en.php

The placeholder format for translations has been changed from sprintf-style (%1$s) to symfony-style (%named%).

In order to make the default translations less specific to deployment in the educational world, and less specific to
SURFconext, all occurrences of SURFconext and the noun 'institution' are configurable. Instead of translations like:

    'More information about SURFconext'
    'Find your institution'

We now write the translations as:

    'More information about %suiteName%
    'Find your %organisationNoun%

The values of suiteName and organisationNoun are themselves translations, defaulting to:

    'suite_name'        => 'OpenConext',
    'organisation_noun' => 'organisation',

It is possible to override all translations by placing a overrides.en.php or overrides.nl.php inside the languages
folder. For SURFconext, on deployment the two files will have to be added containing at least:


    <?php

    return [
        'suite_name'        => 'SURFconext,
        'organisation_noun' => 'institution,
    ];

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
