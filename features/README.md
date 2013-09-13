To make functional testing work do the following:

Make EngineBlock listen to a different domain on wich the functional testing environment is active
====================================================================================================

Example:
<code>
<VirtualHost *:443>
    DocumentRoot /opt/www/engineblock/www/authentication
    ServerName   engine-test.demo.openconext.org
    SetEnv ENGINEBLOCK_ENV functional-testing

    ...

</VirtualHost>
</code>

Add functional testing domain to hosts file of system running the tests
=======================================================================
Example:
<code>
192.168.56.101 engine-test.demo.openconext.org
</code>

Set functional testing domain in engineblock application config
===============================================================
Example
<code>
[functional-testing : testing]

functionalTesting.engineBlockUrl = "https://engine-test.demo.openconext.org"
</code>
