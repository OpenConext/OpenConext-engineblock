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

    public function handleAction($actionName, $arguments)
    {
        $methodName = $this->_getMethodNameFromAction($actionName);

        call_user_method_array($methodName, $this, $arguments);

        if ($this->_noRender) {
            return;
        }

        $renderedView = $this->_renderView($actionName);
        EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setBody($renderedView);
    }

    protected function _getMethodNameFromAction($actionName)
    {
        return $this->_convertDashedToCamelCase($actionName) . 'Action';
    }

    protected function _convertDashedToCamelCase($string)
    {
        $parts = explode('-', $string);
        $ret = array_shift($parts);

        foreach ($parts as $part)
        {
            $ret .= ucfirst($part);
        }
        return $ret;
    }

    protected function _getMethodNameFromActionName($actionName)
    {
        return $actionName;
    }

    public function setNoRender($noRender = true)
    {
        $this->_noRender = $noRender;
        return $this;
    }

    protected function _renderView($actionName)
    {
        $moduleDir = dirname(__FILE__) . '/../../../application/modules/';
        $filePath = $moduleDir . ucfirst($this->_moduleName) . '/View/';
        $filePath .= ucfirst($this->_controllerName) . '/' . ucfirst($this->_convertDashedToCamelCase($actionName)) . '.phtml';

        if (!file_exists($filePath)) {
            // @todo error out!
            throw new EngineBlock_Exception("View $filePath does not exist");
        }

        ob_start();
        
        extract($this->_viewData);
        require $filePath;

        $renderedView = ob_get_contents();
        ob_end_clean();

        return $renderedView;
    }

    protected function _initAuthentication()
    {
       return EngineBlock_Authenticator::authenticate();
    }

    protected function _redirectToUrl($url)
    {
        EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setRedirectUrl($url);
    }
}
