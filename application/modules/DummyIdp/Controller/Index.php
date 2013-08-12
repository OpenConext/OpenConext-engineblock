
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

class DummyIdp_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->setNoRender();

        // @todo find out what correct content type is
        header('Content-Type: text/xml');
        $samlResponse = $this->factorySaml2PResponse();

        echo $samlResponse;
        exit;
    }

    private function factorySaml2PResponse()
    {
        $dateTimeFormatted = gmdate('Y-m-d\TH:i:s\Z', time());
        foreach($_SESSION as $sessionKey => $sessionValue) {
            if (isset($sessionValue['_InResponseTo'])) {
                $inResponseTo = $sessionKey;
            }
        }

        if (!isset($inResponseTo)) {
            throw new Exception('Saml response not present in session');
        }

        $saml = <<<SAML
<saml2p:Response xmlns:saml2p="urn:oasis:names:tc:SAML:2.0:protocol"
                 Destination="https://engine.demo.openconext.org/authentication/sp/consume-assertion"
                 ID="bae8bb2f-7316-44df-9176-109974307d12"
                 InResponseTo="CORTOf0db2669974a688815a1f65a8f0bc7654f0e9480"
                 IssueInstant="$dateTimeFormatted"
                 Version="2.0"
                     >
    <saml2:Issuer xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion"
                  Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity"
                      >https://engine.demo.openconext.org/dummy-idp</saml2:Issuer>
    <saml2p:Status>
        <saml2p:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" />
    </saml2p:Status>
    <saml2:Assertion xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion"
                     ID="99d40bfc-5e42-4bb1-99d0-ec705d34ecf3"
                     IssueInstant="$dateTimeFormatted"
                     Version="2.0"
                     xmlns:xs="http://www.w3.org/2001/XMLSchema"
        >
        <saml2:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://engine.demo.openconext.org/dummy-idp</saml2:Issuer>
        <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <ds:SignedInfo>
                <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#" />
                <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1" />
                <ds:Reference URI="#99d40bfc-5e42-4bb1-99d0-ec705d34ecf3">
                    <ds:Transforms>
                        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />
                        <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#">
                            <ec:InclusiveNamespaces xmlns:ec="http://www.w3.org/2001/10/xml-exc-c14n#"
                                                    PrefixList="xs"
                                                        />
                        </ds:Transform>
                    </ds:Transforms>
                    <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1" />
                    <ds:DigestValue>9lrVfHStAV0aYbtSvibfzSeaCSo=</ds:DigestValue>
                </ds:Reference>
            </ds:SignedInfo>
            <ds:SignatureValue>hAGv4eC4hcQKhu+/44rzTBjiEr1BC9DDWpRE9cyN6jPA9bqr6vesbaxEdkiArnBWo3RGlaLY2+jOITU0ni0B/0uf2rc92W+3xhW5txusjTAZNlkTa5h8npT5FQl2ePsWWB05inB16RdkdJQ1Joo+BLytzo1gF1sq44OmBBN0E9w=</ds:SignatureValue>
        </ds:Signature>
        <saml2:Subject>
            <saml2:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">a</saml2:NameID>
            <saml2:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml2:SubjectConfirmationData Address="192.168.56.1"
                                               InResponseTo="CORTOf0db2669974a688815a1f65a8f0bc7654f0e9480"
                                               NotOnOrAfter="2013-08-03T05:10:52.996Z"
                                               Recipient="https://engine.demo.openconext.org/authentication/sp/consume-assertion"
                                                   />
            </saml2:SubjectConfirmation>
        </saml2:Subject>
        <saml2:AuthnStatement AuthnInstant="2013-08-03T05:09:22.644Z">
            <saml2:AuthnContext>
                <saml2:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml2:AuthnContextClassRef>
                <saml2:AuthenticatingAuthority>https://engine.demo.openconext.org/dummy-idp</saml2:AuthenticatingAuthority>
            </saml2:AuthnContext>
        </saml2:AuthnStatement>
        <saml2:AttributeStatement>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:uid">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >a</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >guest</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:sn">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >Doe</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:mail">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >j.doe@example.com</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:eduPersonPrincipalName">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >j.doe@example.com</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:displayName">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >a</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:givenName">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >John</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:terena.org:attribute-def:schacHomeOrganization">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >example.com</saml2:AttributeValue>
            </saml2:Attribute>
            <saml2:Attribute Name="urn:mace:dir:attribute-def:cn">
                <saml2:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                      xsi:type="xs:string"
        >John Doe</saml2:AttributeValue>
            </saml2:Attribute>
        </saml2:AttributeStatement>
    </saml2:Assertion>
</saml2p:Response>

SAML;
        return $saml;
    }
}
