<?php
 
class Authentication_Controller_ServiceProvider extends EngineBlock_Controller_Abstract
{
    public function consumeAssertionAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();
        
        try {
            $proxyServer->consumeAssertion();
        }
        catch(EngineBlock_Exception_UserNotMember $e) {
            $application->getLog()->warn('User not a member error');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/vomembershiprequired');
        }
        catch(Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLog()->warn('Unable to receive message error');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/unable-to-receive-message');
        }
        catch (Corto_Module_Bindings_UnknownIssuerException $e) {
            $application->getLog()->warn($e->getMessage());
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/unknown-issuer');
        }
    }

    public function processConsentAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();

        try {
            $proxyServer->processConsent();
        }
        catch (Corto_Module_Services_SessionLostException $e) {
            $application->getLog()->warn('Session lost error');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/session-lost');
        }
    }

    /**
     * The metadata for EngineBlock as a Service Provider
     *
     * @return void
     */
    public function metadataAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->sPMetadata();
    }
}
