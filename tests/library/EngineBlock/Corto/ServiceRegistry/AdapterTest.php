<?php

require_once dirname(__FILE__) . '/../../../../autoloading.inc.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../ServiceRegistryMock.php';

class EngineBlock_Corto_ServiceRegistry_AdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Corto_ServiceRegistry_Adapter
     */
    protected $_adapter;

    public function setUp()
    {
        $serviceRegistry = new EngineBlock_ServiceRegistryMock();
        $serviceRegistry->setIdPList(array(
            "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" => array(
                "base64attributes" => true,
                "certData" => "MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMCTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYDVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xiZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2ZlaWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5vMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2QarQ4/67OZfHd7R+POBXhophSMv1ZOo",
                "description:en" => "EngineBlock Testing IdP",
                "description:nl" => "EngineBlock Testing IdP",
                "name:en" => "EngineBlock Testing IdP",
                "name:nl" => "EngineBlock Testing IdP",
                "organization:OrganizationDisplayName:nl" => "EngineBlock Testing IdP",
                "organization:OrganizationName:nl" => "EngineBlock Testing IdP",
                "organization:OrganizationURL:nl" => "http://www.surfnet.nl",
                "redirect.sign" => true,
                "redirect.validate" => true,
                "SingleSignOnService:0:Binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
                "SingleSignOnService:0:Location" => "https://idp.testing.dev.coin.surf.net/simplesaml/saml2/idp/SSOService.php",
                "metadataUrl" => "https://ss.idp.ebdev.net/simplesaml/saml2/idp/metadata.php",
                "entityID" => "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp"
            ),
        ));
        $serviceRegistry->setSPList(array(
            "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp"=> array(
                "AssertionConsumerService:0:Binding"=> "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                "AssertionConsumerService:0:Location"=> "https://sp.testing.dev.coin.surf.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
                "base64attributes"=> true,
                "certData"=> "MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMCTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYDVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xiZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2ZlaWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5vMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2QarQ4/67OZfHd7R+POBXhophSMv1ZOo",
                "description:en"=> "EngineBlock Testing SP",
                "description:nl"=> "EngineBlock Testing SP",
                "name:en"=> "EngineBlock Testing SP",
                "name:nl"=> "EngineBlock Testing SP",
                "organization:OrganizationDisplayName:nl"=> "EngineBlock Testing SP",
                "organization:OrganizationName:nl"=> "EngineBlock Testing SP",
                "organization:OrganizationURL:nl"=> "http://www.surfnet.nl",
                "redirect.sign"=> true,
                "redirect.validate"=> true,
                "metadataUrl"=> "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp",
                "entityID"=> "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp"
            ),
        ));

        $this->_adapter = new EngineBlock_Corto_ServiceRegistry_Adapter($serviceRegistry);
    }

    public function testGetRemoteMetaData()
    {
        $metadata = $this->_adapter->getRemoteMetaData();

        $expectedResult = array(
            "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp"=> array(
                "AssertionConsumerService"=> array(
                    'Binding'  => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                    'Location' => "https://sp.testing.dev.coin.surf.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp"
                ),
                'WantsAssertionsSigned' => true,
                "certificates" => array(
                    'public' => "-----BEGIN CERTIFICATE-----
MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMC
Tk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYD
VQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG
9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4
MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xi
ZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2Zl
aWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5v
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LO
NoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHIS
KOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d
1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8
BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7n
bK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2Qar
Q4/67OZfHd7R+POBXhophSMv1ZOo
-----END CERTIFICATE-----
",
                ),
                "Name"=> array(
                    'en' => "EngineBlock Testing SP",
                    'nl' => "EngineBlock Testing SP",
                ) ,
                "Description"=> array(
                    'en' => "EngineBlock Testing SP",
                    'nl' => "EngineBlock Testing SP",
                ),
                "WantsAuthnRequestsSigned" => true,
                'WantsResponsesSigned' => true,
            ),
            "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" => array(
                "SingleSignOnService" => array(
                    'Binding'   => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
                    'Location'  => "https://idp.testing.dev.coin.surf.net/simplesaml/saml2/idp/SSOService.php"
                ),
                "certificates" => array(
                    'public' => "-----BEGIN CERTIFICATE-----
MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMC
Tk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYD
VQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG
9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4
MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xi
ZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2Zl
aWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5v
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LO
NoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHIS
KOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d
1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8
BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7n
bK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2Qar
Q4/67OZfHd7R+POBXhophSMv1ZOo
-----END CERTIFICATE-----
",
                ),
                "Name"=> array(
                    'en' => "EngineBlock Testing IdP",
                    'nl' => "EngineBlock Testing IdP",
                ) ,
                "Description"=> array(
                    'en' => "EngineBlock Testing IdP",
                    'nl' => "EngineBlock Testing IdP",
                ),
                "WantsAuthnRequestsSigned"  => true,
                'WantsResponsesSigned'      => true,
            ),
        );
        $this->assertEquals($expectedResult, $metadata, "Converting a simple result from Service Registry with 1 IdP and 1 SP to Cortos Metadata format");
    }

    public function testServiceRegistryEntityToMultiDimensionalArray()
    {
        $serviceRegistryEntity = array(
            'Aa:b1:0:tekno'     => 'darkon',
            'Aa:c1:c2:1:if'     => 'not',
            'something.else'    => 'sacrifice',
            'minimal_testcase'  => 'real'
        );

        $expectedResult = array(
            'Aa'=> array(
                'b1' => array(
                    0 => array(
                        'tekno' => 'darkon',
                    )
                ),
                'c1' => array(
                    'c2' => array(
                        1 => array(
                            'if' => 'not',
                        ),
                    )
                ),
            ),
            'something' => array(
                'else' => 'sacrifice',
            ),
            'minimal_testcase' => 'real',
        );

        $convertedEntity = EngineBlock_Corto_ServiceRegistry_Adapter::convertServiceRegistryEntityToMultiDimensionalArray($serviceRegistryEntity);

        $this->assertEquals($expectedResult, $convertedEntity, "Converting a service registry entity to a multi-dimensional array");
    }
}
