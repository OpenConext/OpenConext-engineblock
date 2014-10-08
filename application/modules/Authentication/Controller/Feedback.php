<?php

class Authentication_Controller_Feedback extends EngineBlock_Controller_Abstract
{
    public function vomembershiprequiredAction()
    {
        $this->_getResponse()->setStatus(403, 'Forbidden');
    }

    public function unableToReceiveMessageAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
    }

    public function sessionLostAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
    }

    public function unknownIssuerAction()
    {
        $this->_getResponse()->setStatus(404, 'Not Found');
        $this->__set('entity-id', $this->_getRequest()->getQueryParameter('entity-id'));
        $this->__set('destination', $this->_getRequest()->getQueryParameter('destination'));
    }

    public function unknownServiceProviderAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        $this->__set('entity-id', $this->_getRequest()->getQueryParameter('entity-id'));
    }

    public function missingRequiredFieldsAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
    }

    public function noConsentAction()
    {

    }

    public function customAction()
    {
        $proxyServer = new EngineBlock_Corto_ProxyServer();
        $proxyServer->startSession();
    }

    public function invalidAcsLocationAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
    }

    public function invalidAcsBindingAction()
    {
        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
    }

    public function receivedErrorStatusCodeAction()
    {
        // @todo Send 4xx or 5xx header?
    }

    public function receivedInvalidResponseAction()
    {
        // @todo Send 4xx or 5xx header?
    }

    public function receivedInvalidSignedResponseAction()
    {
        // @todo Send 4xx or 5xx header?
    }

    public function noIdpsAction()
    {
        // @todo Send 4xx or 5xx header?
    }
}
