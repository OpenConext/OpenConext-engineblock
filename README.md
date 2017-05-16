# OpenConext EngineBlock

Build Status:

[![Build Status](https://travis-ci.org/OpenConext/OpenConext-engineblock.svg?branch=master)][travis-build]

## License

See the [LICENSE-2.0.txt][license] file

## Disclaimer

See the [NOTICE.txt][notice] file

## Upgrading

See the [UPGRADING.md][upgrading] file

## (Theme) Development

Please see the [wiki][eb-wiki-theme-development] for information on how to get started with developing themes for OpenConext EngineBlock
In short, themes require compilation which can be done by running the following commands:
```
    (cd theme && npm install && grunt)
```

## System Requirements

* Linux
* Apache
* PHP 5.6:
    - libxml
    - mcrypt
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB
    - default-collation=utf8_unicode_ci
* [Janus][janus]
* NPM (optional for theme deployment)
* Grunt-cli (optional for theme deployment)

_**Note**:
While care was given to make EngineBlock as compliant as possible with mainstream Linux distributions,
it is only regularly tested with RedHat Enterprise Linux and CentOS._

## Installation

_**Note**: you are advised to use [OpenConext-Deploy][op-dep] to deploy OpenConext installations._

If you are reading this then you've probably already installed a copy of EngineBlock somewhere on the destination server,
if not, then that would be step 1 for the installation.

If you do not use [OpenConext-Deploy][op-dep] and have an installed copy and your server meets all the requirements 
above, then please follow the steps below to start your installation.

### First, create an empty database

**EXAMPLE**

    mysql -p
    Enter password:
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 21
    Server version: 5.0.77 Source distribution

    Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

    mysql> create database engineblock default charset utf8 default collate utf8_unicode_ci;


### Then configure application config

Copy over the example configuration file from the *docs* directory to */etc/openconext/engineblock.ini*:

    sudo mkdir /etc/openconext
    sudo cp docs/example.engineblock.ini /etc/openconext/engineblock.ini

Then edit this file with your favorite editor and review the settings to make sure it matches your configuration.
The settings in the *example.engineblock.ini* are a subset of all configuration options, which can be found, along
with their default value in *application/configs/application.ini*.

Note that EngineBlock requires you to set a path to a logfile, but you have to make sure that this file
is writable by your webserver user.

After that, you are required to ensure the application is in bootable state. Assuming you are preparing your 
installation for a production environment, you have to run:

    composer prepare-env

should you not have access to a local installation of [composer][comp], a version is shipped with EngineBlock, replace
the `composer` part above with `bin/composer.phar`. This version is regularly updated, but may give warnings about
being outdated.


### Install database schema

To install the initial database, just call the 'migrate' script in *bin/*, followed by migration tool introduced in 5.x,
like so:

    (cd bin && ./migrate && cd .. && app/console doctrine:migrations:migrate --env=prod)

_**Note**:
EngineBlock requires database settings, without it the install script will not function. Furthermore, this assumes that
the application must use the production settings (`--env=prod`), this could be replaced with `dev` should you run a 
development version._


### Configure HTTP server

Configure a single virtual host, this should point to the `web` directory: 

    DocumentRoot    /opt/www/engineblock/web
    
It should also serve both the `engine.yourdomain.example` and `engine-api.yourdomain.example` domains.

Make sure the `ENGINEBLOCK_ENV` is set, and that the `SYMFONY_ENV` is set, this can be mapped from `ENGINEBLOCK_ENV` as:

| `ENGINEBLOCK_ENV` | `SYMFONY_ENV` |
| --- | --- |
| production | prod |
| acceptance | acc |
| test | test |
| vm | dev |

**EXAMPLE**

    SetEnv ENGINEBLOCK_ENV !!ENV!!
    SetEnv SYMFONY_ENV !!SF_ENV!!

Make sure you have the following rewrite rules (replace `app.php` with `app_dev.php` for development):

    RewriteEngine On
    # We support only GET/POST/HEAD
    RewriteCond %{REQUEST_METHOD} !^(POST|GET|HEAD)$
    RewriteRule .* - [R=405,L]
    # If the requested url does not map to a file or directory, then forward it to index.php/URL.
    # Note that the requested URL MUST be appended because Corto uses the PATH_INFO server variable
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /app.php/$1 [L] # Send the query string to index.php

    # Requests to the domain (no query string)
    RewriteRule ^$ app.php/ [L]

Note that EngineBlock SHOULD run on HTTPS, you can redirect users from HTTP to HTTPS
with the following Apache rewrite rules on a *:80 VirtualHost:

    RewriteEngine   on
    RewriteCond     %{SERVER_PORT} ^80$
    RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R=301]

### Test your EngineBlock instance

Use these URLs to test your EngineBlock instance:

- http://engine.example.com, this should redirect you to the following URL
- https://engine.example.com, show a page detailing information about the capabilities
- https://engine.example.com/authentication/idp/metadata, this should present you with the IdP metadata of EngineBlock
- https://engine.example.com/authentication/sp/metadata, this should present you with the SP metadata of EngineBlock
- https://engine.example.com/authentication/proxy/idps-metadata, this should present you with the proxy IdP metadata
- https://engine-api.example.com, this should return an empty 200 OK response

## Updating

It is recommended practice that you deploy engineblock in a directory that includes the version number and use a
symlink to link to the 'current' version of EngineBlock.

**EXAMPLE**

    .
    ..
    engineblock -> engineblock-v1.6.0
    engineblock-v1.5.0
    engineblock-v1.6.0

If you are using this pattern, an update can be done with the following:

1. Download and deploy a new version in a new directory.

2. Check out the release notes in docs/release_notes/X.Y.Z.md (where X.Y.Z is the version number) for any
   additional steps.

3. Prepare your environment (see above)

        SYMFONY_ENV=prod composer prepare-env

4. Run the database migrations script.

        app/console doctrine:migrations:migrate --env=prod

5. Change the symlink.

## Additional Documentation

Most additional documentation can be found in the [wiki][wiki]. If you want to help with development for instance, you
can take a look at the [Development Guidelines][wiki-development]

Also, the following documentation can be found in the [docs][docs] directory:

1. [License][docs-license]
1. [Release Procedure][docs-release]
1. [EngineBlock Input and Output Command Chains][docs-filter]
1. [Release notes for releases < 5.0.0][docs-release-notes]

[travis-build]: https://travis-ci.org/OpenConext/OpenConext-engineblock
[license]: LICENSE-2.0.txt
[notice]: NOTICE.txt
[upgrading]: UPGRADING.md
[comp]: https://getcomposer.org/
[op-dep]: https://github.com/OpenConext/OpenConext-deploy
[janus]: https://github.com/janus-ssp/janus
[eb-wiki-theme-development]: https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#theme-development
[wiki]: https://github.com/OpenConext/OpenConext-engineblock/wiki
[wiki-development]: https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines
[docs]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/index.md
[docs-license]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/LICENSE
[docs-release]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/release_procedure.md
[docs-filter]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/filter_commands.md
[docs-release-notes]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/release_notes
