SURFnet Collaboration Infrastructure EngineBlock
================================================

The SURFnet Collaboration Infrastructure EngineBlock is a multi-purpose software component
that has as it's goal the following:

- Publicly Proxy and manage Single Sign On logins
- Privately offer OpenSocial data based on the SSO user data

Requirements
------------
* Apache with modules:
** mod_php
* PHP 5.2.x with modules:
** memcache
** ldap
* Java > 1.5
* JDBC MySQL Driver (Download driver from: http://dev.mysql.com/downloads/connector/j/)
* MySQL > 5.x
* Memcached
* LDAP
* Grouper
* Service Registry

Installation
------------

* Unpack

  tar xzvf coin-engineblock.tar.gz

* Configure environment

Edit /etc/profile and add:

  export ENGINEBLOCK_ENV="production"

Where "production" can be replace by your environment of choice.

* Configure application config

  nano application/config/application.ini

* Create the database

  mysql -p
  Enter password:
  Welcome to the MySQL monitor.  Commands end with ; or \g.
  Your MySQL connection id is 21
  Server version: 5.0.77 Source distribution

  Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

  mysql> create database engine_block2 default charset utf8 default collate utf8_unicode_ci;

* Install database schema

  java -jar ./database/liquibase/liquibase.jar \


* ...

* Install Apache Virtual Hosts



Upgrade
-------
