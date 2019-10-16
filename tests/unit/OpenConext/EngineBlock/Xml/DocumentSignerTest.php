<?php declare(strict_types=1);

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Xml;

use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlock\Metadata\X509\X509PrivateKey;
use PHPUnit\Framework\TestCase;

class DocumentSignerTest extends TestCase
{
    public function test_signs_xml_documents()
    {
        $signer = new DocumentSigner();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2019-10-16T12:41:12Z" cacheDuration="PT604800S" entityID="Test Entity">
   <md:SPSSODescriptor AuthnRequestsSigned="true" WantAssertionsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
      <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
      <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://example.com/acs" index="1" />
   </md:SPSSODescriptor>
   <md:Organization>
      <md:OrganizationName xml:lang="en-US">Test Organization</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en-US">Test</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en-US">https://examle.org</md:OrganizationURL>
   </md:Organization>
</md:EntityDescriptor>
XML;

        $expextedOutput = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2019-10-16T12:41:12Z" cacheDuration="PT604800S" entityID="Test Entity">
   <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference>
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>UeuCX/0rS+ZFeJT5KQEQ3fB5QrKmygggPFIKkbXLCkE=</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>qANWi8HspdG+jJd5lPKKHQBV46GXf8R7PH6htq4Tq4ZkWkzq87YVbIeCBhs8ayEZquiLS9KdCsV980FgtiahU9iFsSd7oJEAbT/7zFQWd4vf5NkGF2hb+PVVnQI+ysP02slsm8PSCpQHIliDDW5FS2GVgXb+q1InMfWWWBNbyUw8/bUJ2B4xyGvT9ytvSedOY4qceldtNzNz4kDrEP1Qv/iiInU3t59POeq+WFKwXNsdk5Df/koNWdW++Nqm+HOy+SgCNfkGvV4lb6suQyH7yXOKcC7M6/fpruTvg4xNYpd99YNKF+uawx/7JDNLP4P7dxgzHjTl3K8csVvoiwI1cA==</ds:SignatureValue>
      <ds:KeyInfo>
         <ds:X509Data>
            <ds:X509Certificate>MIIDXzCCAkegAwIBAgIJAM4CwNsdIhJ3MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE0MDUxMjEzMjIxNloXDTI0MDUxMTEzMjIxNlowRjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC2aQ9OYsAASWR/aN5NB2mQqFsBc13uN0nSbjkk1Um8VouGo7OmSM0eiur5my8UvPYth1DXQM6u2wiFq19RtfVZWJOmrzAfVHc9VRj9Xj4T+MVpR4bDWctvxVT1OPm9L23KQKvaqDmUo7uSPsBD36EIH7dFOBydDtSXfZTW0ien+lZr6C4nPuxzDbHJ+Jlo2brieimUBQNetX/ettnAglJ9536sJDkhsa120mkYPhVnvepbOtxPyU5ZDUpDNmMQR2/SORCBJcfvLSVZ4It4O67l6/EJnkFRLerIqOpk/W8jY3USQaLM2WM7sWBGxEFKDVcTFgrOH50Z94K2M/KweY2bAgMBAAGjUDBOMB0GA1UdDgQWBBSewI9OzfzbIxnl6XMkaQkYY1hHPjAfBgNVHSMEGDAWgBSewI9OzfzbIxnl6XMkaQkYY1hHPjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQAFfYPZbsYPHz4ypV/aO59do7CtHPnAMWr0NcQt4h9IW8gjihaNHt12V30QtHrVaXejXybB/LaGbPPyA64+l/SeC7ksrRxlitCwFqnws6ISXJaYU0iEFHGUD/cAj1iGloIsOm5IOdb3sdG/SsBv49G8es2wG0rDd0/s2fBVvXd4qUoXzKJAjYk1MFQxnGHomlt67SBrr2QLh+m2VHg+mkdi6yrdm9B9ylF8V55Vl82pPZXxphIRgqdos5YWeALS7dr5dSw9s5smFBxyy8IfCQMxagfNC59w22w2ULC/J7au/oP8ylusuxncxizdR/+5UazzAlOWtkjzaABzzBWM4hEK</ds:X509Certificate>
         </ds:X509Data>
      </ds:KeyInfo>
   </ds:Signature>
   <md:SPSSODescriptor AuthnRequestsSigned="true" WantAssertionsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
      <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
      <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://example.com/acs" index="1" />
   </md:SPSSODescriptor>
   <md:Organization>
      <md:OrganizationName xml:lang="en-US">Test Organization</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en-US">Test</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en-US">https://examle.org</md:OrganizationURL>
   </md:Organization>
</md:EntityDescriptor>
XML;
        $publicKeyFactory = new X509CertificateFactory();

        // Warning: certificates expire on May 11, 2024
        $publicKey = $publicKeyFactory->fromFile(__DIR__ . '/test.pem.crt');
        $privateKey = new X509PrivateKey(__DIR__ . '/test.pem.key');
        $keyPair = new X509KeyPair($publicKey, $privateKey);

        $output = $signer->sign($xml, $keyPair);

        $this->assertXmlStringEqualsXmlString($expextedOutput, $output);
    }
}
