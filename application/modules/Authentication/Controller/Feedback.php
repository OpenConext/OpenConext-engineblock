<?php

class Authentication_Controller_Feedback extends EngineBlock_Controller_Abstract
{
    public function vomembershiprequiredAction()
    {
        $this->_getResponse()->setStatus(403, 'Forbidden');
        session_start();
    }

    public function unableToReceiveMessageAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        session_start();
    }

    public function sessionLostAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        session_start();
    }

    public function dissimilarWorkflowStatesAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        session_start();
    }

    public function unknownIssuerAction()
    {
        $this->_getResponse()->setStatus(404, 'Not Found');
        $this->__set('entity-id', $this->_getRequest()->getQueryParameter('entity-id'));
        $this->__set('destination', $this->_getRequest()->getQueryParameter('destination'));
        session_start();
    }

    public function unknownServiceProviderAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        $this->__set('entity-id', $this->_getRequest()->getQueryParameter('entity-id'));
        session_start();
    }

    public function unknownPreselectedIdpAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        $this->__set('idp-hash', $this->_getRequest()->getQueryParameter('idp-hash'));
        session_start();
    }

    public function missingRequiredFieldsAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        session_start();
    }

    public function authorizationPolicyViolationAction()
    {
        session_start();
    }

    public function noConsentAction()
    {
        session_start();
    }

    public function customAction()
    {
        session_start();
    }

    public function invalidAcsLocationAction()
    {
        $this->_getResponse()->setStatus(400, 'Bad Request');
        session_start();
    }

    public function invalidAcsBindingAction()
    {
        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
        session_start();
    }

    public function receivedErrorStatusCodeAction()
    {
        // @todo Send 4xx or 5xx header?
        session_start();
    }

    public function receivedInvalidResponseAction()
    {
        // @todo Send 4xx or 5xx header?
        session_start();
    }

    public function receivedInvalidSignedResponseAction()
    {
        // @todo Send 4xx or 5xx header?
        session_start();
    }

    public function noIdpsAction()
    {
        // @todo Send 4xx or 5xx header?
        session_start();
    }
}
