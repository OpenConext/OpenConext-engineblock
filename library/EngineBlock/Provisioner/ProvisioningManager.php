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

class EngineBlock_Provisioner_ProvisioningManager
{
    protected $_url;

    public function __construct(Zend_Config $config)
    {
        $this->_url = $config->url;
    }

    /**
     * 
     *
     * @param  $userId
     * @param  $attributes
     * @param  $spMetadata
     * @param  $idpMetadata
     * @return void
     */
    public function provisionUser($userId, $attributes, $spMetadata, $idpMetadata)
    {
        if (!$spMetadata['MustProvisionExternally']) {
            return false;
        }

        // https://os.XXX.surfconext.nl/provisioning-manager/provisioning/jit.shtml?
        // provisionDomain=apps.surfnet.nl&provisionAdmin=admin%40apps.surfnet.nl&
        // provisionPassword=xxxxx&provisionType=GOOGLE
        
        $client = new Zend_Http_Client($this->_url);
        $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json; charset=utf-8')

            ->setParameterGet('provisionType'       , $spMetadata['ExternalProvisionType'])
            ->setParameterGet('provisionDomain'     , $spMetadata['ExternalProvisionDomain'])
            ->setParameterGet('provisionAdmin'      , $spMetadata['ExternalProvisionAdmin'])
            ->setParameterGet('provisionPassword'   , $spMetadata['ExternalProvisionPassword'])

            ->setRawData(json_encode($this->_getData($userId, $attributes)))

            ->request('POST');

        ebLog()->debug("PROVISIONING: Sent HTTP request to provision user using " . __CLASS__);
        ebLog()->debug("PROVISIONING: URI: " . $client->getUri(true));
        ebLog()->debug("PROVISIONING: REQUEST: " . $client->getLastRequest());
        ebLog()->debug("PROVISIONING: RESPONSE: " . $client->getLastResponse());
    }

    /**
     * The external provisioning tool requires the following data:
     * {"person":
     *    {"name":"provisioning",
     *     "surName":"manager",
     *     "id":"urn:collab:person:surfnet.nl:test",
     *     "emails":["test1@surfnet.nl","test2@surfnet.nl"],
     *     "uid":"provisioning.manager"},
     *  "groups":
     *    [
     *      {"id":"nl:surfnet:diensten:test1",
     *       "description":"Group for test1",
     *       "title":"ProvTest1"},
     *      {"id":"nl:surfnet:diensten:test2",
     *       "description":"Group for test2",
     *       "title":"ProvTest2"},
     *      {"id":"nl:surfnet:diensten:test3",
     *       "description":"Group for test3",
     *       "title":"ProvTest3"}
     *     ]
     *  }
     * 
     * @param  $userId
     * @param  $attributes
     * @return array
     */
    protected function _getData($userId, $attributes)
    {
        
        $groups = $this->_getGroups($userId);
        $provisionData = array(
            'person' => array(
                'id'            => $userId,
                'uid'           => $attributes['urn:mace:dir:attribute-def:uid'][0],
                'name'          => $attributes['urn:mace:dir:attribute-def:givenName'][0],
                'surName'       => $attributes['urn:mace:dir:attribute-def:sn'][0],
                'organization'  => $attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0],
                'emails'        => $attributes['urn:mace:dir:attribute-def:mail'],
            ),
            'groups' => $groups,
        );
        return $provisionData;
    }

    protected function _getGroups($userId)
    {
        $groupProvider = EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy::createFromDatabaseFor($userId);
        return $groupProvider->getGroups();
    }

}