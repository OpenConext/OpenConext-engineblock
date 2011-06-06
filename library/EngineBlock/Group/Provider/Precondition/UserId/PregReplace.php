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
 *
 */
class EngineBlock_Group_Provider_Precondition_UserId_PregReplace
{
    protected $_provider;
    protected $_search;

    public function __construct(EngineBlock_Group_Provider_Interface $provider, Zend_Config $options)
    {
        $this->_provider = $provider;
        $this->_search   = $options->search;
        $this->_replace  = $options->replace;
    }

    public function validate()
    {
        $oldUserId = $this->_provider->getUserId();
        if (!$oldUserId) {
            return false;
        }

        $newUserId = preg_replace($this->_search, $this->_replace, $oldUserId);
        if (!$newUserId || $newUserId === $oldUserId) {
            return false;
        }

        $this->_provider->setUserId($newUserId);

        return true;
    }
}