
<?php
class Dummy_Model_Sp_TestCase_UnknownScopingRequester
    implements Dummy_Model_Sp_TestCase_TestCaseInterface
{
    public function decorateRequest(SAML2_Request $request)
    {
        $request->setRequesterID(
            array(
                'unknownRequester'
            )
        );
    }

    /**
     * @param string &$bindingType
     */
    public function setBindingType(&$bindingType) {}
}