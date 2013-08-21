<?php
class Dummy_Model_Idp_TestCase_ErrorStatusCode
    implements Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateResponse(SAML2_Response $response)
    {
        $response->setStatus(array(
            'Code' => 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy',
            'Message' => 'NameIdPolicy is invalid'
        ));
    }
}