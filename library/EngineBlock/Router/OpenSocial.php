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
 * Route all /social/ URLs to the Social module with the Rest controller
 */
class EngineBlock_Router_OpenSocial extends EngineBlock_Router_Abstract
{
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);

        if (!isset($urlParts[0]) || $urlParts[0] !== 'social') {
            return false;
        }

        $this->_moduleName      = 'Social';

        if (count($urlParts)===1) {
            $this->_controllerName = 'Index';
            $this->_actionName     = 'Index';
        }
        else {
            $this->_controllerName  = 'Rest';
            $this->_actionName      = 'Index';
            $this->_actionArguments = array(
                implode('/', array_slice($urlParts, 1))
            );
        }
        return true;
    }
}
