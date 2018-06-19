<?php

class EngineBlock_Corto_Filter_Command_AttributeReleasePolicy extends EngineBlock_Corto_Filter_Command_Abstract implements
    EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface,
    EngineBlock_Corto_Filter_Command_ResponseAttributeValueTypesModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function getResponseAttributeValueTypes()
    {
        return $this->_responseAttributeValueTypes;
    }

    public function execute()
    {
        $logger = EngineBlock_ApplicationSingleton::getLog();
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        $attributes = $this->_responseAttributes;

        // Get the Requester chain, which starts at the oldest (farthest away from us SP) and ends with our next hop.
        $requesterChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );
        // Note that though we should traverse in reverse ordering, it doesn't make a difference.
        // A then B filter or B then A filter are equivalent.
        foreach ($requesterChain as $spMetadata) {
            $spEntityId = $spMetadata->entityId;

            $arp = $this->getMetadataRepository()->fetchServiceProviderArp($spMetadata);

            if (!$arp) {
                continue;
            }

            $logger->info("Applying attribute release policy for $spEntityId");
            $attributes = $enforcer->enforceArp($arp, $attributes);

            $this->_responseAttributeValueTypes = $enforcer->updateAttributeValueTypes(
                $attributes,
                $this->_responseAttributeValueTypes
            );
        }

        $this->_responseAttributes = $attributes;
    }

    protected function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
