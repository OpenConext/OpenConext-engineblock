<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($argument = null)
    {
        $this->setNoRender();

        try {
     
            $proxyServer = new EngineBlock_Corto_Adapter();
      
            $idPEntityId = NULL;
            
            if (substr($argument, 0, 3)=="vo:") {
                $proxyServer->setVirtualOrganisationContext(substr($argument,3));
            } else {
                $idPEntityId = $argument;
            }

            $proxyServer->singleSignOn($idPEntityId);
        } catch(EngineBlock_Groups_Exception_UserDoesNotExist $e) {
            EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setRedirectUrl('/error/myerror');
        }
    }

    public function processWayfAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();
    }

    public function metadataAction($argument = null)
    {
        $this->setNoRender();
        
        $proxyServer = new EngineBlock_Corto_Adapter();
        
        if (substr($argument, 0, 3)=="vo:") {
            $proxyServer->setVirtualOrganisationContext(substr($argument,3));
        }
        
        $proxyServer->idPMetadata();
    }

    public function processConsentAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();
    }
}
