<?php

/**
 * Validate if the IDP sending this response is allowed to connect to the SP that made the request.
 **/
class EngineBlock_Corto_Filter_Command_ValidateAllowedConnection extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        if (!$this->_serviceProvider->isAllowed($this->_identityProvider->entityId)) {
            throw new EngineBlock_Corto_Exception_InvalidConnection(
                sprintf(
                    'Disallowed response by SP configuration. Response from IdP "%s" to SP "%s"',
                    $this->_identityProvider->entityId,
                    $this->_serviceProvider->entityId
                )
            );
        }
    }
}
