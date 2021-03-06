<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class EngineBlock_Corto_Exception_UnknownPreselectedIdp extends EngineBlock_Exception
{
    private $_remoteIdpMd5Hash;

    public function __construct($message, $remoteIdpMd5Hash)
    {
        parent::__construct($message, self::CODE_NOTICE);
        $this->_remoteIdpMd5Hash = $remoteIdpMd5Hash;
    }

    /**
     * @return string
     */
    public function getRemoteIdpMd5Hash()
    {
        return $this->_remoteIdpMd5Hash;
    }
}
