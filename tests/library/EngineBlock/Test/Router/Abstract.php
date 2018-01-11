<?php

abstract class EngineBlock_Test_Router_Abstract extends PHPUnit_Framework_TestCase
{
    /**
     * Test that router with classname $routerClass maps uri $uri to module $module (null is the default module),
     * controller $controller (null is the default controller), action $action (null is the default action) and
     * with action arguments $arguments (empty array by default).
     *
     * @deprecated In favor of EngineBlock_Test_Router_AssertionBuilder
     *
     * @param string $routerClass
     * @param string $uri
     * @param bool   $routable
     * @param null|string   $module
     * @param null|string   $controller
     * @param null|string   $action
     * @param array  $arguments
     * @return void
     */
    protected function _testRoute($routerClass, $uri, $routable=true, $module = null, $controller = null, $action = null, $arguments = array())
    {
        /** @var $router EngineBlock_Router_Default */
        $router = new $routerClass();
        if (is_null($module)) {
            $module     = $router->getDefaultModuleName();
        }
        if (is_null($controller)) {
            $controller = $router->getDefaultControllerName();
        }
        if (is_null($action)) {
            $action     = $router->getDefaultActionName();
        }

        if ($routable) {
            $this->assertTrue($router->route($uri), "$routerClass router should be able to route '$uri'");
        }
        else {
            $this->assertFalse($router->route($uri), "$routerClass router should not be able to route '$uri'");
            return;
        }
        $this->assertEquals($module    , $router->getModuleName()     , "$routerClass routes $uri to module '$module'");
        $this->assertEquals($controller, $router->getControllerName() , "$routerClass routes $uri to controller '$controller'");
        $this->assertEquals($action    , $router->getActionName()     , "$routerClass routes $uri to action '$action'");
        $this->assertEquals($arguments , $router->getActionArguments(), "$routerClass gets " . var_export($arguments, true) . " from uri '$uri'");
    }
}
