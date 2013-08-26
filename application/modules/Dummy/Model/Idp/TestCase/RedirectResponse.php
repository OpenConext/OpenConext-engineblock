<?php
class Dummy_Model_Idp_TestCase_RedirectResponse
    implements Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateResponse(SAML2_Response $response) {}

    /**
     * @param string &$bindingType
     */
    public function setBindingType(&$bindingType)
    {
        $bindingType = Dummy_Model_Binding_BindingFactory::TYPE_REDIRECT;
    }
}