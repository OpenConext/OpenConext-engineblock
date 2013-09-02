<?php
class Dummy_Model_Idp_TestCase_PrivateKeyRollover
    implements Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateConfig(SimpleSAML_Configuration $config)
    {
        // Changed keys so EB will not recognize them
        $sspConfig = array();
        $keysPath = ENGINEBLOCK_FOLDER_APPLICATION . 'modules/Dummy/keys/';
        $sspConfig['privatekey'] = $keysPath . 'private_key-rolled-over.pem';
        $sspConfig['certData'] = file_get_contents($keysPath . 'certificate-rolled-over.crt');
        return new SimpleSAML_Configuration($sspConfig, null);
    }

    public function decorateResponse(SAML2_Response $response)
    {
        return $response;
    }

    public function setBindingType($bindingType)
    {
        return $bindingType;
    }
}