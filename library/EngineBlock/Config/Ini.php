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

class EngineBlock_Config_Ini extends Zend_Config_Ini
{
    /**
     * Load the INI file from disk using parse_ini_file(). Use a private error
     * handler to convert any loading errors into a Zend_Config_Exception
     *
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _parseIniFile($filename)
    {
        set_error_handler(array($this, '_loadFileErrorHandler'));
        if (substr($filename, -4) == '.ini') {
            $iniArray = parse_ini_file($filename, true);
        } else {
            $iniArray = parse_ini_string($filename, true);
        }
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            /**
             * @see Zend_Config_Exception
             */
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception($this->_loadFileErrorStr);
        }

        return $iniArray;
    }
}