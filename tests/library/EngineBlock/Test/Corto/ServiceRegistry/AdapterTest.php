<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class EngineBlock_Test_Corto_ServiceRegistry_AdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Corto_ServiceRegistry_Adapter
     */
    protected $_adapter;

    public function setUp()
    {
        $serviceRegistry = new EngineBlock_Test_ServiceRegistryMock();
        $serviceRegistry->setIdPList(array(
            "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" => array(
                "base64attributes" => true,
                "certData" => "MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMCTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYDVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xiZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2ZlaWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5vMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2QarQ4/67OZfHd7R+POBXhophSMv1ZOo",
                "description:en" => "EngineBlock Testing IdP",
                "description:nl" => "EngineBlock Testing IdP",
                "name:en" => "EngineBlock Testing IdP",
                "name:nl" => "EngineBlock Testing IdP",
                'keywords:en' => 'test,english',
                'keywords:nl' => 'test, nederlands',
                "redirect.sign" => true,
                "SingleSignOnService:0:Binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
                "SingleSignOnService:0:Location" => "https://idp.testing.dev.coin.surf.net/simplesaml/saml2/idp/SSOService.php",
                "metadataUrl" => "https://ss.idp.ebdev.net/simplesaml/saml2/idp/metadata.php",
                "entityID" => "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
                "disableConsent:0" => "http://test-sp"

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
                "redirect.sign"=> true,
                "metadataUrl"=> "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp",
                "entityID"=> "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp",
                'coin:implicit_vo_id'    => 'rave-devs',
                'contacts:1:contactType' => 'support',
                'contacts:1:emailAddress'=> 'boy@ibuildings.nl',
                'contacts:1:givenName'   => 'Boy',
                'contacts:1:surName'     => 'Baukema',
                'OrganizationName:nl' => 'SURFnet',
                'OrganizationName:en' => 'SURFnet',
                'OrganizationURL:en' => 'https://www.surfnet.nl/en/',
                'OrganizationURL:nl' => 'https://www.surfnet.nl',
                'SingleLogoutService_Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'SingleLogoutService_Location' => 'https://engine.surfconext.nl/logout'
            ),
        ));

        $this->_adapter = new EngineBlock_Corto_ServiceRegistry_Adapter($serviceRegistry);
    }

    public function testGetRemoteMetaData()
    {
        $metadata = $this->_adapter->getRemoteMetaData();

        $expectedResult = array(
            "https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp"=> array(
                'VoContext' => 'rave-devs',
                "AssertionConsumerServices"=> array(
                    0 => array(
                        'Binding'  => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                        'Location' => "https://sp.testing.dev.coin.surf.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp"
                    ),
                ),
                'MustProvisionExternally' => false,
                'ProvideIsMemberOf' => false,
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
                    'nl' => "EngineBlock Testing SP",
                    'en' => "EngineBlock Testing SP",
                ) ,
                "Description"=> array(
                    'nl' => "EngineBlock Testing SP",
                    'en' => "EngineBlock Testing SP",
                ),
                "AuthnRequestsSigned" => true,
                'Organization'=> array(
                    'Name' => array(
                        'nl' => 'SURFnet',
                        'en' => 'SURFnet',
                    ),
                    'URL' => array(
                        'nl' => 'https://www.surfnet.nl',
                        'en' => 'https://www.surfnet.nl/en/',
                    ),
                ),
                'NameIDFormats' => array(
                    0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                    1 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                ),
                'ContactPersons' => array(
                    1 => array(
                        'ContactType' => 'support',
                        'EmailAddress'=> 'boy@ibuildings.nl',
                        'GivenName'   => 'Boy',
                        'SurName'     => 'Baukema',
                    ),
                ),
                'EntityID' => 'https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp',
                'SingleLogoutService' => array(
                    array(
                        'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                        'Location' => 'https://engine.surfconext.nl/logout'
                    )
                ),
            ),
            "https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" => array(
                "SingleSignOnService" => array(
                    array(
                        'Binding'   => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
                        'Location'  => "https://idp.testing.dev.coin.surf.net/simplesaml/saml2/idp/SSOService.php"
                    )
                ),
                "GuestQualifier" => "All",
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
                    'nl' => "EngineBlock Testing IdP",
                    'en' => "EngineBlock Testing IdP",
                ) ,
                "Description"=> array(
                    'nl' => "EngineBlock Testing IdP",
                    'en' => "EngineBlock Testing IdP",
                ),
                'AuthnRequestsSigned' => true,
                'Keywords' => array(
                    'nl' => 'test, nederlands',
                    'en' => 'test,english',
                ),
                "AuthnRequestsSigned" => true,
                'NameIDFormats' => array(
                    0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                    1 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                ),
                'EntityID' => 'https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
                "SpsWithoutConsent" => array(
                    "http://test-sp"
                ),
                'isHidden' => false,
                'shibmd:scopes' => Array ()
            ),
        );
        $this->assertEquals($expectedResult, $metadata, "Converting a simple result from Service Registry with 1 IdP and 1 SP to Cortos Metadata format");
    }
}
