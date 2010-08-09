<?php
 
abstract class EngineBlock_Saml2_Binding_Abstract 
{
    protected $_request;

    public function __construct(EngineBlock_Http_Request $request)
    {
        $this->_request = $request;
    }

    public static function isBeingUsed(EngineBlock_Http_Request $request)
    {
        return false;
    }

    abstract public function receiveAuthenticationRequest();
    abstract public function receiveAssertion();
}
