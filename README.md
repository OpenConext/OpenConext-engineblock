SURFnet SURFconext Service Registry
===================================



Requirements
------------


Installation
------------

* Check out the Service Registry

* Install vhosts, based on these:

    <VirtualHost *:80>
        ServerName serviceregistry.example.com

        RewriteEngine   on
        RewriteCond     %{SERVER_PORT} ^80$
        RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R]
  </VirtualHost>
  <Virtualhost *:443>
        ServerAdmin youremail@example.com

        DocumentRoot /var/www/serviceregistry/www
        ServerName serviceregistry.example.com

        Alias /simplesaml /var/www/serviceregistry/www

        SSLEngine on
        SSLCertificateFile      /etc/httpd/ssl/example.pem
        SSLCertificateKeyFile   /etc/httpd/ssl/example.key
        SSLCertificateChainFile /etc/httpd/ssl/chain-example.pem
  </VirtualHost>

* Create database

* Set the database connection

* Set the SAML metadata for EngineBlock

* Apply JANUS patches

./bin/apply_janus_patches.sh

* Enable the JANUS module
  touch modules/janus/enable

* Run:
  php /bin/initialise_janus.php
 
  This does the following:
  * Installs the database schema for JANUS
  * Applies the JANUS patches
  * Adds the admin user
  * Adds a user for engineblock with full rights

* Switch the Service Registry to log in with 'admin'.

* Log in to JANUS with the admin user

* Add the Service Registry as an SP in JANUS

* Add Identity Providers

* Enjoy your Service Registry!


Updating
--------

* Revert JANUS patches

* Update the Service Registry with Subversion
  svn up

* Apply any new JANUS patches
  ./bin/apply_janus_patches.sh

* Apply any new database patches
  ./bin/apply_db_patches.sh
