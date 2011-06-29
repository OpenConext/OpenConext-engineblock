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
class EngineBlock_Corto_Log_Adapter implements Corto_Log_Interface
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
     * @param String $message
     */
    public function debug($message)
    {
        ebLog()->debug($this->_getPrefix() . $message);
    }

    /**
     * The dummy logger ignores any call to err()
     * @param String $message
     */
    public function err($message)
    {
        ebLog()->err($this->_getPrefix() . $message);
    }

    /**
     * The dummy logger ignores any call to warn()
     * @param String $message
     */
    public function warn($message)
    {
        ebLog()->warn($this->_getPrefix() . $message);
    }

    protected function _getPrefix()
    {
        return 'CORTO' . (isset($this->_id)? '[' . $this->_id . '] ': '');
    }
}
