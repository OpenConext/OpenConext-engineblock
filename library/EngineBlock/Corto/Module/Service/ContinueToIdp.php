<?php

/**
 * Copyright 2014 SURFnet B.V.
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

class EngineBlock_Corto_Module_Service_ContinueToIdp extends EngineBlock_Corto_Module_Service_Abstract
{
    /**
     * Handle the forwarding of the user to the proper IdP0 after the WAYF screen.
     *
     * @param string $serviceName
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @throws EngineBlock_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function serve($serviceName)
    {
        $selectedIdp = urldecode($_REQUEST['idp']);
        if (!$selectedIdp) {
            throw new EngineBlock_Corto_Module_Services_Exception(
                'No IdP selected after WAYF'
            );
        }

        // Retrieve the request from the session.
        $id      = $_POST['ID'];
        if (!$id) {
            throw new EngineBlock_Exception(
                'Missing ID for AuthnRequest after WAYF',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_server->getLogger());
        $request = $authnRequestRepository->findRequestById($id);

        if (!$request) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                'Session lost after WAYF'
            );
        }

        // Flush log if SP or IdP has additional logging enabled
        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($selectedIdp);
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->flushLog('Activated additional logging for the SP or IdP');

            $log = $application->getLogInstance();
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }
}
