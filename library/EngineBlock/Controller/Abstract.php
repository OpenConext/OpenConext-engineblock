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

class EngineBlock_Controller_Abstract 
{
    protected $_moduleName;
    protected $_controllerName;
    protected $_viewData = array();
    protected $_noRender = false;
    protected $_rendered = false;
    protected $_view;

    public function __construct($moduleName, $controllerName)
    {
        $this->_moduleName     = $moduleName;
        $this->_controllerName = $controllerName;
    }

    public function __set($name, $value)
    {
        $this->_viewData[$name] = $value;
        return $value;
    }

    public function __get($name)
    {
        return $this->_viewData[$name];
    }

    public function hasAction($actionName)
    {
        $methodName = $this->_getMethodNameFromAction($actionName);
        return method_exists($this, $methodName);
    }

    public function handleAction($actionName, $arguments=array())
    {
        if (method_exists($this, 'init')) {
            $this->init();
        }

        $methodName = $this->_getMethodNameFromAction($actionName);

        call_user_func_array(array($this, $methodName), $arguments);

        if ($this->_noRender || $this->_rendered) {
            return;
        }

        $this->renderAction($actionName);
    }

    public function renderAction($actionName)
    {
        $output = $this->_getView()->setData($this->_viewData)->render(
            $this->_getViewScriptPath($actionName)
        );

        EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setBody($output);

        $this->_rendered = true;
    }

    protected function _getViewScriptPath($actionName)
    {
        $moduleDir = ENGINEBLOCK_FOLDER_APPLICATION . '/modules/' . ucfirst($this->_moduleName);
        $filePath = $moduleDir . '/View/' . ucfirst($this->_controllerName) . '/' . $actionName . '.phtml';
        return $filePath;
    }

    protected function _getMethodNameFromAction($actionName)
    {
        return lcfirst($actionName) . 'Action';
    }

    public function setNoRender($noRender = true)
    {
        $this->_noRender = $noRender;
        return $this;
    }

    protected function _redirectToController($controllerName)
    {
        $this->_controllerName = $controllerName;
        return $this;
    }

    // CONVENIENCE METHODS

    protected function _initAuthentication()
    {
        $helper = new Surfnet_Zend_Auth_Adapter_Saml();
        $result = $helper->authenticate();
        $this->_viewData['entityId'] = $helper->getEntityId();

        return new EngineBlock_User($result->getIdentity());
    }

    protected function _redirectToUrl($url)
    {
        EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setRedirectUrl($url);
    }

    protected function _getRequest()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
    }

    protected function _getResponse()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse();
    }
 
    /**
     * @return EngineBlock_Log
     */
    protected function _getSessionLog()
    {
        return EngineBlock_ApplicationSingleton::getLog();
    }
   
    // DEPENDENCIES

    /**
     * @return EngineBlock_View
     */
    protected function _getView()
    {
        if (!isset($this->_view)) {
            $this->_view = new EngineBlock_View();
        }
        return $this->_view;
    }

    protected function _setView($view)
    {
        $this->_view = $view;
        return $this;
    }
}
