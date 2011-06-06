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


Post-update actions
-------------------

* Add Profile SP to serviceregistry


Quick Test Plan
---------------

* How to quickly test that all functionality is working *
