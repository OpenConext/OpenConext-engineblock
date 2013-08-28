
<?php
class Dummy_Model_Sp_TestCase_PostRequest
    implements Dummy_Model_Sp_TestCase_TestCaseInterface
{
    public function decorateRequest(SAML2_Request $request)
    {
        return $request;
    }

    /**
     * @param string &$bindingType
     */
    public function setBindingType($bindingType)
    {
        return Dummy_Model_Binding_BindingFactory::TYPE_POST;
    }
}