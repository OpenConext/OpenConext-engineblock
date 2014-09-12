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
        $arp = $this->getMetadataRepository()->fetchServiceProviderArp($this->_serviceProvider);
        if (!$arp) {
            return;
        }

        $spEntityId = $this->_serviceProvider->entityId;
        EngineBlock_ApplicationSingleton::getLog()->info(
            "Applying attribute release policy for $spEntityId"
        );
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        $newAttributes = $enforcer->enforceArp($arp, $this->_responseAttributes);

        $this->_responseAttributes = $newAttributes;
    }

    protected function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}