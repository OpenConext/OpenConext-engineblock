<?php

class EngineBlock_VirtualOrganization_GroupValidator
{
    const ACCESS_TOKEN_KEY = "EngineBlock_VirtualOrganization_GroupValidator_Access_Token_Key";

    public function isMember($subjectId, array $groups)
    {
        return $this->_validateGroupMembership($subjectId, $groups);
    }

    protected function _validateGroupMembership($subjectId, array $groups, $requireNew = false)
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
            if ($response->getStatus() === 200) {
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
            } else if (!$requireNew && $response->getStatus() === 400) {
                EngineBlock_ApplicationSingleton::getLog()->info(
                    "Possible expired accessToken $accessToken. Trying to obtain new accessToken"
                );
                return $this->_validateGroupMembership($subjectId, $groups, true);
            }
            else {
                EngineBlock_ApplicationSingleton::getLog()->error(
                    'Received non-200 response from API trying to get the group memberships',
                    array('http_response' => $response->getHeadersAsString() . PHP_EOL . $response->getBody())
                );
                throw new EngineBlock_Exception(
                    'Non-200 from API trying to get the group memberships'
                );
            }
            return false;
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($subjectId)
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->error(
                "Could not connect to API for VO validation" . $exception->getMessage(),
                array('additional_info' => $additionalInfo->toArray())
            );
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
            EngineBlock_ApplicationSingleton::getLog()->error(
                "Error in connecting to API(s) for access token grant" . $exception->getMessage(),
                array('additional_info' => $additionalInfo->toArray())
            );
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
