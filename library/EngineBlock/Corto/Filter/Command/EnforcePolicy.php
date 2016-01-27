<?php

class EngineBlock_Corto_Filter_Command_EnforcePolicy extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        if (!$serviceProvider) {
            $serviceProvider = $this->_serviceProvider;
        }

        if (!$serviceProvider->policyEnforcementDecisionRequired) {
            return;
        }

        EngineBlock_ApplicationSingleton::getLog()->debug(
            "Policy Enforcement Point consult"
        );

        $validator = $this->_getValidator();
        $hasAccess = $validator->hasAccess(
            $this->_collabPersonId,
            $this->_identityProvider->entityId,
            $serviceProvider->entityId,
            $this->_responseAttributes
        );

        if ($hasAccess) {
            return;
        }

        $message = "Policy Decision Point: access denied.";
        if ($validator->getMessage()) {
            $message = $validator->getMessage();
        }

        EngineBlock_ApplicationSingleton::getLog()->debug(
            "Policy Enforcement Point access denied: " . $message
        );
        throw new EngineBlock_Corto_Exception_PEPNoAccess($message);
    }

    /**
     * @return EngineBlock_PolicyDecisionPoint_PepValidator
     */
    protected function _getValidator()
    {
        return new EngineBlock_PolicyDecisionPoint_PepValidator();
    }
}
