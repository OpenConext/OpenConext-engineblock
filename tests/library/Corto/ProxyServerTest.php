<?php



class Corto_ProxyServerTest extends PHPUnit_Framework_TestCase
{
    public function testParametersFromUrl()
    {
        $_SERVER['SCRIPT_NAME'] = '/path/corto.php';

    }
}
