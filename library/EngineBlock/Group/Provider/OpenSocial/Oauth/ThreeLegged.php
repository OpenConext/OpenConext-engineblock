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

    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $databaseFactory = new EngineBlock_Database_ConnectionFactory();
        $databaseAdapter = $databaseFactory->create(
            EngineBlock_Database_ConnectionFactory::MODE_READ
        );

        $accessTokenHelper = new EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken(
            $config->id,
            $databaseAdapter,
            $userId
        );

        $httpClient = new Zend_Oauth_Client($config->auth->toArray(), $config->url, $config);

        $accessToken = $accessTokenHelper->loadAccessToken();
        if ($accessToken) {
            $httpClient->setToken($accessToken);
        }

        $openSocialRestClient = new OpenSocial_Rest_Client($httpClient);

        $provider = new self(
            $config->id,
            $config->name,
            $openSocialRestClient
        );

        $provider->setUserId($userId);

        $provider->setAccessTokenHelper($accessTokenHelper);

        $provider->addPrecondition('EngineBlock_Group_Provider_Precondition_OpenSocial_Oauth_AccessTokenExists');

        $decoratedProvider = $provider->configureDecoratorChain($config);

        return $decoratedProvider;
    }

    public function setAccessTokenHelper($helper)
    {
        $this->_accessTokenHelper = $helper;
        return $this;
    }

    public function setAccessToken($accessToken)
    {
        $this->_accessTokenHelper->storeAccessToken($accessToken);
        $this->_openSocialRestClient->getHttpClient()->setToken($accessToken);
    }

    /**
     * @return osapiAuth
     */
    public function getOpenSocialRestClient()
    {
        return $this->_openSocialRestClient;
    }
}