<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($argument = null)
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {
     
            $proxyServer = new EngineBlock_Corto_Adapter();
      
            $idPEntityId = NULL;
            
            if (substr($argument, 0, 3)=="vo:") {
                $proxyServer->setVirtualOrganisationContext(substr($argument,3));
            } else {
                $idPEntityId = $argument;
            }

            $proxyServer->singleSignOn($idPEntityId);
        }
        catch (EngineBlock_Groups_Exception_UserDoesNotExist $e) {
            $application->getLog()->warn('User does not exist error');
            $application->getHttpResponse()->setRedirectUrl('/error/myerror');
        }
        catch (Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLog()->warn('Unable to receive message');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/unable-to-receive-message');
        }
        catch (Corto_Module_Services_SessionLostException $e) {
            $application->getLog()->warn('Session was lost');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/session-lost');
        }
        catch (Corto_Module_Bindings_UnknownIssuerException $e) {
            $application->getLog()->warn('Unknown issuer');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/unknown-issuer');
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
