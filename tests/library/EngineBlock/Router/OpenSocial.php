<?php

require_once(dirname(__FILE__) . '/../../../autoloading.inc.php');

class Test_EngineBlock_Router_OpenSocial extends PHPUnit_Framework_TestCase
{
    public function testUnroutables()
    {
        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('');
        $this->assertFalse($routable, "OpenSocial router does not route empty uri");

        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('/');
        $this->assertFalse($routable, "OpenSocial router does not route /");

        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('/default/index/index');
        $this->assertFalse($routable, "OpenSocial router does not route /default/index/index");
    }

    public function testPeopleRoutables()
    {
        $uri = '/social/people/1234/@all';
        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route($uri);
        $this->assertTrue($routable, "OpenSocial router knows to route '$uri'");
        $this->assertEquals('social', $router->getModuleName(), 'OpenSocial router routes to social module');
        $this->assertEquals('people', $router->getControllerName(), 'OpenSocial people call routes to people controller');
        $this->assertEquals('');

        /*
         * /people/
         */
    }
}