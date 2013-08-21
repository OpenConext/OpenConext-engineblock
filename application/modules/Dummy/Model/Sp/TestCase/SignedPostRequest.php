
<?php
class Dummy_Model_Sp_TestCase_SignedPostRequest
    implements Dummy_Model_Sp_TestCase_TestCaseInterface
{
    public function decorateRequest(SAML2_Request $request)
    {
        // @todo fix generic support for this
        $sspIdpConfig = array();
        $sspIdpConfig['privatekey'] = ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/private_key.pem';
        $sspIdpConfig['certData'] = file_get_contents(ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/certificate.crt');
        $idpConfig = new SimpleSAML_Configuration($sspIdpConfig, null);
        sspmod_saml_Message::addSign($idpConfig, null, $request);
    }

    /**
     * @param string &$bindingType
     */
    public function setBindingType(&$bindingType)
    {
        $bindingType = Dummy_Model_Binding_BindingFactory::TYPE_POST;
    }
}