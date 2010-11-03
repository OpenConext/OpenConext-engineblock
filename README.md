SURFnet Collaboration Infrastructure EngineBlock
================================================

The SURFnet Collaboration Infrastructure EngineBlock is a multi-purpose software component
that has as it's goal the following:

- Publicly Proxy and manage Single Sign On authentication requests and responses
- Privately offer OpenSocial data based on the SSO user data and Grouper information

Requirements
------------
* Apache with modules:
** mod_php
* PHP 5.2.x with modules:
** memcache
** ldap
* Java > 1.5
* MySQL > 5.x with settings:
** default-storage-engine=InnoDB (recommended)
** default-collation=utf8_unicode_ci (recommended)
* Memcached
* LDAP
* Grouper
* Service Registry
* wget

Installation
------------

* Unpack

  tar xzvf coin-engineblock.tar.gz

* Configure environment

Edit /etc/profile (as root or with sudo) and add:

  export ENGINEBLOCK_ENV="production"

Where "production" can be replace by your environment of choice.
Then open a new terminal to make sure you have the new environment.

* Configure application config

Review all configuration settings in 'production' and make sure they are set properly
for your environment.

  nano application/config/application.ini

* Create the database

  mysql -p
  Enter password:
  Welcome to the MySQL monitor.  Commands end with ; or \g.
  Your MySQL connection id is 21
  Server version: 5.0.77 Source distribution

  Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

  mysql> create database engine_block2 default charset utf8 default collate utf8_unicode_ci;

* Download & Install MySQL JDBC driver

  cd database && ./install_jdbc_driver.sh && cd ..

* Install database schema

  cd database && ./migrate.sh && cd ..

* Install Apache Virtual Hosts

Install 2 HTTPS virtual hosts.
Make sure the ENGINEBLOCK_ENV is set.
Make sure the rewrite rules from www/.htaccess are loaded.
Protect your engineblock internal interface from external requests.

Example:
  <VirtualHost *:80>
        ServerName engineblock-internal.example.com
    
        RewriteEngine   on
        RewriteCond     %{SERVER_PORT} ^80$
        RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R]
  </VirtualHost>

  <Virtualhost *:443>
        ServerAdmin youremail@example.com

        DocumentRoot /var/www/html/coin-engineblock/www/internal
        ServerName engineblock-internal.example.com

        <Directory "/var/www/html/coin-engineblock/www">
            AllowOverride None
        </Directory>

        Include /var/www/html/coin-engineblock/www/.htaccess

        SetEnv ENGINEBLOCK_ENV production

        SSLEngine on
        SSLCertificateFile      /etc/httpd/ssl/engineblock.pem
        SSLCertificateKeyFile   /etc/httpd/ssl/engineblock.key
        SSLCertificateChainFile /etc/httpd/ssl/enbineblock-chain.pem
  </VirtualHost>

  <VirtualHost *:80>
        ServerName engineblock.example.com

        RewriteEngine   on
        RewriteCond     %{SERVER_PORT} ^80$
        RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R]
  </VirtualHost>

  <Virtualhost *:443>
        ServerAdmin youremail@example.com

        DocumentRoot /var/www/html/coin-engineblock/www/public
        ServerName   engineblock.example.com

        <Directory "/var/www/html/coin-engineblock/www">
            AllowOverride None
            Order Allow,Deny
            # Only allow from private ip adresses
            # http://www.jpsdomain.org/networking/nat.html
            allow from 127.0.0.0/8
            allow from 10.0.0.0/8
            allow from 172.16.0.0/12
            allow from 192.168.0.0/16            
        </Directory>

        Include /var/www/html/coin-engineblock/www/.htaccess

        SetEnv ENGINEBLOCK_ENV production

        SSLEngine on
        SSLCertificateFile      /etc/httpd/ssl/engineblock.pem
        SSLCertificateKeyFile   /etc/httpd/ssl/engineblock.key
        SSLCertificateChainFile /etc/httpd/ssl/enbineblock-chain.pem
  </VirtualHost>

* Test your EngineBlock instance

Use these URLs to test your Engineblock instance:
[http://engineblock.example.com][]
[https://engineblock.example.com][]
[https://engineblock.example.com/authentication/idp/metadata][]
[https://engineblock.example.com/authentication/sp/metadata][]
[https://engineblock.dev.coin.surf.net/authentication/proxy/idps-metadata][]
[http://engineblock-internal.example.com][]
[https://engineblock-internal.example.com][]
[https://engineblock-internal.example.com/social/][]
