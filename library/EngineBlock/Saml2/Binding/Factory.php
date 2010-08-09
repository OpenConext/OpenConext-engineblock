<?php
 
class EngineBlock_Saml2_Binding_Factory
{
    protected static $_registeredBindingClassNames = array(
        'EngineBlock_Saml2_Binding_HttpPost',
        'EngineBlock_Saml2_Binding_HttpRedirect',
    );

    public static function createByDiscovery(EngineBlock_Http_Request $request)
    {
        foreach (self::$_registeredBindingClassNames as $registeredBindingClassName) {
            if ($registeredBindingClassName::isBeingUsed($request)) {
                return new $registeredBindingClassName($request);
            }
        }
    }

    public static function createFromUrn($urn, $request)
    {
        foreach (self::$_registeredBindingClassNames as $registeredBindingClassName) {
            if ($registeredBindingClassName::URN === $urn) {
                return new $registeredBindingClassName($request);
            }
        }
    }
}
