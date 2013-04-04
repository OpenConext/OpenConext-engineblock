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

class EngineBlock_Dispatcher
{
    protected $_routers = array();
    protected $_useErrorHandling = true;

    public function __construct()
    {
        \Lvl\Profiler::getInstance()->startBlock('dispatch');

        $this->_addDefaultRouter();
    }

    protected function _addDefaultRouter()
    {
        $this->_routers[] = new EngineBlock_Router_Default();
    }

    public function getRouters()
    {
        return $this->_routers;
    }

    public function setRouters($routers)
    {
        $this->_routers = $routers;
        return $this;
    }

    public function setUseErrorHandling($bool)
    {
        $this->_useErrorHandling = $bool;
        return $this;
    }

    public function dispatch($uri = "")
    {
        try {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            if (!$uri) {
                $uri = $application->getHttpRequest()->getUri();
            }

            if (!$this->_dispatch($uri)) {
                EngineBlock_ApplicationSingleton::getLog()->notice("[404]Unroutable URI: '$uri'");
                $this->_getControllerInstance('default', 'error')->handleAction('NotFound');
            }
        } catch(Exception $e) {
            $this->_handleDispatchException($e);
        }
    }

    protected function _dispatch($uri)
    {
        $router = $this->_getFirstRoutableRouterFor($uri);

        $module             = $router->getModuleName();
        $controllerName     = $router->getControllerName();
        $action             = $router->getActionName();
        $attributeArguments = $router->getActionArguments();

        $controllerInstance = $this->_getControllerInstance($module, $controllerName);

        if (!$controllerInstance || !$controllerInstance->hasAction($action)) {
            return false;
        }

        $controllerInstance->handleAction($action, $attributeArguments);
        return true;
    }

    protected function _handleDispatchException(Exception $e)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        if ($e instanceof EngineBlock_Exception) {
            $application->reportError($e);
        }
        else {
            $application->reportError(
                new EngineBlock_Exception($e->getMessage(), EngineBlock_Exception::CODE_ERROR, $e)
            );
        }

        if (!$this->_useErrorHandling) {
            throw $e;
        }
        else {
            $errorConfiguration = $application->getConfiguration()->error;
            $module         = $errorConfiguration->module;
            $controllerName = $errorConfiguration->controller;
            $action         = $errorConfiguration->action;

            $controllerInstance = $this->_getControllerInstance($module, $controllerName);
            $controllerInstance->handleAction($action, array($e));
        }
    }

    /**
     * @param $module
     * @param $controllerName
     * @return EngineBlock_Controller_Abstract|bool
     * @throws EngineBlock_Exception
     */
    protected function _getControllerInstance($module, $controllerName)
    {
        $className = $this->_getControllerClassName($module, $controllerName);
        if (!class_exists($className)) {
            return false;
        }

        $controllerInstance = new $className($module, $controllerName);

        if (!($controllerInstance instanceof EngineBlock_Controller_Abstract)) {
            throw new EngineBlock_Exception(
                "Controller $className is not an EngineBlock controller (does not extend EngineBlock_Controller_Abstract)!",
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        return $controllerInstance;
    }

    /**
     * Returns the first router that is capable of routing the given URI
     *
     * @param  string $uri
     * @return EngineBlock_Router_Abstract
     */
    protected function _getFirstRoutableRouterFor($uri)
    {
        /**
         * @var EngineBlock_Router_Interface $router
         */
        foreach ($this->_routers as $router) {
            $routable = $router->route($uri);
            if ($routable) {
                break;
            }
        }
        return $router;
    }

    protected function _getControllerClassName($module, $controller)
    {
        return ucfirst($module) . '_Controller_' . ucfirst($controller);
    }
}
