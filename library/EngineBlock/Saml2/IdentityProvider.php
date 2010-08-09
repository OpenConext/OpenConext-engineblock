<?php
 
class EngineBlock_Saml2_IdentityProvider 
{
    const BINDING_DEFAULT = "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect";

    protected $_singleSignOnLocation;
    protected $_singleSignOnBinding;

    public function __construct($id)
    {

    }

    public function setSingleSignOnService($location, $binding = "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect")
    {
        $this->_singleSignOnLocation = $location;
        $this->_singleSignOnBinding  = $binding;
    }

    public function getSingleSignOnLocation()
    {
        return $this->_singleSignOnLocation;
    }
}
