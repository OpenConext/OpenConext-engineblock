Welcome to the EngineBlock Database install / patching tooling!

On LiquiBase
============
We use the LiquiBase 2.0 <http://www.liquibase.org/> Database Refactoring tool.
LiquiBase is a Java tool that uses XML files for managing Database installing / updating.

The LiquiBase functionality is in:
 /database/liquibase/

The actual XML changelogs are in:
 /database/

You may already be used to simple SQL patch files and your reaction might be 'WTF?'.
LiquiBase was used because of it's advantages (primarily for the long term of EngineBlock):

What we gain:
- Database keeps track of it's own version
- Automatic rollbacks
- Branching support in patch files
And more: <http://www.liquibase.org>

What we lose:
- Ability to copy and paste SQL snippets from MySQL admin tool
- Time in learning a small new XML syntax

Installing 
==========
You need to have Java 1.6 installed on your server.
You also need the MySQL JDBC driver, you can install this with:
  ./install_jdbc_driver.sh

After that you can use the .sh scripts in database/ or call database/liquibase.jar yourself.

Performing an update
====================
  ./update

Adding a Patch
==============
Open up the appropriate changelog file for the version and add the XML.
Then test it with ./update.
