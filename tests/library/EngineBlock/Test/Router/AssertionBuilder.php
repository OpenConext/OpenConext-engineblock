<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Test_Router_AssertionBuilder
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected $_test;

    protected $_routerClass;

    /**
     * @var EngineBlock_Router_Interface
     */
    protected $_router;

    protected $_uri;

    protected $_isRoutable;

    protected $_requiredModule;

    protected $_requiredController;

    protected $_requiredAction;

    protected $_expectModule;

    protected $_expectController;

    protected $_expectAction;

    protected $_expectArguments = array();

    public static function create(PHPUnit_Framework_TestCase $test)
    {
        return new self($test);
    }

    public function __construct(PHPUnit_Framework_TestCase $test)
    {
        $this->_test = $test;
    }

    public function routerClass($className)
    {
        $this->_routerClass = $className;
        $this->_router = new $className();
        return $this;
    }

    public function uri($uri)
    {
        $this->_uri = $uri;
        return $this;
    }

    public function routable()
    {
        $this->_isRoutable = true;
        return $this;
    }

    public function notRoutable()
    {
        $this->_isRoutable = false;
        return $this;
    }

    public function expectModule($module)
    {
        $this->_expectModule = $module;
        return $this;
    }

    public function expectController($controller)
    {
        $this->_expectController = $controller;
        return $this;
    }

    public function expectAction($action)
    {
        $this->_expectAction = $action;
        return $this;
    }

    public function expectArguments(array $arguments)
    {
        $this->_expectArguments = $arguments;
        return $this;
    }

    public function requireModule($module)
    {
        $this->_requiredModule = $module;
        return $this;
    }

    public function requireController($controller)
    {
        $this->_requiredController = $controller;
        return $this;
    }

    public function requireAction($action)
    {
        $this->_requiredAction = $action;
        return $this;
    }

    public function test()
    {
        $routerClass = $this->_routerClass;
        $routerInstance = new $routerClass();
        if (!isset($this->_expectModule)) {
            $this->_expectModule    = $routerInstance->getDefaultModuleName();
        }
        if (!isset($this->_expectController)) {
            $this->_expectController = $routerInstance->getDefaultControllerName();
        }
        if (!isset($this->_expectAction)) {
            $this->_expectAction    = $routerInstance->getDefaultActionName();
        }

        $postfix = "";
        if (isset($this->_requiredModule)) {
            $this->_router->requireModule($this->_requiredModule);
            if ($this->_requiredModule) {
                $postfix .= "(module '{$this->_requiredModule}' required)";
            }
            else{
                $postfix .= '(module required)';
            }
        }
        
        if (isset($this->_requiredController)) {
            $this->_router->requireController($this->_requiredController);
            if ($this->_requiredController) {
                $postfix .= "(controller '{$this->_requiredController}' required)";
            }
            else{
                $postfix .= '(controller required)';
            }
        }
        
        if (isset($this->_requiredAction)) {
            $this->_router->requireAction($this->_requiredAction);
            if ($this->_requiredAction) {
                $postfix .= "(action '{$this->_requiredAction}' required)";
            }
            else{
                $postfix .= '(action required)';
            }
        }

        $routable = $this->_router->route($this->_uri);

        if ($this->_isRoutable) {
            $this->_test->assertTrue($routable, "{$this->_routerClass} router should be able to route '{$this->_uri}' " . $postfix);
        }
        else {
            $this->_test->assertFalse($routable, "{$this->_routerClass} router should not be able to route '{$this->_uri}' " . $postfix);
            return;
        }
        $this->_test->assertEquals($this->_expectModule    , $this->_router->getModuleName()     , "{$this->_routerClass} routes {$this->_uri} to module '{$this->_expectModule}'");
        $this->_test->assertEquals($this->_expectController, $this->_router->getControllerName() , "{$this->_routerClass} routes {$this->_uri} to controller '{$this->_expectController}'");
        $this->_test->assertEquals($this->_expectAction    , $this->_router->getActionName()     , "{$this->_routerClass} routes {$this->_uri} to action '{$this->_expectAction}'");
        $this->_test->assertEquals($this->_expectArguments , $this->_router->getActionArguments(), "{$this->_routerClass} gets " . var_export($this->_expectArguments, true) . " from uri '{$this->_uri}'");
    }
}
