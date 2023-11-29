
# OpenConext EngineBlock

Build Status:

![test-integration](https://github.com/OpenConext/OpenConext-engineblock/workflows/test-integration/badge.svg?branch=master&event=push)

## License

See the [LICENSE-2.0.txt][license] file

## Disclaimer

See the [NOTICE.txt][notice] file

## Upgrading

See the [UPGRADING.md][upgrading] file

## (Theme) Development

Please see the [wiki][eb-wiki-theme-development] for information on how to get started with developing themes for OpenConext EngineBlock
In short, themes require front-end resource compilation which can be done by running the following commands:

First set the desired theme name in the parameters.yml. This will load the correct Twig templates as they are a part of the Symfony config.

```
parameters:
    # Other parameters have been left out for brevity
    theme.name: skeune
```

Next build the front-end assets for the selected theme.

```
    (cd theme && yarn install --frozen-lockfile && yarn build)
```

Finally, when not in an environment with the debug flag enabled, you need to clear the cache. This will ensure the translations and templates are swapped out for the ones found in the new theme.

```
$ php72 ./app/console cache:clear --env=prod
```

To setup the required tooling on the VM, the following steps might be useful:

    cd /opt/openconext/OpenConext-engineblock/theme
    sudo curl --silent --location https://rpm.nodesource.com/setup_11.x | sudo bash -
    sudo yum install nodejs
    sudo npm install --global yarn
    (yarn install --frozen-lockfile && yarn build)

In addition to the npm/yarn scripts that are available to run (unit/e2e) tests and quality assurance, you can also use the
Twig linting tool built into Symfony. To run this linter:

```
If you are able to run Ant build targets use:
$ ant php-twig-lint

But you can also run the linter directly from the Symfony console. From the webroot:
$ php72 ./app/console lint:twig theme/
```

## System Requirements

* Linux
* Apache
* PHP 7.2:
    - libxml
    - apcu
    - apcu-bc (a requirement while we are using an older Doctrine version)
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB
    - default-collation=utf8_unicode_ci
* [Manage][manage]
* Composer (for php dendency management)
* Yarn (optional for theme deployment)

_**Note**:
While care was given to make EngineBlock as compliant as possible with mainstream Linux distributions,
it is only regularly tested with RedHat Enterprise Linux and CentOS._

## Installation

_**Note**: you are highly encouraged to use [OpenConext-Deploy][op-dep] to deploy OpenConext installations._

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


### Then configure application

EngineBlock requires you to have the folders below writable by your webserver user.

     app/cache

Run the command below in the root of your project to install the required dependencies.

    composer install --no-interaction --optimize-autoloader --prefer-dist --no-dev

 **Note**:
The command above assumes that the application must be build for production, and omits development dependencies.

Then edit the `parameters.yml` with your favorite editor and review the settings to make sure it matches your configuration.

### Install database schema updates

To install possible database updates, call doctrine migrations by using the following console command:

    app/console doctrine:migrations:migrate --env=prod

_**Note**:
EngineBlock requires database settings, without it doctrine migrate will not function. Furthermore, this assumes that
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

### Grab the front controller
Copy the `app_dev.php.dist` file to the `web` directory.

```bash
Openconext-engineblock $ cp app_dev.php.dist web/app_dev.php
```

### Test your EngineBlock instance

Use these URLs to test your EngineBlock instance:

- http://engine.example.com, this should redirect you to the following URL
- https://engine.example.com, show a page detailing information about the capabilities
- https://engine.example.com/authentication/idp/metadata, this should present you with the IdP metadata of EngineBlock
- https://engine.example.com/authentication/sp/metadata, this should present you with the SP metadata of EngineBlock
- https://engine.example.com/authentication/proxy/idps-metadata, this should present you with the proxy IdP metadata
- https://engine-api.example.com, this should return an empty 200 OK response

### Security

Please see [security considerations](docs/security.md) to harden your Engineblock after installation.

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

2. Check out the release notes in UPGRADING.md

3. Run the database migrations script.

        app/console doctrine:migrations:migrate --env=prod

4. Change the symlink.

## Browsers support

The list of browsers that should be supported:

| [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/edge/edge_48x48.png" alt="IE / Edge" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>IE / Edge | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/firefox/firefox_48x48.png" alt="Firefox" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Firefox | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Chrome | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari/safari_48x48.png" alt="Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari-ios/safari-ios_48x48.png" alt="iOS Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>iOS Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/samsung-internet/samsung-internet_48x48.png" alt="Samsung" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Samsung | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/opera/opera_48x48.png" alt="Opera" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Opera |
| --------- | --------- | --------- | --------- | --------- | --------- | --------- |
| IE10, IE11, Edge| last 2 versions| last 2 versions| last 2 versions| last 2 versions| last 2 versions| last 2 versions

The list of browsers being tested:

| [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/edge/edge_48x48.png" alt="IE / Edge" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>IE / Edge | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/firefox/firefox_48x48.png" alt="Firefox" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Firefox | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Chrome | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Chrome Android | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari/safari_48x48.png" alt="Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari-ios/safari-ios_48x48.png" alt="iOS Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)</br>iOS Safari |
| --------- | --------- | --------- | --------- | --------- | --------- |
| IE11, Edge last version| last version| last version| last version| last version| last version|

## Additional Documentation

Most additional documentation can be found in the [wiki][wiki]. If you want to help with development for instance, you
can take a look at the [Development Guidelines][wiki-development]

Also, the following documentation can be found in the [docs][docs] directory:

1. [License][docs-license]
1. [Release Procedure][docs-release]
1. [EngineBlock Input and Output Command Chains][docs-filter]
1. [Release notes for releases < 5.0.0][docs-release-notes]

[qa-build]: https://github.com/OpenConext/OpenConext-engineblock/workflows/test-integration/badge.svg
[license]: LICENSE-2.0.txt
[notice]: NOTICE.txt
[upgrading]: UPGRADING.md
[comp]: https://getcomposer.org/
[op-dep]: https://github.com/OpenConext/OpenConext-deploy
[manage]: https://github.com/OpenConext/OpenConext-manage
[eb-wiki-theme-development]: https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#theme-development
[wiki]: https://github.com/OpenConext/OpenConext-engineblock/wiki
[wiki-development]: https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines
[docs]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/index.md
[docs-license]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/LICENSE
[docs-release]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/release_procedure.md
[docs-filter]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/filter_commands.md
[docs-release-notes]: https://github.com/OpenConext/OpenConext-engineblock/tree/master/docs/release_notes
