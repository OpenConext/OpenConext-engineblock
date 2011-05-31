# SURFnet SURFconext Service Registry #

The SURFnet SURFconext Service Registry is the federation metadata management tool for SURFconext.

It's responsibilities are twofold:
1. Allow administering of SAML2 metadata for the federation.
2. Allow automated access via the REST api to the metadata information.


## Requirements ##
* Linux
* Apache with modules:
    - mod_php
* PHP 5.3.x.
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB (recommended)
    - default-collation=utf8_unicode_ci (recommended)
* EngineBlock

## Installation ##


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


## Updating ##

It is recommended practice that you deploy the Service Registry in a directory that includes
the version number and use a symlink to link to the 'current' version of the Service Registry.

**EXAMPLE**

    .
    ..
    serviceregistry -> serviceregistry-v1.6.0
    serviceregistry-v1.5.0
    serviceregistry-v1.6.0

If you are using this pattern, an update can be done with the following:

1. Download and deploy a new version in a new directory.

2. Check out the release notes in docs/release_notes/X.Y.Z.md (where X.Y.Z is the version number) for any
   additional steps.

3. Run the JANUS patches:

    ./bin/apply_janus_patches.sh

4. Change the symlink.

5. Run the database migrations script.

    cd database && ./update && cd ..