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

    public function isMember($subjectId, array $groups)
    {
        if (empty($groups)) {
            return false;
        }

        //here we make a call to API to determine if the VO membership is valid
        $conf = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->api->vovalidate;

        $url = $this->_getVoValidationUrl($conf->url, $subjectId, $groups);

        $client = new Zend_Http_Client($url);
        $client->setConfig(array('timeout' => 15));

        try {
            $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json; charset=utf-8')
                ->setAuth($conf->key, $conf->secret)
                ->request('GET');
            $body = $client->getLastResponse()->getBody();
            $response = json_decode($body, true);
            return $response['ismember'];
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($subjectId)
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->err("Could not connect to API for VO validation" . $exception->getMessage(), $additionalInfo);
            return false;
        }
    }


    protected function _getVoValidationUrl($configuredUrl, $subjectId, array $groups)
    {
        // api/member/{uid:.+}?groups=<comma-separated-group-ids>
        $slash = '/';
        $configuredUrl = (substr($configuredUrl, -strlen($slash)) === $slash) ? $configuredUrl : $configuredUrl . $slash;
        $configuredUrl .= urlencode($subjectId) . '?groups=';
        $configuredUrl .= implode(',', $groups);
        return $configuredUrl;
    }

}