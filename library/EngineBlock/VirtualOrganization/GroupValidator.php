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

class EngineBlock_VirtualOrganization_GroupValidator
{

    const ACCESS_TOKEN_KEY = "EngineBlock_VirtualOrganization_GroupValidator_Access_Token_Key";

    public function isMember($subjectId, array $groups)
    {
        return $this->_validateGroupMembership($subjectId, $groups, false);
    }

    protected function _validateGroupMembership($subjectId, array $groups, $requireNew)
    {
        //here we make a call to API to determine if the VO membership is valid
        $conf = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->api->vovalidate;

        $url = $this->_getVoValidationUrl($conf->baseUrl, $subjectId);

        $client = new Zend_Http_Client($url);
        $client->setConfig(array('timeout' => 15));
        $accessToken = $this->_getAccessToken($conf, $subjectId, $requireNew);

        try {
            $response = $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json; charset=utf-8')
                ->setHeaders('Authorization', 'Bearer ' . $accessToken)
                ->request('GET');
            if ($response->getStatus() == 200) {
                $result = json_decode($response->getBody(), true);
                if (isset($result['entry'])) {
                    $memberShips = array();
                    foreach ($result['entry'] as $group) {
                        $memberShips[] = $group['id'];
                        if (in_array($group['id'], $groups)) {
                            return true;
                        }
                    }
                    EngineBlock_ApplicationSingleton::getLog()->info(
                        "No valid group membership for $subjectId (" . implode(',', $groups) . "). Group memberships returned: " . implode(',', $memberShips)
                    );
                }
            } else if (!$requireNew) {
                EngineBlock_ApplicationSingleton::getLog()->info(
                    "Possible expired accessToken $accessToken. Trying to obtain new accessToken"
                );
                return $this->_validateGroupMembership($subjectId, $groups, true);
            }
            return false;
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($subjectId)
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->err("Could not connect to API for VO validation" . $exception->getMessage(), $additionalInfo);
            return false;
        }
    }

    protected function _getVoValidationUrl($baseUrl, $subjectId)
    {
        // For example https://api.dev.surfconext.nl/v1/social/rest/groups/urn
        $baseUrl = $this->_ensureTrailingSlash($baseUrl);
        $baseUrl .= 'v1/social/rest/groups/';
        $baseUrl .= $subjectId;
        return $baseUrl;
    }

    protected function _getAccessToken($conf, $subjectId, $requireNew)
    {

        $cache = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getApplicationCache();
        if (!$requireNew && $cache instanceof Zend_Cache_Backend_Apc) {
            $accessToken = $cache->load(self::ACCESS_TOKEN_KEY);
            if ($accessToken) {
                return $accessToken;
            }
        }
        // for example https://api.dev.surfconext.nl/v1/oauth2/token
        $baseUrl = $this->_ensureTrailingSlash($conf->baseUrl) . 'v1/oauth2/token';
        $client = new Zend_Http_Client($baseUrl);
        try {
            $response = $client->setConfig(array('timeout' => 15))
                ->setHeaders(Zend_Http_Client::CONTENT_TYPE, Zend_Http_Client::ENC_URLENCODED)
                ->setAuth($conf->key, $conf->secret)
                ->setParameterPost('grant_type', 'client_credentials')
                ->request(Zend_Http_Client::POST);
            $result = json_decode($response->getBody(), true);

            if (isset($result['access_token'])) {
                $accessToken = $result['access_token'];
                if ($cache instanceof Zend_Cache_Backend_Apc) {
                    $cache->save($accessToken, self::ACCESS_TOKEN_KEY);
                }
                return $accessToken;
            }
            throw new EngineBlock_VirtualOrganization_AccessTokenNotGrantedException(
                'AccessToken not granted for EB as SP. Check SR and the Group Provider endpoint log.'
            );
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($subjectId)
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->err("Error in connecting to API(s) for access token grant" . $exception->getMessage(), $additionalInfo);
            throw new EngineBlock_VirtualOrganization_AccessTokenNotGrantedException(
                'AccessToken not granted for EB as SP. Check SR and the Group Provider endpoint log',
                EngineBlock_Exception::CODE_ALERT,
                $exception
            );
        }
    }

    protected function _ensureTrailingSlash($configuredUrl)
    {
        $slash = '/';
        $configuredUrl = (substr($configuredUrl, -strlen($slash)) === $slash) ? $configuredUrl : $configuredUrl . $slash;
        return $configuredUrl;
    }

}