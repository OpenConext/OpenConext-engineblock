<?php

/**
 * Validate if the IDP sending this response is allowed to connect to the SP that made the request.
 **/
class EngineBlock_Corto_Filter_Command_ValidateAllowedConnection extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $serviceRegistryAdapter = $diContainer->getServiceRegistryAdapter();
        $isConnectionAllowed = $serviceRegistryAdapter->isConnectionAllowed(
            $this->_spMetadata->entityId,
            $this->_idpMetadata->entityId
        );

        if (!$isConnectionAllowed) {
            throw new EngineBlock_Corto_Exception_InvalidConnection(
                "Received a response from an IDP that is not allowed to connect to the requesting SP"
            );
        }
    }
}
