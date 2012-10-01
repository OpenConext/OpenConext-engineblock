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

class EngineBlock_LicenseEngine_ValidationManager
{
    const LICENSE_SAML_ATTRIBUTE = 'urn:nl.surfconext.licenseInfo';
    const LICENSE_UNKNOWN = 'LICENSE_UNKNOWN';
    const LICENSE_OK = 'LICENSE_OK';
    const LICENSE_NOT_OK = 'LICENSE_NOT_OK';

    protected $_url;
    protected $_active;

    public function __construct(Zend_Config $config)
    {
        if (!isset($config->licenseEngine)) {
            $this->_active = false;
        }
        else {
            $this->_url = $config->licenseEngine->url;
            $this->_active = $config->licenseEngine->active;
        }
    }

    /**
     * Validate the license information
     *
     * @param string $userId
     * @param array $spMetadata
     * @param array $idpMetadata
     * @return string
     */
    public function validate($userId, array $spMetadata, array $idpMetadata)
    {
        if (!$this->_active) {
            return EngineBlock_LicenseEngine_ValidationManager::LICENSE_UNKNOWN;
        }

        $client = new Zend_Http_Client($this->_url);
        $client->setConfig(array('timeout' => 15));
        try {
            $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json; charset=utf-8')

                    ->setParameterGet('userId', urlencode($userId))
                    ->setParameterGet('serviceProviderEntityId', urlencode($spMetadata['EntityId']))
                    ->setParameterGet('identityProviderEntityId', urlencode($idpMetadata['EntityId']))
                    ->request('GET');
            $body = $client->getLastResponse()->getBody();
            $response = json_decode($body, true);
            $status = $response['status'];
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($userId)
                ->setIdp($idpMetadata['EntityId'])
                ->setSp($spMetadata['EntityId'])
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->err("Could not connect to License Manager" . $exception->getMessage(), $additionalInfo);
            return EngineBlock_LicenseEngine_ValidationManager::LICENSE_UNKNOWN;
        }
        if ($status['returnUrl']) {
            $currentResponse = EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse();
            $currentResponse->setRedirectUrl($status['returnUrl']);
            $currentResponse->send();
            exit;

        } else if ($status['licenseStatus']) {
            return $status['licenseStatus'];
        }
        else {
            return EngineBlock_LicenseEngine_ValidationManager::LICENSE_UNKNOWN;
        }
    }

}
