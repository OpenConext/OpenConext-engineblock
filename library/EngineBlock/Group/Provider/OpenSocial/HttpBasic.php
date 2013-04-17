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

class EngineBlock_Group_Provider_OpenSocial_HttpBasic 
    extends EngineBlock_Group_Provider_OpenSocial_Abstract
{
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        // @todo get client from factory via DI container instead of instantiating it here, this would make testing and logging easier
        $httpClient = new Zend_Http_Client($config->url);
        $httpClient->setAuth(
            $config->user,
            $config->password
        );

        $openSocialRestClient = new OpenSocial_Rest_Client($httpClient);
        $provider = new self($config->id, $config->name, $openSocialRestClient);

        $provider->setUserId($userId);
        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);

        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }
}
