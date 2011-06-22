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

abstract class EngineBlock_Group_Provider_Decorator_Abstract
    implements EngineBlock_Group_Provider_Decorator_Interface
{
    /**
     * Factory method to create and configure a group provider from it's given configuration
     *
     * @static
     * @abstract
     * @param Zend_Config $config Configuration for this provider in it's specific format
     * @param string      $userId UserId to provide group information for (required)
     * @return EngineBlock_Group_Provider_Interface
     */
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        throw new EngineBlock_Exception("createFromConfigs not possible for decorator, need access to provider");
    }
}