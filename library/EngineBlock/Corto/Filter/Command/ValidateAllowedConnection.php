<?php

/**
 * Validate if the IDP sending this response is allowed to connect to the SP that made the request.
 **/
class EngineBlock_Corto_Filter_Command_ValidateAllowedConnection extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        $metadataRepository = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
        $allowedIdpEntityIds = $metadataRepository->findAllowedIdpEntityIdsForSp($this->_serviceProvider);
        if (!in_array($this->_identityProvider->entityId, $allowedIdpEntityIds)) {
            throw new EngineBlock_Corto_Exception_InvalidConnection(
                "Disallowed response by SP configuration. " .
                "Response from IdP '{$this->_identityProvider->entityId}' to SP '{$this->_serviceProvider->entityId}'"
            );
        }
    }
}
