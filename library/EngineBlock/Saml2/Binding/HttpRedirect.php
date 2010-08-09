<?php
 
class EngineBlock_Saml2_Binding_HttpRedirect extends EngineBlock_Saml2_Binding_Abstract
{
    const URN = "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect";

    public static function isBeingUsed(EngineBlock_Http_Request $request)
    {
        if ($request->getMethod() !== "GET") {
            return false;
        }

        return true;
    }

    public function receiveAuthenticationRequest()
    {

    }

    public function receiveAssertion()
    {
        
    }
}
