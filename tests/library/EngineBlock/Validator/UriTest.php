<?php
/**
 * @todo write test which tests failing...this validator is so permissive it is VERY hard to let it fail
 */
class EngineBlock_Validator_UriTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validUriProvider
     */
    public function testUriValidates($uri)
    {
        $validator = new EngineBlock_Validator_Uri();
        $this->assertTrue($validator->validate($uri));
    }

    public function validUriProvider()
    {
        return array(
            array('http://example.com'), // Pretty standard http url
            array('urn:mace:dir:entitlement:common-lib-terms') // Saml entitlement (not valid when using Zend_Uri)
        );
    }
}
