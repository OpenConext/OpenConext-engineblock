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

class EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken
{
    const STORAGE_KEY_TEMPLATE = 'OAuth:[[ConsumerKey]]:[[UserId]]:[[ProviderName]]';

    protected $_storageKey;

    protected $_connection;
    protected $_provider;
    protected $_storage;
    protected $_userId;

    public function __construct(PDO $databaseConnection,
        EngineBlock_Group_Provider_OpenSocial_Oauth_ThreeLegged $provider,
        $userId
    )
    {
        $this->_connection = $databaseConnection;
        $this->_provider   = $provider;
        $this->_storage    = $this->_getStorage();
        $this->_userId     = $userId;
    }

    protected function _getStorage()
    {
        return new Osapi_Storage_Database($this->_connection);
    }

    public function get()
    {
        return $this->_storage->get($this->_getStorageKey());
    }

    public function set($accessToken)
    {
        return $this->_storage->set($this->_getStorageKey(), $accessToken);
    }

    public function delete()
    {
        return $this->_storage->delete($this->_getStorageKey());
    }

    protected function _getStorageKey()
    {
        if (isset($this->_storageKey)) {
            return $this->_storageKey;
        }

        /**
         * @var Osapi_Auth_ThreeLegged $openSocialAuth
         */
        $openSocialAuth = $this->_provider->getOpenSocialAuth();
        $storageKey = self::STORAGE_KEY_TEMPLATE;
        $storageKey = str_replace('[[ConsumerKey]]' , $openSocialAuth->getConsumerKey() , $storageKey);
        $storageKey = str_replace('[[UserId]]'      , $this->_userId                    , $storageKey);
        $storageKey = str_replace('[[ProviderName]]', $this->_provider->getDisplayName(), $storageKey);
        $this->_storageKey = $storageKey;

        return $this->_storageKey;
    }
}