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

class EngineBlock_Group_Provider_OpenSocial_Oauth_ThreeLegged extends EngineBlock_Group_Provider_OpenSocial_Abstract
{
    /**
     * @var EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken
     */
    protected $_accessTokenHelper;

    /**
     * @var \osapiAuth
     */
    protected $_openSocialAuth;

    /**
     * @var \osapi
     */
    protected $_openSocialClient;

    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $databaseFactory = new EngineBlock_Database_ConnectionFactory();
        $databaseAdapter = $databaseFactory->create(
            EngineBlock_Database_ConnectionFactory::MODE_READ
        );

        $auth = new Osapi_Auth_ThreeLegged(
            $config->auth->consumerKey,
            $config->auth->consumerSecret,
            new Osapi_Storage_Database($databaseAdapter),
            new Osapi_Provider_PlainRest($config->name, $config->url),
            $userId,
            $config->name
        );

        $provider = self::createFromConfigsWithAuth($config, $auth, $userId);

        $provider->_accessTokenHelper = new EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken(
            $databaseAdapter,
            $provider,
            $userId
        );

        $provider->addPrecondition('EngineBlock_Group_Provider_Precondition_OpenSocial_Oauth_AccessTokenExists');
        
        return $provider;
    }

    public function setAccessToken($accessToken)
    {
        $this->_accessTokenHelper->set($accessToken);
    }

    /**
     * @return osapiAuth
     */
    public function getOpenSocialAuth()
    {
        return $this->_openSocialAuth;
    }
}