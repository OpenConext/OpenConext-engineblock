<?php

use OpenConext\EngineBlockBundle\Pdp\Dto\Request;

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

        $log = EngineBlock_ApplicationSingleton::getLog();
        $log->debug("Policy Enforcement Point: consulting Policy Decision Point");

        $pdpRequest = Request::from(
            $this->_collabPersonId,
            $this->_identityProvider->entityId,
            $serviceProvider->entityId,
            $this->_responseAttributes
        );

        $log->debug("Policy Enforcement Point: Requesting decision from PDP");

        $pdp = $this->getPdpClient();
        $policyDecision = $pdp->requestDecisionFor($pdpRequest);

        $log->debug("Policy Enforcement Point: PDP decision received.");

        if ($policyDecision->permitsAccess()) {
            $log->debug("Policy Enforcement Point: PDP permits access");

            return;
        }

        $log->debug("Policy Enforcement Point: PDP did not permit access, enforcing decision");

        if ($policyDecision->hasStatusMessage()) {
            EngineBlock_ApplicationSingleton::getLog()->debug(sprintf(
                'Policy Enforcement Point access denied with status message "%s"',
                $policyDecision->getStatusMessage()
            ));
        }

        if ($policyDecision->hasLocalizedDenyMessage()) {
            EngineBlock_ApplicationSingleton::getLog()->debug(sprintf(
                'Policy Enforcement Point access denied with status message "%s"',
                $policyDecision->getLocalizedDenyMessage('en')
            ));
        }

        throw EngineBlock_Corto_Exception_PEPNoAccess::basedOn($policyDecision);
    }

    /**
     * @return EngineBlock_PolicyDecisionPoint_PepValidator
     */
    protected function _getValidator()
    {
        return new EngineBlock_PolicyDecisionPoint_PepValidator();
    }

    /**
     * @return OpenConext\EngineBlockBundle\Pdp\PdpClient
     */
    private function getPdpClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getPdpClient();
    }
}
