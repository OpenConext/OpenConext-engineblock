# OpenConext EngineBlock #

The SURFnet SURFconext EngineBlock is a multi-purpose software component
that has as it's goal the following:

1. Publicly Proxy and manage Single Sign On authentication requests and responses
2. Privately offer OpenSocial data based on the SSO user data and Grouper information


## Requirements ##

* Linux
* Apache with modules:
    - mod_php
* PHP 5.3.x with modules:
    - memcache
    - ldap
    - libxml
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB (recommended)
    - default-collation=utf8_unicode_ci (recommended)
* LDAP
* Internet2 Grouper
* Service Registry
* wget
* Memcached (optional)

**NOTE**
While care was given to make EngineBlock as compliant as possible with mainstream Linux distributions,
it is only regularly tested with RedHat Enterprise Linux and CentOS.


## Installation ##

If you are reading this then you've probably already installed a copy of EngineBlock somewhere on the destination server,
if not, then that would be step 1 for the installation.

If you have an installed copy and your server meets all the requirements above, then please follow the steps below
to start your installation.


### What you NEED to know about EngineBlock ###

EngineBlock was designed to be deployed on multiple environments, sometimes multiple times on the same machine.
As such EngineBlock has a mechanism called 'Environments' to differentiate between... well... environments.

So a normal setup would be something like this:

    | Server 1      |   | Server 2   |  |  Server 3  |
    |               |   |            |  |            |
    | development   |   | staging    |  | production |
    | testing       |   |            |  |            |

In this scenario, Server 2 would have an */etc/surfconext/engineblock.ini* that starts with:

    [staging : base]

and in the Apache Virtual Host would be the following:

    SetEnv ENGINEBLOCK_ENV staging

So whenever a request comes in for EngineBlock, Apache would tell EngineBlock to load up the 'staging'
configuration values.

This is a big help for Server 1, which can have the following in it's configuration file:

    [development : base]
     .... Lots of configuration values ...

     [testing : base]
     .... Different configuration values ...

With that out of the way, let's get started!


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

Copy over the example configuration file from the *docs* directory to */etc/surfconext/engineblock.ini*:

    sudo mkdir /etc/surfconext
    sudo cp docs/example.engineblock.ini /etc/surfconext/engineblock.ini

Then edit this file with your favorite editor and review the settings to make sure it matches your configuration.
The settings in the *example.engineblock.ini* are a subset of all configuration options, which can be found, along
with their default value in *application/configs/application.ini*.

Note that EngineBlock requires you to set a path to a logfile, but you have to make sure that this file
is writable by your webserver user.


### Next, configure environment ###

Edit */etc/profile* (as root or with sudo) and add:

    export ENGINEBLOCK_ENV="!!ENV!!"

Where "!!ENV!!" MUST be replace by your environment of choice.
Then open a new terminal to make sure you have the new environment.

    echo $ENGINEBLOCK_ENV

This is done so shell scripts (like the database update) can also know which configuration to use.

If you have multiple EngineBlock instances on one machine and don't want to keep setting ENGINEBLOCK_ENV
you can also NOT set the ENGINEBLOCK_ENV variable, but instead make EngineBlock auto-detect what
environment to use with the following configuration settings:

    env.host = "myserver"
    env.path = "/var/www/engineblock-1"

Or any combination of those 2.

The *env.host* configuration will make EngineBlock autodetect which settings to use
for a particulair host (usefull if you are sharing a configuration file for multiple instances on multiple servers)
based on the hostname (you can check which hostname it uses with *hostname -f*).

The *env.path* configuration will make EngineBlock autodetect which settings to use
for a particulair path that EngineBlock is installed in.


### Install database schema ###

To install the initial database, just call the 'migrate' script in *bin/*, like so:

    cd bin && ./migrate && cd ..

**NOTE**
EngineBlock requires database settings, without it the install script will not function


### Configure HTTP server ###

Install 3 HTTPS virtual hosts, one that points to the authentication interface, which handles authentication and
proxying thereof. The second one should point to the internal interface. Finally, the third one should point to the
profile interface.

Make sure the ENGINEBLOCK_ENV is set.

**EXAMPLE**

    SetEnv ENGINEBLOCK_ENV !!ENV!!

Make sure you have the following rewrite rules:

    RewriteEngine On
    # If the requested url does not map to a file or directory, then forward it to index.php/URL.
    # Note that it MUST be index.php/URL because Corto uses the PATH_INFO server variable
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php/$1 [L] # Send the query string to index.php

    # Requests to the domain (no query string)
    RewriteRule ^$ index.php/ [L]

Note that EngineBlock SHOULD run on HTTPS, you can redirect users from HTTP to HTTPS
with the following Apache rewrite rules on a *:80 VirtualHost:

    RewriteEngine   on
    RewriteCond     %{SERVER_PORT} ^80$
    RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R=301]

For all virtual hosts you should specify a different *DocumentRoot*.

1st virtual host:

    DocumentRoot    /opt/www/engineblock/www/authentication

2nd virtual host:

    DocumentRoot    /opt/www/engineblock/www/internal

3rd virtual host:

    DocumentRoot    /opt/www/engineblock/www/profile

Also for the 3rd virtual host (profile interface) you should specify an extra *RewriteCond*.
Add it behind the last *RewriteCond* descriptive (but before the *RewriteRule* descriptive) in your virtual host
configuration:

    RewriteCond %{REQUEST_URI} !^/(simplesaml.*)$

Also set an alias for simplesaml for the third virtual host:

    Alias /simplesaml /opt/www/engineblock/vendor/simplesamlphp/simplesamlphp/www

4th virtual host:

    ServerName      vomanage.[dev|test|acc|prod].surfconext.nl:443
    DocumentRoot    /opt/www/engineblock/www/vomanage

Also for the 4th virtual host (VO attribute management interface) you should specify an extra *RewriteCond*.
Add it behind the last *RewriteCond* descriptive (but before the *RewriteRule* descriptive) in your virtual host
configuration:

    RewriteCond %{REQUEST_URI} !^/(simplesaml.*)$

Also set an alias for simplesaml for the fourth virtual host:

    Alias /simplesaml /opt/www/engineblock/vendor/simplesamlphp/simplesamlphp/www

### Virtual host for static files ###

Engineblock needs a fourth virtual host for media, style and script files, below an example virtual host configuration
is given.

**Note** Please make the virtual host an https virtual host because otherwise Internet Explorer will give you messages
about some content not coming from a secure connection.

Fill the DocumentRoot of the static virtual host with the contents of:

    https://svn.surfnet.nl/svn/coin-eb/static/tags/!!VERSION!!

Take the same version of the static content as you have checked out of Engineblock.

**EXAMPLE**

    <Virtualhost *:443>
       DocumentRoot "/opt/www/static
       ServerName static.example.com

        ErrorLog                logs/static_error_log
        TransferLog             logs/static_access_log

        SSLEngine on

        SSLProtocol -ALL +SSLv3 +TLSv1
        SSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:!RC4-MD5:RC4+RSA:+HIGH:+MEDIUM

        SSLCertificateFile      /etc/httpd/keys/static.example.com.pem
        SSLCertificateKeyFile   /etc/httpd/keys/static.example.com.key
        SSLCACertificateFile    /etc/httpd/keys/static.example.com_cabundle.pem

    </VirtualHost>

### Finally, test your EngineBlock instance ###

Use these URLs to test your Engineblock instance:
[http://engineblock.example.com][]
[https://engineblock.example.com][]
[https://engineblock.example.com/authentication/idp/metadata][]
[https://engineblock.example.com/authentication/sp/metadata][]
[https://engineblock.dev.coin.surf.net/authentication/proxy/idps-metadata][]
[http://engineblock-internal.example.com][]
[https://engineblock-internal.example.com][]
[https://engineblock-internal.example.com/social/][]
[https://static.example.com][]


### Optional: Install attribute-manipulations ###

EngineBlock has the concept of Attribute Manipulations, which allows you to manipulations per Service Provider on
the attributes and response released to that Service Provider.

If you want to use attribute-manipulations, simply make a directory called 'attribute-manipulations' in the same folder
that EngineBlock is located in.

For more documentation please see 
[https://svn.surfnet.nl/svn/coin-eb/attribute-manipulations/trunk/][SURFnets attribute manipulations].


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

5. Install new Static content for engineblock.

    Check out the corresponding version of the static content. The content can be found at:

        https://svn.surfnet.nl/svn/coin-eb/static/tags/!!VERSION!!

    *NOTE* Please use the same recommended practice for the static content as for engineblock. So, create a new
    directory for every tag you check out and change the symlink to make that version the 'current' version used by
    Apache.