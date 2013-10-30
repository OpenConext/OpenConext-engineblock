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

class EngineBlock_Corto_Filter_Command_ValidateVoMembership extends EngineBlock_Corto_Filter_Command_Abstract
{
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        // In filter stage we need to take a look at the VO context
        $vo = false;
        if (isset($this->_request['__']['VoContextImplicit'])) {
            $vo = $this->_request['__']['VoContextImplicit'];
        }
        else if(isset($this->_request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX])) {
            $vo = $this->_request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX];
        }

        if (!$vo) {
            return;
        }

        $this->_adapter->setVirtualOrganisationContext($vo);

        EngineBlock_ApplicationSingleton::getLog()->debug("VO membership required: $vo");

        $isMember = $this->_validateVoMembership($vo, $this->_collabPersonId, $this->_idpMetadata['EntityId'] );

        if (!$isMember) {
            throw new EngineBlock_Corto_Exception_UserNotMember("User not a member of VO $vo");
        }

        $this->_responseAttributes[self::VO_NAME_ATTRIBUTE] = $vo;

    }

    protected function _validateVoMembership($vo, $collabPersonId, $entityId)
    {
        //here we make a call to API to determine if the VO membership is valid
        $conf = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->api->vovalidate;

        $client = new Zend_Http_Client($conf->url);
        $client->setConfig(array('timeout' => 15));
        try {
            $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json; charset=utf-8')
                    ->setAuth($conf->key, $conf->secret)
                    ->setParameterGet('vo', urlencode($vo))
                    ->setParameterGet('personId', urlencode($collabPersonId))
                    ->setParameterGet('identityProviderEntityId', urlencode($entityId))
                    ->request('GET');
            $body = $client->getLastResponse()->getBody();
            $response = json_decode($body, true);
            $result = $response['value'];
        } catch (Exception $exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                ->setUserId($collabPersonId)
                ->setIdp($entityId)
                ->setSp($this->_spMetadata['EntityId'])
                ->setDetails($exception->getTraceAsString());
            EngineBlock_ApplicationSingleton::getLog()->err("Could not connect to API for VO validation" . $exception->getMessage(), $additionalInfo);
            return false;
        }
        return $result;
    }
}