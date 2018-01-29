<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\Component\StokerMetadata\MetadataIndex\Entity;
use PHPUnit_Framework_TestCase;

/**
 * Class JanusRestV1Assembler
 * @package OpenConext\EngineBlock\Metadata\Entity\Translator
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class StokerAssemblerTest extends PHPUnit_Framework_TestCase
{
    public function testIdp()
    {
        $xml = <<<XML
<md:EntityDescriptor xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                     xmlns:shibmd="urn:mace:shibboleth:metadata:1.0"
                     xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     entityID="https://idp-cafe.ifrr.edu.br/idp/shibboleth">
    <md:Extensions>
      <mdrpi:RegistrationInfo registrationAuthority="http://cafe.rnp.br" registrationInstant="2010-01-01T00:00:00Z">
        <mdrpi:RegistrationPolicy xml:lang="pt-br">
                                http://www.rnp.br/_arquivo/servicos/Politica_CAFe_IDP_final.pdf
                              </mdrpi:RegistrationPolicy>
      </mdrpi:RegistrationInfo>
    </md:Extensions>
    <md:IDPSSODescriptor
        protocolSupportEnumeration="
            urn:mace:shibboleth:1.0
            urn:oasis:names:tc:SAML:1.1:protocol
            urn:oasis:names:tc:SAML:2.0:protocol">
      <md:Extensions>
        <shibmd:Scope regexp="false">edu.br</shibmd:Scope>
        <mdui:UIInfo>
          <mdui:DisplayName xml:lang="en">IFRR - Instituto Federal de Roraima</mdui:DisplayName>
          <mdui:DisplayName xml:lang="pt-br">IFRR - Instituto Federal de Roraima</mdui:DisplayName>
          <mdui:Description xml:lang="en">IFRR - Instituto Federal de Roraima</mdui:Description>
          <mdui:Description xml:lang="pt-br">IFRR - Instituto Federal de Roraima</mdui:Description>
          <mdui:InformationURL xml:lang="en">http://www.ifrr.edu.br</mdui:InformationURL>
          <mdui:InformationURL xml:lang="pt-br">http://www.ifrr.edu.br</mdui:InformationURL>
        </mdui:UIInfo>
      </md:Extensions>
      <md:KeyDescriptor>
        <ds:KeyInfo>
          <ds:X509Data>
            <ds:X509Certificate>
    MIIC0DCCAbgCAQAwDQYJKoZIhvcNAQEFBQAwLjEMMAoGA1UECxMDRFRJMQswCQYD
VQQGEwJCUjERMA8GA1UEAxMIaWRwLWNhZmUwHhcNMTQwNDA5MTYyNzU1WhcNMTkw
NDA4MTYyNzU1WjAuMQwwCgYDVQQLEwNEVEkxCzAJBgNVBAYTAkJSMREwDwYDVQQD
EwhpZHAtY2FmZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAM2QLiQp
v/NepaRdzlGTeoyC//4t4vPsByuVLTF9BJ9zXfpgb70bFt5Ka4FXaYEafGC76n8H
uCzk/nWrzEKuNMZMPgQbteCpvhrGm8RziPF7zelZl2Q4FDAORAOQwfw/GIyadsmY
38BYCGQmuppiCAL5SUvGBHvEPyGwJnLBqnE/f1e66ZXfLvniYqRjKvU1VSsUUF5Y
BrVE/LqUB6OA7uJEKTtp6uCx8XNUNYGML6hHR9joDZrQuK4wYOocjdWuWn0/1/uw
hvb8sOYiTgAPap4UtTxTqVZ6XBRWmWVP9fTEjpSNEgeaWBvNpmJx+Ci9Hi5+F0zq
GZMyVBZZAXsBBj0CAwEAATANBgkqhkiG9w0BAQUFAAOCAQEAl6toGY7toL1HPVtj
pjGi7ed76phnITf4cHJi6Ny99tsCK29pdkWKGQ4p1F8usGy8QoWCBBLTAo/LhZye
/AzLnW+zUV9qioGw+guncG8/GCg8SM2FR2nwUt2BXsXUodHg2kl8S8I7NbXcw9p1
oI8OT1fkM3RcJ5F4XBErp9PI9KK5zaLGYjDgCOm4m09GmIuPbzlIBlx77B8+WZXA
CX6Zy+sNmrpOzcg/UDotLp5yCOWlJQkcNH4U4gqCMiwbpys3zMLPjKcH+N2th9MB
DqEeFHCPJJZZTT+MFcUMt4fMrYZ2EKfNZnLbO0cvTEmLXDTxEVU/m9n41DGtxSF0
NMSiiw==
            </ds:X509Certificate>
          </ds:X509Data>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:ArtifactResolutionService
        Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
        Location="https://idp-cafe.ifrr.edu.br:8443/idp/profile/SAML2/SOAP/ArtifactResolution"
        index="2"/>
      <md:ArtifactResolutionService
        Binding="urn:oasis:names:tc:SAML:1.0:bindings:SOAP-binding"
        Location="https://idp-cafe.ifrr.edu.br:8443/idp/profile/SAML1/SOAP/ArtifactResolution"
        index="1"/>
      <md:NameIDFormat>urn:mace:shibboleth:1.0:nameIdentifier</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
      <md:SingleSignOnService   Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign"
                                Location="https://idp-cafe.ifrr.edu.br/idp/profile/SAML2/POST-SimpleSign/SSO"/>
      <md:SingleSignOnService   Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                Location="https://idp-cafe.ifrr.edu.br/idp/profile/SAML2/POST/SSO"/>
      <md:SingleSignOnService   Binding="urn:mace:shibboleth:1.0:profiles:AuthnRequest"
                                Location="https://idp-cafe.ifrr.edu.br/idp/profile/Shibboleth/SSO"/>
      <md:SingleSignOnService   Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://idp-cafe.ifrr.edu.br/idp/profile/SAML2/Redirect/SSO"/>
    </md:IDPSSODescriptor>
    <md:AttributeAuthorityDescriptor
      protocolSupportEnumeration="urn:oasis:names:tc:SAML:1.1:protocol urn:oasis:names:tc:SAML:2.0:protocol">
      <md:Extensions>
        <shibmd:Scope regexp="false">edu.br</shibmd:Scope>
      </md:Extensions>
      <md:KeyDescriptor>
        <ds:KeyInfo>
          <ds:X509Data>
            <ds:X509Certificate>

MIIC0DCCAbgCAQAwDQYJKoZIhvcNAQEFBQAwLjEMMAoGA1UECxMDRFRJMQswCQYD
VQQGEwJCUjERMA8GA1UEAxMIaWRwLWNhZmUwHhcNMTQwNDA5MTYyNzU1WhcNMTkw
NDA4MTYyNzU1WjAuMQwwCgYDVQQLEwNEVEkxCzAJBgNVBAYTAkJSMREwDwYDVQQD
EwhpZHAtY2FmZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAM2QLiQp
v/NepaRdzlGTeoyC//4t4vPsByuVLTF9BJ9zXfpgb70bFt5Ka4FXaYEafGC76n8H
uCzk/nWrzEKuNMZMPgQbteCpvhrGm8RziPF7zelZl2Q4FDAORAOQwfw/GIyadsmY
38BYCGQmuppiCAL5SUvGBHvEPyGwJnLBqnE/f1e66ZXfLvniYqRjKvU1VSsUUF5Y
BrVE/LqUB6OA7uJEKTtp6uCx8XNUNYGML6hHR9joDZrQuK4wYOocjdWuWn0/1/uw
hvb8sOYiTgAPap4UtTxTqVZ6XBRWmWVP9fTEjpSNEgeaWBvNpmJx+Ci9Hi5+F0zq
GZMyVBZZAXsBBj0CAwEAATANBgkqhkiG9w0BAQUFAAOCAQEAl6toGY7toL1HPVtj
pjGi7ed76phnITf4cHJi6Ny99tsCK29pdkWKGQ4p1F8usGy8QoWCBBLTAo/LhZye
/AzLnW+zUV9qioGw+guncG8/GCg8SM2FR2nwUt2BXsXUodHg2kl8S8I7NbXcw9p1
oI8OT1fkM3RcJ5F4XBErp9PI9KK5zaLGYjDgCOm4m09GmIuPbzlIBlx77B8+WZXA
CX6Zy+sNmrpOzcg/UDotLp5yCOWlJQkcNH4U4gqCMiwbpys3zMLPjKcH+N2th9MB
DqEeFHCPJJZZTT+MFcUMt4fMrYZ2EKfNZnLbO0cvTEmLXDTxEVU/m9n41DGtxSF0
NMSiiw==

            </ds:X509Certificate>
          </ds:X509Data>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://idp-cafe.ifrr.edu.br:8443/idp/profile/SAML2/SOAP/AttributeQuery"/>
      <md:AttributeService Binding="urn:oasis:names:tc:SAML:1.0:bindings:SOAP-binding" Location="https://idp-cafe.ifrr.edu.br:8443/idp/profile/SAML1/SOAP/AttributeQuery"/>
      <md:NameIDFormat>urn:mace:shibboleth:1.0:nameIdentifier</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
    </md:AttributeAuthorityDescriptor>
    <md:Organization>
      <md:OrganizationName xml:lang="en">IFRR - Instituto Federal de Roraima</md:OrganizationName>
      <md:OrganizationName xml:lang="pt-br">IFRR - Instituto Federal de Roraima</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en">IFRR - Instituto Federal de Roraima</md:OrganizationDisplayName>
      <md:OrganizationDisplayName xml:lang="pt-br">IFRR - Instituto Federal de Roraima</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en">http://www.ifrr.edu.br</md:OrganizationURL>
      <md:OrganizationURL xml:lang="pt-br">http://www.ifrr.edu.br</md:OrganizationURL>
    </md:Organization>
    <md:ContactPerson contactType="technical">
      <md:SurName>Francisco Cavalcante Filho</md:SurName>
      <md:EmailAddress>francisco.filho@ifrr.edu.br</md:EmailAddress>
    </md:ContactPerson>
  </md:EntityDescriptor>
XML;

        $entity = new Entity(
            'https://idp-cafe.ifrr.edu.br/idp/shibboleth',
            array(Entity::TYPE_IDP),
            'IFRR - Instituto Federal de Roraima',
            'IFRR - Instituto Federal de Roraima'
        );

        $assembler = new StokerAssembler();
        $role = $assembler->assemble($xml, $entity);
        $this->assertTrue($role instanceof IdentityProvider);
        $this->assertNull($role->organizationEn);
        $this->assertNull($role->organizationNl);
        $this->assertEmpty($role->contactPersons);
        $this->assertEquals($role->displayNameNl, $entity->displayNameNl);
        $this->assertEquals($role->displayNameEn, $entity->displayNameEn);
    }

    public function testSp()
    {
        $xml = <<<XML
<md:EntityDescriptor xmlns:idpdisc="urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol"
                     xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     entityID="https://admin.webfiltering.ja.net/shibboleth-sp">
    <md:Extensions>
      <mdrpi:RegistrationInfo registrationAuthority="http://ukfederation.org.uk"/>
    </md:Extensions>
    <md:SPSSODescriptor protocolSupportEnumeration="
                            urn:oasis:names:tc:SAML:2.0:protocol
                            urn:oasis:names:tc:SAML:1.1:protocol
                            urn:oasis:names:tc:SAML:1.0:protocol">
      <md:Extensions>
        <idpdisc:DiscoveryResponse Binding="urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol"
                                   Location="https://admin.webfiltering.ja.net/Shibboleth.sso/DS"
                                   index="1"/>
      </md:Extensions>
      <md:KeyDescriptor use="signing">
        <ds:KeyInfo>
          <ds:X509Data>
            <ds:X509Certificate>
						MIIDGDCCAgCgAwIBAgIJAIDKIKpmE2tBMA0GCSqGSIb3DQEBBQUAMCYxJDAiBgNV
						BAMTG2FkbWluLnNhZmV0eW5ldC5ybXBsYy5jby51azAeFw0xMDA0MDgxNjE2MTha
						Fw0yMDA0MDUxNjE2MThaMCYxJDAiBgNVBAMTG2FkbWluLnNhZmV0eW5ldC5ybXBs
						Yy5jby51azCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALyO69kuQy4+
						R7W0KgA21wY7EnbY2qr8K1AKfX87jvsfDX5Jg+XwifaI/8f8sIju9jlgg0S2ju+M
						uWQyCYV6cCMp790MEXXaOR0PiAkrallrkYg/+FbP7+flmOrPhj6UWCAc80ZWJ8wx
						U6Un+UpDpmhRrQWjOn0sOW6PvkbZlRrEtW6XJ2PfhOCHHfAJIcB/r4frQFvmGBk1
						6e24W46xEPi2gznxM9MRm/MMBnFHBzHlpgu+QmL+gBI6OQ0Gr1nmZgcTJqEJCahk
						dp51SjegGKmXXWAdV7pGYkoRrwYH6TryXNaHUXQn0zoS+aa1kZIf0dVp0S508go6
						aqwe92WWsAcCAwEAAaNJMEcwJgYDVR0RBB8wHYIbYWRtaW4uc2FmZXR5bmV0LnJt
						cGxjLmNvLnVrMB0GA1UdDgQWBBTiUv6qHBxNTHW11t8mP9SXPRvMmjANBgkqhkiG
						9w0BAQUFAAOCAQEAhVafLlFnznxyNY5Zls8IiCN2kAqwUN5iBuVx2Q749LcVHlGy
						NtTZVpLWcrZsklTl2mBJuGI5XjqjuEu/UJtaRx0p6oC32Pg2R8/McMcJjLtbwA9X
						pIZsuOfARLq0AFS9ncrgZtVrua9YErJSWSCUkzZgJTu/1Aelc1Dlojdmp22GH4Rn
						Fr1hRdDgtzpAwdBpxPdai7Pdaf47QVRSgfDjCuJ2zJopboYAbnk6UAuiLWDuLtcB
						o8UiVcMPP/vvRio3IzTVoEKAmb8/EENkXRSR+we7CKNAQhZBvTvC2oWruki4UmMK
						aFvLcsb+dVUZ5xAnv9teHQR18B9PVW+1Se6k3A==
					</ds:X509Certificate>
          </ds:X509Data>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:KeyDescriptor use="encryption">
        <ds:KeyInfo>
          <ds:X509Data>
            <ds:X509Certificate>
						MIIDGDCCAgCgAwIBAgIJAIDKIKpmE2tBMA0GCSqGSIb3DQEBBQUAMCYxJDAiBgNV
						BAMTG2FkbWluLnNhZmV0eW5ldC5ybXBsYy5jby51azAeFw0xMDA0MDgxNjE2MTha
						Fw0yMDA0MDUxNjE2MThaMCYxJDAiBgNVBAMTG2FkbWluLnNhZmV0eW5ldC5ybXBs
						Yy5jby51azCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALyO69kuQy4+
						R7W0KgA21wY7EnbY2qr8K1AKfX87jvsfDX5Jg+XwifaI/8f8sIju9jlgg0S2ju+M
						uWQyCYV6cCMp790MEXXaOR0PiAkrallrkYg/+FbP7+flmOrPhj6UWCAc80ZWJ8wx
						U6Un+UpDpmhRrQWjOn0sOW6PvkbZlRrEtW6XJ2PfhOCHHfAJIcB/r4frQFvmGBk1
						6e24W46xEPi2gznxM9MRm/MMBnFHBzHlpgu+QmL+gBI6OQ0Gr1nmZgcTJqEJCahk
						dp51SjegGKmXXWAdV7pGYkoRrwYH6TryXNaHUXQn0zoS+aa1kZIf0dVp0S508go6
						aqwe92WWsAcCAwEAAaNJMEcwJgYDVR0RBB8wHYIbYWRtaW4uc2FmZXR5bmV0LnJt
						cGxjLmNvLnVrMB0GA1UdDgQWBBTiUv6qHBxNTHW11t8mP9SXPRvMmjANBgkqhkiG
						9w0BAQUFAAOCAQEAhVafLlFnznxyNY5Zls8IiCN2kAqwUN5iBuVx2Q749LcVHlGy
						NtTZVpLWcrZsklTl2mBJuGI5XjqjuEu/UJtaRx0p6oC32Pg2R8/McMcJjLtbwA9X
						pIZsuOfARLq0AFS9ncrgZtVrua9YErJSWSCUkzZgJTu/1Aelc1Dlojdmp22GH4Rn
						Fr1hRdDgtzpAwdBpxPdai7Pdaf47QVRSgfDjCuJ2zJopboYAbnk6UAuiLWDuLtcB
						o8UiVcMPP/vvRio3IzTVoEKAmb8/EENkXRSR+we7CKNAQhZBvTvC2oWruki4UmMK
						aFvLcsb+dVUZ5xAnv9teHQR18B9PVW+1Se6k3A==
					</ds:X509Certificate>
          </ds:X509Data>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:ArtifactResolutionService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/Artifact/SOAP"
                                    index="1"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SLO/SOAP"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SLO/Redirect"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SLO/POST"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SLO/Artifact"/>
      <md:ManageNameIDService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/NIM/SOAP"/>
      <md:ManageNameIDService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/NIM/Redirect"/>
      <md:ManageNameIDService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/NIM/POST"/>
      <md:ManageNameIDService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
                              Location="https://admin.webfiltering.ja.net/Shibboleth.sso/NIM/Artifact"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML2/POST"
                                    index="1"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML2/POST-SimpleSign"
                                    index="2"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML2/Artifact"
                                    index="3"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:2.0:bindings:PAOS"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML2/ECP"
                                    index="4"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:1.0:profiles:browser-post"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML/POST"
                                    index="5"/>
      <md:AssertionConsumerService  Binding="urn:oasis:names:tc:SAML:1.0:profiles:artifact-01"
                                    Location="https://admin.webfiltering.ja.net/Shibboleth.sso/SAML/Artifact"
                                    index="6"/>
    </md:SPSSODescriptor>
    <md:Organization>
      <md:OrganizationName xml:lang="en">RM Education plc</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en">RM Education plc: RM Safetynet JANET</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en">http://www.rm.com/</md:OrganizationURL>
    </md:Organization>
    <md:ContactPerson contactType="support">
      <md:GivenName>Shibboleth Support Contact</md:GivenName>
      <md:EmailAddress>mailto:shibsupport@ifl.net</md:EmailAddress>
    </md:ContactPerson>
    <md:ContactPerson contactType="technical">
      <md:GivenName>Shibboleth Technical Contact</md:GivenName>
      <md:EmailAddress>mailto:shibtechcontact@ifl.net</md:EmailAddress>
    </md:ContactPerson>
  </md:EntityDescriptor>
XML;

        $entity = new Entity(
            'https://admin.webfiltering.ja.net/shibboleth-sp',
            array(Entity::TYPE_SP),
            'admin.webfiltering.ja.net',
            'admin.webfiltering.ja.net'
        );

        $assembler = new StokerAssembler();
        $role = $assembler->assemble($xml, $entity);
        $this->assertTrue($role instanceof ServiceProvider);
        $this->assertNull($role->organizationEn);
        $this->assertNull($role->organizationNl);
        $this->assertEmpty($role->contactPersons);
        $this->assertEquals($role->displayNameNl, $entity->displayNameNl);
        $this->assertEquals($role->displayNameEn, $entity->displayNameEn);
    }
}
