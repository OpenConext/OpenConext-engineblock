<?php

class EngineBlock_Corto_Filter_Command_AddEduPersonTargettedId extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    /**
     * Resolve the eduPersonTargetedId we should send.
     */
    public function execute()
    {
        // Note that we try to service the final destination SP, if we know them and are allowed to do so.
        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        // Resolve what NameID we should send the destination.
        $resolver = new EngineBlock_Saml2_NameIdResolver();
        $nameId = $resolver->resolve(
            $this->_request,
            $this->_response,
            $destinationMetadata,
            $this->_collabPersonId
        );

        $this->_responseAttributes['urn:mace:dir:attribute-def:eduPersonTargetedID'] = array(
            $nameId
        );
    }
}
