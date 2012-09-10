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
 * Adapter to make Corto use EngineBlock Logging
 */
class EngineBlock_Corto_Log_Adapter implements EngineBlock_Corto_Log_Interface
{
    protected $_id = "";

    /**
     * The dummy logger ignores any call to setId
     * @param String $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * The dummy logger ignores any call to debug()
     * @param EngineBlock_Log_Message_AdditionalInfo $additionalInfo Some extra information
     * that can be supplied with the log message
     * @param String $message
     */
    public function debug($message, EngineBlock_Log_Message_AdditionalInfo $additionalInfo = null)
    {
        EngineBlock_ApplicationSingleton::getLog()->debug($this->_getPrefix() . $message, $additionalInfo);
    }

    /**
     * The dummy logger ignores any call to err()
     * @param EngineBlock_Log_Message_AdditionalInfo $additionalInfo Some extra information
     * that can be supplied with the log message
     * @param String $message
     */
    public function err($message, EngineBlock_Log_Message_AdditionalInfo $additionalInfo = null)
    {
        EngineBlock_ApplicationSingleton::getLog()->err($this->_getPrefix() . $message, $additionalInfo);
    }

    /**
     * The dummy logger ignores any call to warn()
     * @param EngineBlock_Log_Message_AdditionalInfo $additionalInfo Some extra information
     * that can be supplied with the log message
     * @param String $message
     */
    public function warn($message, EngineBlock_Log_Message_AdditionalInfo $additionalInfo = null)
    {
        EngineBlock_ApplicationSingleton::getLog()->warn($this->_getPrefix() . $message, $additionalInfo);
    }

    protected function _getPrefix()
    {
        return 'CORTO' . (isset($this->_id)? '[' . $this->_id . '] ': '');
    }
}
