<?php
class Dummy_Model_Idp_TestCase_RedirectResponse
    implements Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateConfig(SimpleSAML_Configuration $config)
    {
        return $config;
    }

    public function decorateResponse(SAML2_Response $response)
    {
        return $response;
    }

    public function setBindingType($bindingType)
    {
        return Dummy_Model_Binding_BindingFactory::TYPE_REDIRECT;
    }
}