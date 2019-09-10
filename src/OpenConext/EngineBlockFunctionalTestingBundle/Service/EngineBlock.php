<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockFunctionalTestingBundle\Service;

/**
 * Class EngineBlock
 * @SuppressWarnings("PMD")
 */
class EngineBlock
{
    const IDP_METADATA_PATH         = '/authentication/idp/metadata';
    const SP_METADATA_PATH          = '/authentication/sp/metadata';
    const SINGLE_SIGN_ON_PATH       = '/authentication/idp/single-sign-on';
    const ASSERTION_CONSUMER_PATH   = '/authentication/sp/consume-assertion';
    const UNSOLICITED_SSO_START_PATH = '/authentication/idp/unsolicited-single-sign-on/%s';
    const LOGOUT                    = '/logout';

    protected $baseUrl;

    /**
     * @param $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function idpEntityId()
    {
        return $this->baseUrl . self::IDP_METADATA_PATH;
    }

    public function transparentSsoLocation($idpEntityId)
    {
        return $this->singleSignOnLocation() . '/' . md5($idpEntityId);
    }

    public function voSsoLocation($voId)
    {
        return $this->singleSignOnLocation() . '/vo:' . $voId;
    }

    public function singleSignOnLocation()
    {
        return $this->baseUrl . self::SINGLE_SIGN_ON_PATH;
    }

    public function unsolicitedLocation($identityProviderEntityId, $serviceProviderEntityId, $keyId = false)
    {
        $keyParameter = '';
        if ($keyId) {
            $keyParameter = sprintf('key:%s/', $keyId);
        }
        return $this->baseUrl
               . sprintf(self::UNSOLICITED_SSO_START_PATH, $keyParameter . md5($identityProviderEntityId))
               . '?sp-entity-id=' . urlencode($serviceProviderEntityId);
    }

    public function unsolicitedLocationInvalidParam($identityProviderEntityId, $serviceProviderEntityId)
    {
        return $this->baseUrl
               . sprintf(self::UNSOLICITED_SSO_START_PATH, md5($identityProviderEntityId))
               . '?wrong-parameter=' . urlencode($serviceProviderEntityId);
    }

    public function spEntityId()
    {
        return $this->baseUrl . self::SP_METADATA_PATH;
    }

    public function assertionConsumerLocation()
    {
        return $this->baseUrl . self::ASSERTION_CONSUMER_PATH;
    }

    public function logoutLocation()
    {
        return $this->baseUrl . self::LOGOUT;
    }
}
