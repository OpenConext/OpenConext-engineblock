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

/**
 * Default router, expects a format like /module/controller/action and routes it.
 */
class EngineBlock_Router_Default extends EngineBlock_Router_Abstract
{
    protected $_defaultModuleName     = "Default";
    protected $_defaultControllerName = "Index";
    protected $_defaultActionName     = "Index";
    
    protected $_requiredModule;
    protected $_requiredController;
    protected $_requiredAction;

    /**
     * Note that this router interprets ////tekno as /tekno, NOT as /default/index/index/tekno
     *
     * @param  $uri
     * @return bool
     */
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);
        $urlPartsCount = count($urlParts);

        $module     = $this->_defaultModuleName;
        $controller = $this->_defaultControllerName;
        $action     = $this->_defaultActionName;

        $arguments  = array();

        // Note how we actually use the fall-through
        switch($urlPartsCount)
        {
            default: // More than 3 parts
                // /module/controller/action/arg1/arg2/etc
                $arguments = array_slice($urlParts, 3);

            case 3:
                // /module/controller/action
                if ($urlParts[2]) {
                    $action     = $this->_convertHyphenatedToCamelCase($urlParts[2]);
                }

            case 2:
                // /module/controller => /module/controller/index
                if ($urlParts[1]) {
                    $controller = $this->_convertHyphenatedToCamelCase($urlParts[1]);
                }

            case 1:
                // /module => /module/index/index
                if ($urlParts[0]) {
                    $module     = $this->_convertHyphenatedToCamelCase($urlParts[0]);
                }

            case 0:
                break;
        }

        if (!$module || !$controller || !$action) {
            return false;
        }

        if ($this->_requiredModule && $module !== $this->_requiredModule) {
            return false;
        }

        if ($this->_requiredController && $controller !== $this->_requiredController) {
            return false;
        }

        if ($this->_requiredAction && $action !== $this->_requiredAction) {
            return false;
        }

        $this->_moduleName      = $module;
        $this->_controllerName  = $controller;
        $this->_actionName      = $action;
 
        $this->setActionArguments($arguments);

        return true;
    }
    
    public function requireModule($moduleName = "") 
    {
        $this->_requiredModule = $moduleName;
        return $this;
    }
    
    public function requireController($controllerName = "")
    {
        $this->_requiredController = $controllerName;
        return $this;
    }
    
    public function requireAction($actionName = "")
    {
        $this->_requiredAction = $actionName;
        return $this;
    }

    /**
     * @static
     * @return EngineBlock_Router_Default
     */
    public static function create()
    {
        return new static();
    }

    public function setDefaultActionName($defaultActionName)
    {
        $this->_defaultActionName = $defaultActionName;
        return $this;
    }

    public function setDefaultControllerName($defaultControllerName)
    {
        $this->_defaultControllerName = $defaultControllerName;
        return $this;
    }

    public function setDefaultModuleName($defaultModuleName)
    {
        $this->_defaultModuleName = $defaultModuleName;
        return $this;
    }

    public function getDefaultActionName()
    {
        return $this->_defaultActionName;
    }

    public function getDefaultControllerName()
    {
        return $this->_defaultControllerName;
    }

    public function getDefaultModuleName()
    {
        return $this->_defaultModuleName;
    }
}
