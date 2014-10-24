<?php

use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;

class Profile_Controller_Index extends Default_Controller_LoggedIn
{
    public function indexAction()
    {
        $this->userAttributes = $this->_normalizeAttributes();

        $this->metadata = new EngineBlock_Attributes_Metadata();

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
     * @param array $spList all service providers
     * @return array with service providers Id's with the ARP
     */
    protected function _getSpAttributeList(array $spList)
    {
        $attributes = $this->_normalizeAttributes();

        $results = array();
        $serviceRegistryClient = $this->_getServiceRegistryClient();
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        foreach ($spList as $spId => $sp) {
            $arp = $serviceRegistryClient->getArp($spId);
            $results[$spId] = $enforcer->enforceArp(new AttributeReleasePolicy($arp), $attributes);
        }

        return $results;
    }

    /**
     * Return the cleansed attributes
     */
    protected function _normalizeAttributes()
    {
        $normalizer = new EngineBlock_Attributes_Normalizer($this->attributes);
        $normalizedAttributes = $normalizer->normalize();
        unset($normalizedAttributes['nameid']);
        return $normalizedAttributes;
    }

    /**
     * @return Janus_Client
     */
    protected function _getServiceRegistryClient()
    {
        $serviceRegistryClient = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryClient();
        return $serviceRegistryClient;
    }
}