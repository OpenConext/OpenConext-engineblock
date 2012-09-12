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

class EngineBlock_Log_Message_AdditionalInfo
{
    protected $_userId;
    protected $_idp;
    protected $_sp;
    protected $_details;

    public static function createFromException(EngineBlock_Exception $e)
    {
        $info = new static();
        $info->_userId  = $e->userId;
        $info->_idp     = $e->idpEntityId;
        $info->_sp      = $e->spEntityId;
        $info->_details = $e->getTraceAsString();
        return $info;
    }

    public function __construct()
    {
    }

    public function setDetails($details)
    {
        $this->_details = $details;
    }

    public function getDetails()
    {
        return $this->_details;
    }

    public function setIdp($idp)
    {
        $this->_idp = $idp;
    }

    public function getIdp()
    {
        return $this->_idp;
    }

    public function setSp($sp)
    {
        $this->_sp = $sp;
    }

    public function getSp()
    {
        return $this->_sp;
    }

    public function setUserId($userId)
    {
        $this->_userId = $userId;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function toArray()
    {
        $array = array();
        $array['userId']    = $this->_userId;
        $array['idp']       = $this->_idp;
        $array['sp']        = $this->_sp;
        $array['details']   = $this->_details;
        return $array;
    }
}