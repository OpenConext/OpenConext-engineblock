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

abstract class EngineBlock_Router_Abstract implements EngineBlock_Router_Interface
{
    protected $_controllerName;
    protected $_moduleName;
    protected $_actionName;
    protected $_actionArguments = array();

    public function getControllerName()
    {
        return $this->_controllerName;
    }

    public function getModuleName()
    {
        return $this->_moduleName;
    }

    public function getActionName()
    {
        return $this->_actionName;
    }

    public function getActionArguments()
    {
        return $this->_actionArguments;
    }

    protected function setActionArguments($arguments)
    {
        $decodedArguments = array();
        foreach ($arguments as $argument) {
            $decodedArguments[] = rawurldecode($argument);
        }

        $this->_actionArguments = $decodedArguments;
        return $this;
    }

    /**
     * Convert a-hyphenated-string to AHyphenatedString
     *
     * @param string $name
     * @return string
     */
    protected function _convertHyphenatedToCamelCase($name)
    {
        return implode(array_map('ucfirst', explode('-', $name)));
    }
}
