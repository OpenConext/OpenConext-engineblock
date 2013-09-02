<?php
interface Dummy_Model_Sp_TestCase_TestCaseInterface
{
    public function decorateRequest(SAML2_Request $request);
    public function setBindingType($bindingType);
}