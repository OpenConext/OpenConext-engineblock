<?php
//    DummyIdp/Model/TestCase/ErrorStatusCode.php
class DummyIdp_Model_TestCase_ErrorStatusCode
    implements DummyIdp_Model_TestCase_TestCaseInterface
{
    public function decorateResponse(SAML2_Response $response)
    {
        $response->setStatus(array(
            'Code' => 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy'
        ));
    }
}