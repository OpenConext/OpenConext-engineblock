<?php

/**
 * Action helper to force authentication in every action.
 *
 * @todo make this more flexible: Accept more different types of identities.
 * @author marc
 */
class Surfnet_Zend_Helper_Authenticate extends Zend_Controller_Action_Helper_Abstract
{
    const AUTH_DISPLAY_NAME_SAML_ATTRIBUTE = 'urn:mace:dir:attribute-def:cn';

    /**
     * Authenticate the user.
     *
     * @static
     * @return SurfConext_Identity
     */
    public function direct()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_NonPersistent());
        $adapter = new Surfnet_Zend_Auth_Adapter_Saml();

        $res = $auth->authenticate($adapter);

        $samlIdentity = $res->getIdentity();
        $identity = new SurfConext_Identity($samlIdentity['nameid'][0]);
        $identity->displayName = $samlIdentity[self::AUTH_DISPLAY_NAME_SAML_ATTRIBUTE][0];

        return $identity;
    }
}