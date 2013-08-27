<?php
interface Dummy_Model_Idp_TestCase_TestCaseInterface
{
    public function decorateConfig(SimpleSAML_Configuration $config);
    public function decorateResponse(SAML2_Response $response);
    public function setBindingType($bindingType);

}