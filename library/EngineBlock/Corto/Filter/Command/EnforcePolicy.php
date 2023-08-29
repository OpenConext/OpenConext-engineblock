<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlockBundle\Pdp\Dto\Request;

class EngineBlock_Corto_Filter_Command_EnforcePolicy extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        $log = EngineBlock_ApplicationSingleton::getLog();
        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository(),
            $log
        );

        if (!$serviceProvider) {
            $serviceProvider = $this->_serviceProvider;
        }

        if (!$serviceProvider->getCoins()->policyEnforcementDecisionRequired()) {
            return;
        }

        $remoteIp = EngineBlock_ApplicationSingleton::getInstance()->getClientIpAddress();

        $log = EngineBlock_ApplicationSingleton::getLog();
        $log->debug("Policy Enforcement Point: consulting Policy Decision Point");

        $pdpRequest = Request::from(
            $this->getPdpClientId(),
            $this->_collabPersonId,
            $this->_identityProvider->entityId,
            $serviceProvider->entityId,
            $this->_responseAttributes,
            $remoteIp
        );

        $log->debug("Policy Enforcement Point: Requesting decision from PDP");

        $pdp = $this->getPdpClient();
        $policyDecision = $pdp->requestDecisionFor($pdpRequest);
        // The IdP logo is set after getting the PolicyDecision as it would be inappropriate to inject this into the
        // decision request.
        if ($this->_identityProvider->getMdui()->hasLogo()){
            $policyDecision->setIdpLogo(
                $this->_identityProvider->getMdui()->getLogo()
            );
        }
        $log->debug("Policy Enforcement Point: PDP decision received.");

        $pdpLoas = $policyDecision->getStepupObligations();
        if (count($pdpLoas) > 0) {
            $log->notice("Policy Enforcement Point: stepup LoA obligations received: " . implode(',', $pdpLoas));
            $this->_response->setPdpRequestedLoas($pdpLoas);
        }

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
     * @return OpenConext\EngineBlockBundle\Pdp\PdpClient
     */
    private function getPdpClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getPdpClient();
    }

    /**
     * @return string
     */
    private function getPdpClientId()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getPdpClientId();
    }
}
