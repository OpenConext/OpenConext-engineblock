<?php

class EngineBlock_Corto_Module_Service_ContinueToIdp extends EngineBlock_Corto_Module_Service_Abstract
{
    /**
     * Handle the forwarding of the user to the proper IdP0 after the WAYF screen.
     *
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     * @return void
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
        if (!isset($_SESSION[$id]['SAMLRequest'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                'Session lost after WAYF'
            );
        }
        $request = $_SESSION[$id]['SAMLRequest'];

        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractIssuerFromMessage($request));
        $idp = $this->_server->getRemoteEntity($selectedIdp);
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp, $idp)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }
}