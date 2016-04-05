# UPGRADE NOTES

## 4.x -> 5.0

### Backwards Compatibility Breaks

#### PHP Versions

As of version 5.0.0-alpha4 the minimum PHP version requirement has been upped to 5.6. This to ensure
using a [safe and supported version of PHP][php1] to run the platform on. 

#### Configuration File Location

- config file location moved from `/etc/surfconext/engineblock.ini` to `/etc/openconext/engineblock.ini`

#### Configuration

Part of the configuration is now also used in `.yml` configuration. In order to have access to the required
parameters when booting, `app/config/ini_parameter.yml` must be present. This file is created automatically
by composer during installation, but can be recreated manually using `bin/composer/dump-required-ini-params.sh`.

#### Database Configuration
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

[doct1]: http://symfony.com/doc/master/bundles/DoctrineBundle/index.html
[doct2]: http://symfony.com/doc/2.7/reference/configuration/doctrine.html
[doct3]: http://www.doctrine-project.org/api/dbal/2.3/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html
[php1]: http://php.net/supported-versions.php
