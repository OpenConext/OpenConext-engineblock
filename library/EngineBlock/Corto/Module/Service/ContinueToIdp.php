<?php

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

        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_server->getSessionLog());
        $request = $authnRequestRepository->findRequestById($id);

        if (!$request) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                'Session lost after WAYF'
            );
        }

        // Flush log if SP or IdP has additional logging enabled
        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($selectedIdp);
        if (
            $this->_server->getConfig('debug', false) ||
            EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))
        ) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }
}
