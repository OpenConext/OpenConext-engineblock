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
        $spEntityId = $this->_spMetadata['EntityID'];

        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        $arp = $serviceRegistryAdapter->getArp($spEntityId);
        if ($arp) {
            EngineBlock_ApplicationSingleton::getLog()->info(
                "Applying attribute release policy {$arp['name']} for $spEntityId"
            );
            $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
            $newAttributes = $enforcer->enforceArp($arp, $this->_responseAttributes);
            $this->_responseAttributes = $newAttributes;
        }
    }

    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }
}