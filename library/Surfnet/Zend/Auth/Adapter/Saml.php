<?php

class Surfnet_Zend_Auth_Adapter_Saml implements Zend_Auth_Adapter_Interface
{
    /**
     * Performs an authentication attempt using SimpleSAMLphp
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $authenticator = $this->_getAuthenticator();

        $authenticator->requireAuth();


        // If SimpleSAMLphp didn't stop it, then the user is logged in.

        return new Zend_Auth_Result(
            Zend_Auth_Result::SUCCESS,
            $authenticator->getAttributes(),
            array("Authentication Successful")
        );
    }

    public function getEntityId()
    {
        $authenticator = $this->_getAuthenticator();

        $authSource = $authenticator->getAuthSource();
        if (!$authSource instanceof sspmod_saml_Auth_Source_SP) {
            throw new Exception('Authenticator is not SAML?');
        }
        /** @var $authSource sspmod_saml_Auth_Source_SP */

        return $authSource->getEntityId();
    }

    protected function _getAuthenticator()
    {
        require_once(ENGINEBLOCK_FOLDER_VENDOR . 'simplesamlphp/simplesamlphp/lib/_autoload.php');
        return new SimpleSAML_Auth_Simple('default-sp');
    }
}