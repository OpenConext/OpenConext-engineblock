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

class Profile_Controller_Index extends Default_Controller_LoggedIn
{
    public function indexAction()
    {
        $this->userAttributes = $this->_getClensedAttributes();

        $this->metadata = new EngineBlock_Attributes_Metadata();
        $this->aggregator = EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy::createFromDatabaseFor(
            $this->attributes['nameid'][0]
        );

        $serviceRegistryClient = $this->_getServiceRegistryClient();
        $this->spList = $serviceRegistryClient->getSpList();

        $this->consent = $this->user->getConsent();
        $this->spAttributesList = $this->_getSpAttributeList($this->spList);

        $this->mailSend = isset($_GET["mailSend"]) ? $_GET["mailSend"] : null;

    }

    /**
     * Returns an array with attributes that are released to each SP.
     *
     * We check if there is an ARP and then return this otherwise all attributes we have gotten.
     *
     * @param $spList all service providers
     * @return array with service providers Id's with the ARP
     */
    protected function _getSpAttributeList($spList)
    {
        $serviceRegistryClient = $this->_getServiceRegistryClient();
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        $attributes = $this->_getClensedAttributes();

        $results = array();
        foreach ($spList as $spId => $sp) {
            $arp = $serviceRegistryClient->getArp($spId);
            $results[$spId] = $enforcer->enforceArp($arp, $attributes);
        }

        return $results;
    }

    /**
     * Return the clensed attributes
     */
    protected function _getClensedAttributes()
    {
        $normalizer = new EngineBlock_Attributes_Normalizer($this->attributes);
        $normalizedAttributes = $normalizer->normalize();
        unset($normalizedAttributes['nameid']);
        return $normalizedAttributes;
    }

    /**
     * @return Janus_Client_CacheProxy
     */
    protected function _getServiceRegistryClient()
    {
        $serviceRegistryClient = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryClient();
        return $serviceRegistryClient;
    }
}