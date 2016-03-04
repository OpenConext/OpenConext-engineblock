# UPGRADE NOTES

## 4.x -> 5.0

### Backwards Compatibility Breaks

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

[doct1]: http://symfony.com/doc/master/bundles/DoctrineBundle/index.html
[doct2]: http://symfony.com/doc/2.7/reference/configuration/doctrine.html
[doct3]: http://www.doctrine-project.org/api/dbal/2.3/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html
