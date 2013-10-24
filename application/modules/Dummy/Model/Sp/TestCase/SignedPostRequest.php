
<?php
class Dummy_Model_Sp_TestCase_SignedPostRequest
    implements Dummy_Model_Sp_TestCase_TestCaseInterface
{
    public function decorateRequest(SAML2_Request $request)
    {
        $sspConfig = Dummy_Model_DiContainer::getInstance()->getSimpleSamlPhpConfig();
        sspmod_saml_Message::addSign(
            $sspConfig,
            SimpleSAML_Configuration::loadFromArray(array()),
            $request
        );
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
