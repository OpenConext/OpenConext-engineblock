<?php

class EngineBlock_Corto_Filter_Command_AttributeReleasePolicy extends EngineBlock_Corto_Filter_Command_Abstract
{
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
        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        $logger = EngineBlock_ApplicationSingleton::getLog();
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        $attributes = $this->_responseAttributes;

        // Get the Requester chain, which starts at the oldest (farthest away from us SP) and ends with our next hop.
        $requesterChain = EngineBlock_SamlHelper::getSpRequesterChain($this->_spMetadata, $this->_request, $this->_server);
        // Note that though we should traverse in reverse ordering, it doesn't make a difference.
        // A then B filter or B then A filter are equivalent.
        foreach ($requesterChain as $spMetadata) {
            $spEntityId = $spMetadata['EntityID'];

            $arp = $serviceRegistryAdapter->getArp($spEntityId);

            if (!$arp) {
                continue;
            }

            $logger->info("Applying attribute release policy {$arp['name']} for $spEntityId");
            $attributes = $enforcer->enforceArp($arp, $attributes);
        }

        $this->_responseAttributes = $attributes;
    }

    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }
}