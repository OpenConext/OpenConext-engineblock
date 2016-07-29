# OpenConext EngineBlock #

Build Status:

| Branch  | Status |
| ------- | ------ |
| 5.x-dev | [![Build Status](https://travis-ci.org/OpenConext/OpenConext-engineblock.svg?branch=5.x-dev)](https://travis-ci.org/OpenConext/OpenConext-engineblock) |
| master  | [![Build Status](https://travis-ci.org/OpenConext/OpenConext-engineblock.svg?branch=master)](https://travis-ci.org/OpenConext/OpenConext-engineblock) |


## License

See the LICENSE-2.0.txt file

## Disclaimer

See the NOTICE.txt file


## System Requirements ##

* Linux
* Apache with modules:
    - mod_php
* PHP 5.3.x with modules:
    - ldap
    - libxml
    - mcrypt
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB (recommended)
    - default-collation=utf8_unicode_ci (recommended)
* LDAP
* Internet2 Grouper
* Service Registry
* wget
* NPM (optional for theme deployment)
* Grunt-cli (optional for theme deployment)

**NOTE**
While care was given to make EngineBlock as compliant as possible with mainstream Linux distributions,
it is only regularly tested with RedHat Enterprise Linux and CentOS.


## Installation ##

If you are reading this then you've probably already installed a copy of EngineBlock somewhere on the destination server,
if not, then that would be step 1 for the installation.

If you have an installed copy and your server meets all the requirements above, then please follow the steps below
to start your installation.

### First, create an empty database ###

**EXAMPLE**

    mysql -p
    Enter password:
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 21
    Server version: 5.0.77 Source distribution

    Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

    mysql> create database engineblock default charset utf8 default collate utf8_unicode_ci;


### Then configure application config ###

Copy over the example configuration file from the *docs* directory to */etc/openconext/engineblock.ini*:

    sudo mkdir /etc/openconext
    sudo cp docs/example.engineblock.ini /etc/openconext/engineblock.ini

Then edit this file with your favorite editor and review the settings to make sure it matches your configuration.
The settings in the *example.engineblock.ini* are a subset of all configuration options, which can be found, along
with their default value in *application/configs/application.ini*.

Note that EngineBlock requires you to set a path to a logfile, but you have to make sure that this file
is writable by your webserver user.


### Install database schema ###

To install the initial database, just call the 'migrate' script in *bin/*, like so:

    cd bin && ./migrate && cd ..

**NOTE**
EngineBlock requires database settings, without it the install script will not function


### Configure HTTP server ###

Configure 2 HTTPS virtual hosts, one that points to the authentication interface, which handles authentication and
proxying thereof. The second one should point to the profile interface.

Make sure the `ENGINEBLOCK_ENV` is set.
Make sure the `SYMFONY_ENV` is set, this can be mapped from `ENGINEBLOCK_ENV` as:
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

For all virtual hosts you should specify the same *DocumentRoot*.

1st virtual host:

    DocumentRoot    /opt/www/engineblock/web

2nd virtual host:

    DocumentRoot    /opt/www/engineblock/web

Also for the 2nd virtual host (profile interface) you should specify an extra *RewriteCond*.
Add it behind the last *RewriteCond* descriptive (but before the *RewriteRule* descriptive) in your virtual host
configuration:

    RewriteCond %{REQUEST_URI} !^/(simplesaml.*)$

Also set an alias for simplesaml for the third virtual host:

    Alias /simplesaml /opt/www/engineblock/vendor/simplesamlphp/simplesamlphp/www

### Finally, test your EngineBlock instance ###

Use these URLs to test your Engineblock instance:
[http://engine.example.com]
[https://engine.example.com]
[https://engine.example.com/authentication/idp/metadata]
[https://engine.example.com/authentication/sp/metadata]
[https://engine.example.com/authentication/proxy/idps-metadata]
[https://engine-api.example.com]
[https://profile.example.com]

## Updating ##

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

3. Change the symlink.

4. Install & Run the database migrations script.

    cd bin/ && ./migrate && cd ..

## Applying a new theme ##

Before being able to use the new theming system, you must install the following:

- [Node.JS][1]
- [Bower][2] (requires Node.JS)
- [Compass][3]

After installing the above tools, the following commandline may give you all the needed dependencies and run grunt to update the installed files after changing a theme:
```
(cd theme && npm install && sudo npm install -g bower && bower install && grunt)
```

When applying a theme for the first time you can enter the theme directory and run `npm install` and `bower install` to
load the required theme modules.

Themes can be deployed using a Grunt task, from the theme directory run `grunt theme:mythemename`, this will initiate
the appropriate tasks for cleaning the previous theme and deploying the new theme on your installation.

[1]: https://nodejs.org/en/
[2]: http://bower.io/
[3]: http://compass-style.org/
