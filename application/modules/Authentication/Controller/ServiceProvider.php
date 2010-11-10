<?php
 
class Authentication_Controller_ServiceProvider extends EngineBlock_Controller_Abstract
{
    public function consumeAssertionAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        
        try {
            $proxyServer->consumeAssertion();
        }
        catch(EngineBlock_Exception_UserNotMember $e) {
            EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setRedirectUrl('/authentication/feedback/vomembershiprequired');
        }
    }

    public function processConsentAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();
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
