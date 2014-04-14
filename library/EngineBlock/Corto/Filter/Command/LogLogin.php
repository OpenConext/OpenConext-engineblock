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

class EngineBlock_Corto_Filter_Command_LogLogin extends EngineBlock_Corto_Filter_Command_Abstract
{
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

    public function execute()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        $voContext = null;
        if (isset($this->_responseAttributes[self::VO_NAME_ATTRIBUTE][0])) {
            $voContext = $this->_responseAttributes[self::VO_NAME_ATTRIBUTE][0];
        }

        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin(
            $this->_spMetadata,
            $this->_idpMetadata,
            $this->_collabPersonId,
            $voContext,
            $this->_request->getKeyId()
        );
    }
}