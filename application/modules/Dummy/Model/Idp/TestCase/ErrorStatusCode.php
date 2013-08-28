<?php
class Dummy_Model_Idp_TestCase_ErrorStatusCode
    implements Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateConfig(SimpleSAML_Configuration $config)
    {
        return $config;
    }

    public function decorateResponse(SAML2_Response $response)
    {
        $response->setStatus(array(
            'Code' => 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy',
            'Message' => 'NameIdPolicy is invalid'
        ));

        return $response;
    }

    public function setBindingType($bindingType){
        return $bindingType;
    }
}