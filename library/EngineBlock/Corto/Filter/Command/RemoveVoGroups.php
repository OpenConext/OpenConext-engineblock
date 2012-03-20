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
 * Remove any IDP set urn:collab:org:... only SURFconext is allowed to set these.
 */
class EngineBlock_Corto_Filter_Command_RemoveVoGroups extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_VO_PREFIX    = 'urn:collab:org:';
    const URN_IS_MEMBER_OF = 'urn:mace:dir:attribute-def:isMemberOf';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (!isset($this->_responseAttributes[self::URN_IS_MEMBER_OF])) {
            return;
        }

        $groups = &$this->_responseAttributes[self::URN_IS_MEMBER_OF];

        for ($i = 0; $i < count($groups); $i++) {
            $hasVoPrefix = strpos($groups[$i], self::URN_VO_PREFIX) === 0;
            if ($hasVoPrefix) {
                unset($groups[$i]);
            }
        }
    }
}