<?php

/**
 *
 *
 * @package    Corto
 * @module     Tests
 * @author     Mads Freek Petersen, <freek@ruc.dk>
 * @author     Boy Baukema, <boy@ibuildings.com>
 * @licence    MIT License, see http://www.opensource.org/licenses/mit-license.php
 * @copyright  2009-2010 WAYF.dk
 * @version    $Id:$
 */


require_once 'PHPUnit/Framework.php';
require_once './../lib/Corto/XmlToHash.php';
require_once './../lib/Corto/xh.php';

class XmlToHashTest extends PHPUnit_Framework_TestCase
{
    public function testXmlToHashSampleAuthenticationRequest()
    {
        $xml = '<?xml version="1.0"?>
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="_bec424fa5103428909a30ff1e31168327f79474984" Version="2.0" IssueInstant="2007-12-10T11:39:34Z" ForceAuthn="false" IsPassive="false" ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">urn:mace:feide.no:services:no.feide.moodle</saml:Issuer>
    <samlp:NameIDPolicy xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" SPNameQualifier="moodle.bridge.feide.no" AllowCreate="true"/>
    <samlp:RequestedAuthnContext xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" Comparison="exact">
        <saml:AuthnContextClassRef xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
    </samlp:RequestedAuthnContext>
</samlp:AuthnRequest>
';
        $hash = Corto_XmlToHash::xml2hash($xml);

        $expectedHash = array (
  '__t' => 'samlp:AuthnRequest',
  '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
  '_ID' => '_bec424fa5103428909a30ff1e31168327f79474984',
  '_Version' => '2.0',
  '_IssueInstant' => '2007-12-10T11:39:34Z',
  '_ForceAuthn' => 'false',
  '_IsPassive' => 'false',
  '_ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
  '_AssertionConsumerServiceURL' => 'http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php',
  'saml:Issuer' =>
  array (
    '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
    '__v' => 'urn:mace:feide.no:services:no.feide.moodle',
  ),
  'samlp:NameIDPolicy' =>
  array (
    '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
    '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    '_SPNameQualifier' => 'moodle.bridge.feide.no',
    '_AllowCreate' => 'true',
  ),
  'samlp:RequestedAuthnContext' =>
  array (
    '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
    '_Comparison' => 'exact',
    'saml:AuthnContextClassRef' =>
    array (
      '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
      '__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
    ),
  ),
);
        $this->assertEquals($expectedHash, $hash, 'Example SAML 2.0 Authentication Request');
        $this->assertEquals($xml, Corto_XmlToHash::hash2xml($hash, '', true), "Example SAML 2.0 Authentication Request - converted to hash and back to xml again gives same result");

    }

    /**
     * Note that we are cheating a bit here, we've formatted the XML so it conforms with XMLWriters indentation policy.
     * Note also that XML can't canonicalise indentation, which makes it's use dangerous, but if you want to debug an XML message...
     *
     * @return void
     */
    public function testXmlToHashSampleAuthenticationResponse()
    {
        $xml = '<?xml version="1.0"?>
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b" InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984" Version="2.0" IssueInstant="2007-12-10T11:39:48Z" Destination="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">max.feide.no</saml:Issuer>
    <samlp:Status xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol">
        <samlp:StatusCode xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Version="2.0" ID="s2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab" IssueInstant="2007-12-10T11:39:48Z">
        <saml:Issuer>max.feide.no</saml:Issuer>
        <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
            <SignedInfo>
                <CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
                <Reference URI="#s2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab">
                    <Transforms>
                        <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
                        <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                    </Transforms>
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                    <DigestValue>k7z/t3iPKiyY9P7B87FIsMxnlnk=</DigestValue>
                </Reference>
            </SignedInfo>
            <SignatureValue>KvUrzGcwGsu8WMNogIRfAxxWlO4uKXhJrouOYaadkzUHvz1xbVURH35si6U8084utNAjXTjZyxfj qurEX7VgCw6Xn7Fxn4nJxD6FOP5x/iRk8KqCufipRNHwICq/VufqPkrP7sVLdymJyZ2Cu5QrEU23 qaIzjFf84Kfp4LVnlJY=</SignatureValue>
            <KeyInfo>
                <X509Data>
                    <X509Certificate>MIIB/jCCAWcCBEbzjNswDQYJKoZIhvcNAQEFBQAwRjELMAkGA1UEBhMCTk8xEDAOBgNVBAoTB1VO SU5FVFQxDjAMBgNVBAsTBUZlaWRlMRUwEwYDVQQDEwxtYXguZmVpZGUubm8wHhcNMDcwOTIxMDky MDI3WhcNMDcxMjIwMDkyMDI3WjBGMQswCQYDVQQGEwJOTzEQMA4GA1UEChMHVU5JTkVUVDEOMAwG A1UECxMFRmVpZGUxFTATBgNVBAMTDG1heC5mZWlkZS5ubzCBnzANBgkqhkiG9w0BAQEFAAOBjQAw gYkCgYEAvZlBzQ2jGM6Q9STBJ6tqtugkOBMEU/kpvvwOlT6c1X5UIXMwApL+NV2Eaqk+oA0N+M42 J7Sy0dLDqKVCwsh7qpsIYlDS/omyUMdy6AzvptRUUhLLhC6zQFFAU+6rcUKEiSkER5eziB4M3ae0 EkW0drm1rOZwb22tr8NJ65q3gnsCAwEAATANBgkqhkiG9w0BAQUFAAOBgQCmVSta9TWin/wvvGOi e8Cq7cEg0MJLkBWLofNNzrzh6hiQgfuz9KMom/kh9JuGEjyE7rIDbXp2ilxSHgZSaVfEkwnMfQ51 vuHUrtRolD/skysIocm+HJKbsmPMjSRfUFyzBh4RNjPoCvZvTdnyBfMP/i/H39njAdBRi+49aopc vw==</X509Certificate>
                </X509Data>
            </KeyInfo>
        </Signature>
        <saml:Subject>
            <saml:NameID NameQualifier="max.feide.no" SPNameQualifier="urn:mace:feide.no:services:no.feide.moodle" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">UB/WJAaKAPrSHbqlbcKWu7JktcKY</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData NotOnOrAfter="2007-12-10T19:39:48Z" InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984" Recipient="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php"/>
            </saml:SubjectConfirmation>
        </saml:Subject>
        <saml:Conditions NotBefore="2007-12-10T11:29:48Z" NotOnOrAfter="2007-12-10T19:39:48Z">
            <saml:AudienceRestriction>
                <saml:Audience>urn:mace:feide.no:services:no.feide.moodle</saml:Audience>
            </saml:AudienceRestriction>
        </saml:Conditions>
        <saml:AuthnStatement AuthnInstant="2007-12-10T11:39:48Z" SessionIndex="s259fad9cad0cf7d2b3b68f42b17d0cfa6668e0201">
            <saml:AuthnContext>
                <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef>
            </saml:AuthnContext>
        </saml:AuthnStatement>
        <saml:AttributeStatement>
            <saml:Attribute Name="givenName">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">RkVJREUgVGVzdCBVc2VyIChnaXZlbk5hbWUpIMO4w6bDpcOYw4bDhQ==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonPrincipalName">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">dGVzdEBmZWlkZS5ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="o">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">VU5JTkVUVA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="ou">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">VU5JTkVUVA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonOrgDN">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">ZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonPrimaryAffiliation">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">c3R1ZGVudA==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="mail">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">bW9yaWEtc3VwcG9ydEB1bmluZXR0Lm5v</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="preferredLanguage">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">bm8=</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonOrgUnitDN">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">b3U9dW5pbmV0dCxvdT1vcmdhbml6YXRpb24sZGM9dW5pbmV0dCxkYz1ubw==</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="sn">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">RkVJREUgVGVzdCBVc2VyIChzbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="cn">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">RkVJREUgVGVzdCBVc2VyIChjbikgw7jDpsOlw5jDhsOF</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="eduPersonAffiliation">
                <saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">ZW1wbG95ZWU=_c3RhZmY=_c3R1ZGVudA==</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>
';
        $hash = Corto_XmlToHash::xml2hash($xml);

        $expectedHash = array (
  '__t' => 'samlp:Response',
  '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
  '_ID' => 's2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b',
  '_InResponseTo' => '_bec424fa5103428909a30ff1e31168327f79474984',
  '_Version' => '2.0',
  '_IssueInstant' => '2007-12-10T11:39:48Z',
  '_Destination' => 'http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php',
  'saml:Issuer' =>
  array (
    '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
    '__v' => 'max.feide.no',
  ),
  'samlp:Status' =>
  array (
    '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
    'samlp:StatusCode' =>
    array (
      '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
      '_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
    ),
  ),
  'saml:Assertion' =>
  array (
    '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
    '_Version' => '2.0',
    '_ID' => 's2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab',
    '_IssueInstant' => '2007-12-10T11:39:48Z',
    'saml:Issuer' =>
    array (
      '__v' => 'max.feide.no',
    ),
    'Signature' =>
    array (
      '_xmlns' => 'http://www.w3.org/2000/09/xmldsig#',
      'SignedInfo' =>
      array (
        'CanonicalizationMethod' =>
        array (
          '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
        ),
        'SignatureMethod' =>
        array (
          '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
        ),
        'Reference' =>
        array (
          '_URI' => '#s2b7afe8e21a0910d027dfbc94ec4b862e1fbbd9ab',
          'Transforms' =>
          array (
            'Transform' =>
            array (
              array(
                  '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                  ),
              array(
                  '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
              ),
            ),
          ),
          'DigestMethod' =>
          array (
            '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
          ),
          'DigestValue' =>
          array (
            '__v' => 'k7z/t3iPKiyY9P7B87FIsMxnlnk=',
          ),
        ),
      ),
      'SignatureValue' =>
      array (
        '__v' => 'KvUrzGcwGsu8WMNogIRfAxxWlO4uKXhJrouOYaadkzUHvz1xbVURH35si6U8084utNAjXTjZyxfj qurEX7VgCw6Xn7Fxn4nJxD6FOP5x/iRk8KqCufipRNHwICq/VufqPkrP7sVLdymJyZ2Cu5QrEU23 qaIzjFf84Kfp4LVnlJY=',
      ),
      'KeyInfo' =>
      array (
        'X509Data' =>
        array (
          'X509Certificate' =>
          array (
            '__v' => 'MIIB/jCCAWcCBEbzjNswDQYJKoZIhvcNAQEFBQAwRjELMAkGA1UEBhMCTk8xEDAOBgNVBAoTB1VO SU5FVFQxDjAMBgNVBAsTBUZlaWRlMRUwEwYDVQQDEwxtYXguZmVpZGUubm8wHhcNMDcwOTIxMDky MDI3WhcNMDcxMjIwMDkyMDI3WjBGMQswCQYDVQQGEwJOTzEQMA4GA1UEChMHVU5JTkVUVDEOMAwG A1UECxMFRmVpZGUxFTATBgNVBAMTDG1heC5mZWlkZS5ubzCBnzANBgkqhkiG9w0BAQEFAAOBjQAw gYkCgYEAvZlBzQ2jGM6Q9STBJ6tqtugkOBMEU/kpvvwOlT6c1X5UIXMwApL+NV2Eaqk+oA0N+M42 J7Sy0dLDqKVCwsh7qpsIYlDS/omyUMdy6AzvptRUUhLLhC6zQFFAU+6rcUKEiSkER5eziB4M3ae0 EkW0drm1rOZwb22tr8NJ65q3gnsCAwEAATANBgkqhkiG9w0BAQUFAAOBgQCmVSta9TWin/wvvGOi e8Cq7cEg0MJLkBWLofNNzrzh6hiQgfuz9KMom/kh9JuGEjyE7rIDbXp2ilxSHgZSaVfEkwnMfQ51 vuHUrtRolD/skysIocm+HJKbsmPMjSRfUFyzBh4RNjPoCvZvTdnyBfMP/i/H39njAdBRi+49aopc vw==',
          ),
        ),
      ),
    ),
    'saml:Subject' =>
    array (
      'saml:NameID' =>
      array (
        '_NameQualifier' => 'max.feide.no',
        '_SPNameQualifier' => 'urn:mace:feide.no:services:no.feide.moodle',
        '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        '__v' => 'UB/WJAaKAPrSHbqlbcKWu7JktcKY',
      ),
      'saml:SubjectConfirmation' =>
      array (
        '_Method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
        'saml:SubjectConfirmationData' =>
        array (
          '_NotOnOrAfter' => '2007-12-10T19:39:48Z',
          '_InResponseTo' => '_bec424fa5103428909a30ff1e31168327f79474984',
          '_Recipient' => 'http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php',
        ),
      ),
    ),
    'saml:Conditions' =>
    array (
      '_NotBefore' => '2007-12-10T11:29:48Z',
      '_NotOnOrAfter' => '2007-12-10T19:39:48Z',
      'saml:AudienceRestriction' =>
      array (
        'saml:Audience' =>
        array (
          '__v' => 'urn:mace:feide.no:services:no.feide.moodle',
        ),
      ),
    ),
    'saml:AuthnStatement' =>
    array (
      '_AuthnInstant' => '2007-12-10T11:39:48Z',
      '_SessionIndex' => 's259fad9cad0cf7d2b3b68f42b17d0cfa6668e0201',
      'saml:AuthnContext' =>
      array (
        'saml:AuthnContextClassRef' =>
        array (
          '__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password',
        ),
      ),
    ),
    'saml:AttributeStatement' =>
    array (
      'saml:Attribute' =>
      array (
        0 =>
        array (
          '_Name' => 'givenName',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'RkVJREUgVGVzdCBVc2VyIChnaXZlbk5hbWUpIMO4w6bDpcOYw4bDhQ==',
            ),
          ),
        ),
        1 =>
        array (
          '_Name' => 'eduPersonPrincipalName',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'dGVzdEBmZWlkZS5ubw==',
            ),
          ),
        ),
        2 =>
        array (
          '_Name' => 'o',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'VU5JTkVUVA==',
            ),
          ),
        ),
        3 =>
        array (
          '_Name' => 'ou',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'VU5JTkVUVA==',
            ),
          ),
        ),
        4 =>
        array (
          '_Name' => 'eduPersonOrgDN',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'ZGM9dW5pbmV0dCxkYz1ubw==',
            ),
          ),
        ),
        5 =>
        array (
          '_Name' => 'eduPersonPrimaryAffiliation',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'c3R1ZGVudA==',
            ),
          ),
        ),
        6 =>
        array (
          '_Name' => 'mail',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'bW9yaWEtc3VwcG9ydEB1bmluZXR0Lm5v',
            ),
          ),
        ),
        7 =>
        array (
          '_Name' => 'preferredLanguage',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'bm8=',
            ),
          ),
        ),
        8 =>
        array (
          '_Name' => 'eduPersonOrgUnitDN',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'b3U9dW5pbmV0dCxvdT1vcmdhbml6YXRpb24sZGM9dW5pbmV0dCxkYz1ubw==',
            ),
          ),
        ),
        9 =>
        array (
          '_Name' => 'sn',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'RkVJREUgVGVzdCBVc2VyIChzbikgw7jDpsOlw5jDhsOF',
            ),
          ),
        ),
        10 =>
        array (
          '_Name' => 'cn',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'RkVJREUgVGVzdCBVc2VyIChjbikgw7jDpsOlw5jDhsOF',
            ),
          ),
        ),
        11 =>
        array (
          '_Name' => 'eduPersonAffiliation',
          'saml:AttributeValue' =>
          array (
            0 =>
            array (
              '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
              '__v' => 'ZW1wbG95ZWU=_c3RhZmY=_c3R1ZGVudA==',
            ),
          ),
        ),
      ),
    ),
  ),
);

        $this->assertEquals($expectedHash, $hash, "Example SAML 2.0 Authentication Response");
        $this->assertEquals($xml, Corto_XmlToHash::hash2xml($hash, '', true), "Example SAML 2.0 Authentication Response - converted to hash and back to xml again gives same result");
    }

    public function testHashToXml()
    {
        $this->markTestIncomplete('Test hash to XML');
    }

    public function testAttributesToHash()
    {
        $this->markTestIncomplete('Test attributes to hash');
    }

    public function testHashToAttributes()
    {
        $this->markTestIncomplete('Test hash to attributes');
    }
}