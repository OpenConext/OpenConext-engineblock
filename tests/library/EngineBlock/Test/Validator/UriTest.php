<?php
/**
 * @todo write test which tests failing...this validator is so permissive it is VERY hard to let it fail
 */
class EngineBlock_Test_Validator_UriTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Validator_Urn
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new EngineBlock_Validator_Uri();
    }

    /**
     * @dataProvider validUriProvider
     */
    public function testUriValidates($uri)
    {
        $this->assertTrue($this->validator->validate($uri));
    }

    public function validUriProvider()
    {
        return array(
            array('http://example.com'), // Pretty standard http url
            array('urn:mace:dir:entitlement:common-lib-terms') // Saml entitlement (not valid when using Zend_Uri)
        );
    }
}
