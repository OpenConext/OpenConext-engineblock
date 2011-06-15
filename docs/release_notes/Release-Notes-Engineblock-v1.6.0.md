# SURFnet SURFconext Engineblock v1.6.0 Release Notes #

* About this release, what is the main focus *

Notable issues resolved with this release:
* List of issues *

For a detailed list of issues resolved see:
* Bugtracker? *


Pre-update actions
------------------

* Virtual host modifications
    - The public endpoint is renamed to authentication, so the DocumentRoot for the
      engine.{dev,test,acc,prod}.surfconext.nl vhost should be renamed to
      
      /opt/www/engineblock/www/authentication

    - Additional virtual host for the profile page
      Add an additional HTTPS virtual host for the profile interface should be added.
      For the guidelines of the virtual host configuration see the README.md file in the docs folder.
      However, set the DocumentRoot to the following location:

        DocumentRoot    /opt/www/engineblock/www/profile

    - Specify the environment

       SetEnv ENGINEBLOCK_ENV *REPLACE WITH ENVIRONMENT FROM YOUR ENGINEBLOCK.INI*

    - Add the following alias:

        Alias /simplesaml LOCATION_OF_ENGINEBLOCK/library/simplesamlphp/www

    - Add the following rewrites:

        # If the requested url does not map to a file or directory, then forward it to index.php/URL.
        # Note that it MUST be index.php/URL because Corto uses the PATH_INFO server variable
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_URI} !^/(simplesaml.*)$
        RewriteRule ^(.*)$ /index.php/$1 [L] # Send the query string to index.php

        # Requests to the domain (no query string)
        RewriteRule ^$ index.php/ [L]


Post-update actions
-------------------

* Add Profile SP to serviceregistry


Quick Test Plan
---------------

* How to quickly test that all functionality is working *
