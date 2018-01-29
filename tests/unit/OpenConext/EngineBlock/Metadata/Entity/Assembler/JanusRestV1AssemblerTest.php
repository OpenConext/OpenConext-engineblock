<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use SAML2_Const;

/**
 * Class JanusRestV1Assembler
 * @package OpenConext\EngineBlock\Metadata\Entity\Translator
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class JanusRestV1AssemblerTest extends PHPUnit_Framework_TestCase
{
    public function testAssembleEmptyEntityInfo()
    {
        $entity = array();

        $assembler = new JanusRestV1Assembler();
        $this->assertNull($assembler->assemble('https://entityId', $entity));
    }

    public function testAssembleServiceProvider()
    {
        $entity = json_decode('{
    "AssertionConsumerService:0:Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
    "AssertionConsumerService:0:index": 0,
    "AssertionConsumerService:0:Location":
        "https://serviceregistry.surfconext.nl/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
    "AssertionConsumerService:1:Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact",
    "AssertionConsumerService:1:index": 2,
    "AssertionConsumerService:1:Location":
        "https://serviceregistry.surfconext.nl/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
    "contacts:0:contactType": "technical",
    "contacts:1:contactType": "technical",
    "contacts:2:contactType": "technical",
    "logo:0:height": 60,
    "logo:0:url": "https://.png",
    "logo:0:width": 120,
    "NameIDFormat": "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent",
    "redirect.sign": false,
    "coin:signature_method":"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256",
    "workflowState": "prodaccepted"
}', true);

        if ($entity === null) {
            throw new RuntimeException('Unable to decode JSON');
        }

        $assembler = new JanusRestV1Assembler();
        $serviceProvider = $assembler->assemble(
            'https://serviceregistry.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp',
            $entity
        );
        $this->assertTrue($serviceProvider instanceof ServiceProvider);
        $this->assertCount(2, $serviceProvider->assertionConsumerServices);

        $this->assertEquals(SAML2_Const::BINDING_HTTP_POST, $serviceProvider->assertionConsumerServices[0]->binding);
        $this->assertEquals(0, $serviceProvider->assertionConsumerServices[0]->serviceIndex);
        $this->assertEquals(
            'https://serviceregistry.surfconext.nl/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
            $serviceProvider->assertionConsumerServices[0]->location
        );
        $this->assertNull($serviceProvider->assertionConsumerServices[0]->isDefault);
        $this->assertEquals(
            SAML2_Const::BINDING_HTTP_ARTIFACT,
            $serviceProvider->assertionConsumerServices[1]->binding
        );
        // Note that even though we expect the Service Index to be the value of :index
        // it's not. We don't use that. See: https://github.com/OpenConext/OpenConext-engineblock/issues/106
        $this->assertEquals(1, $serviceProvider->assertionConsumerServices[1]->serviceIndex);
        $this->assertEquals(
            'https://serviceregistry.surfconext.nl/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
            $serviceProvider->assertionConsumerServices[1]->location
        );
        $this->assertCount(3, $serviceProvider->contactPersons);
        $this->assertEquals('technical', $serviceProvider->contactPersons[0]->contactType);
        $this->assertEmpty($serviceProvider->contactPersons[0]->emailAddress);
        $this->assertEmpty($serviceProvider->contactPersons[0]->givenName);
        $this->assertEmpty($serviceProvider->contactPersons[0]->surName);
        $this->assertEquals('technical', $serviceProvider->contactPersons[1]->contactType);
        $this->assertEquals('technical', $serviceProvider->contactPersons[2]->contactType);
        $this->assertNotNull($serviceProvider->logo);
        $this->assertEquals(60, $serviceProvider->logo->height);
        $this->assertEquals(120, $serviceProvider->logo->width);
        $this->assertEquals('https://.png', $serviceProvider->logo->url);
        $this->assertEquals(SAML2_Const::NAMEID_PERSISTENT, $serviceProvider->nameIdFormat);
        $this->assertFalse($serviceProvider->requestsMustBeSigned);
        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $serviceProvider->signatureMethod);
        $this->assertEquals(AbstractRole::WORKFLOW_STATE_PROD, $serviceProvider->workflowState);
    }

    public function testEmptyIdentityProvider()
    {
        $entity = json_decode('{
            "certData":"",
            "coin:guest_qualifier":"All",
            "contacts:0:contactType":"technical",
            "contacts:0:emailAddress":"",
            "contacts:0:givenName":"",
            "contacts:0:surName":"",
            "contacts:1:contactType":"technical",
            "contacts:1:emailAddress":"",
            "contacts:1:givenName":"",
            "contacts:1:surName":"",
            "contacts:2:contactType":"technical",
            "contacts:2:emailAddress":"",
            "contacts:2:givenName":"",
            "contacts:2:surName":"",
            "description:en":"",
            "description:nl":"",
            "name:en":"",
            "name:nl":"",
            "OrganizationDisplayName:nl":"",
            "redirect.sign":false,
            "coin:signature_method":"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256",
            "SingleSignOnService:0:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
            "SingleSignOnService:0:Location":"",
            "UIInfo:Keywords:en":"",
            "UIInfo:Keywords:nl":"",
            "workflowState":"testaccepted"
        }', true);

        $assembler = new JanusRestV1Assembler();
        $identityProvider = $assembler->assemble(
            '2',
            $entity
        );
        $this->assertTrue($identityProvider instanceof IdentityProvider);
        $this->assertEmpty($identityProvider->certificates);
        $this->assertEquals(IdentityProvider::GUEST_QUALIFIER_ALL, $identityProvider->guestQualifier);
        $this->assertCount(3, $identityProvider->contactPersons);
        $this->assertEmpty($identityProvider->descriptionEn);
        $this->assertEmpty($identityProvider->descriptionNl);
        $this->assertEmpty($identityProvider->nameNl);
        $this->assertEmpty($identityProvider->nameEn);
        $this->assertNull($identityProvider->organizationEn);
        $this->assertNull($identityProvider->organizationNl);
        $this->assertFalse($identityProvider->requestsMustBeSigned);
        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $identityProvider->signatureMethod);
        $this->assertEmpty($identityProvider->singleSignOnServices);
        $this->assertEmpty($identityProvider->keywordsEn);
        $this->assertEmpty($identityProvider->keywordsNl);
        $this->assertEquals(IdentityProvider::WORKFLOW_STATE_TEST, $identityProvider->workflowState);
    }

    public function testFullSp()
    {
        $certData = 'MIIDkzCCAnugAwIBAgIJAOeliBIos1WCMA0GCSqGSIb3DQEBBQUAMGAxCzAJBgNV'
            . 'BAYTAk5MMQswCQYDVQQIDAJVVDEQMA4GA1UEBwwHVXRyZWNodDEVMBMGA1UECgwM'
            . 'U1VSRm5ldCBCLlYuMRswGQYDVQQDDBJpZHAtYWNjLnN1cmZuZXQubmwwHhcNMTMw'
            . 'MzEzMTYyOTEzWhcNMzMwMzEyMTYyOTEzWjBgMQswCQYDVQQGEwJOTDELMAkGA1UE'
            . 'CAwCVVQxEDAOBgNVBAcMB1V0cmVjaHQxFTATBgNVBAoMDFNVUkZuZXQgQi5WLjEb'
            . 'MBkGA1UEAwwSaWRwLWFjYy5zdXJmbmV0Lm5sMIIBIjANBgkqhkiG9w0BAQEFAAOC'
            . 'AQ8AMIIBCgKCAQEA50175NSolQmWRx4sK/wO5Nr4KckGyka1GA3fpMXDoQFBSKi6'
            . '1QFF2LfPphufhJ1mtXoTH6VEda5urjaKz6OSIVUHBzEXrwtd7UVV/UD0R3Y6J8hs'
            . 'oaMRzq2HIc2cVoh8t1NIhZfb3qVIXvP6KmqJ48jwCgx3TtaEJeAQBmDUcylVX4h8'
            . 'RhwgatEMGJSmwVjJPfPJhJ/gNunIL8bJxyUfBWLsZZONzGIxDh7jvCn2t3qm4i4o'
            . 'qLTILs7/il94MLV0PRRPrNG3aqSmhvkO+j9xBA6MtxQPPJJD1Od9fitkaW8bqW9R'
            . '49Oi136hsglDeKpxZ5SSNswA/L5NpyOfvJTqGwIDAQABo1AwTjAdBgNVHQ4EFgQU'
            . 'QjR8YC059M+/ntOrB+TjbSUFXqwwHwYDVR0jBBgwFoAUQjR8YC059M+/ntOrB+Tj'
            . 'bSUFXqwwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAUscvvekGWAEB'
            . 'hTJAIey1lrVGX5pGHmwkuajo/r3N1He02y6T3qXmnf0rnvBmYvSqTxQnccvmWEkR'
            . 'MXk1LkFf2erQXKF9W4fln9d/eoV+8XaiZCJCszAp9lFIzpLelL6HK18AOslr4G2x'
            . 'p9ooCj0vp4ja4NA7n/g/pZ/9N2zrTY9VsX3bp9e0XsauF92HWaEnBM/Tiq2BQBBk'
            . 'pqPmXOGde+uXC4ipk9GXHm7ZVTYCOuPKMoLk87H6yzRqoVe1XZvYY9dYIT0YJxxj'
            . 'bLAetQmU33PYPOJWfnKzRLQI5yNQgWFAKf9KTcc6N2gI6NkB3CHqMwcH0mdapriq'
            . '+lCiaVxdZw==';

        $entity = json_decode('{
        "AssertionConsumerService:0:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
        "AssertionConsumerService:0:index":0,
        "AssertionConsumerService:0:Location":
          "https://perftestsp.dev.surfconext.nl/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
        "AssertionConsumerService:1:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
        "AssertionConsumerService:1:index":1,
        "AssertionConsumerService:1:Location":"https://example.edu",
        "AssertionConsumerService:2:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
        "AssertionConsumerService:2:index":2,
        "AssertionConsumerService:2:Location":"https://example.edu",
        "AssertionConsumerService:3:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
        "AssertionConsumerService:3:index":3,
        "AssertionConsumerService:3:Location":"https://example.edu",
        "AssertionConsumerService:5:Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
        "AssertionConsumerService:5:index":5,
        "AssertionConsumerService:5:Location":"https://example.edu",
        "certData":"' . $certData . '",
        "certData2":"' . $certData . '",
        "certData3":"' . $certData . '",
        "coin:additional_logging":true,
        "coin:alternate_private_key":"' . $certData . '",
        "coin:alternate_public_key":"' . $certData . '",
        "coin:application_url":"Test application URL",
        "coin:display_unconnected_idps_wayf":true,
        "coin:do_not_add_attribute_aliases":true,
        "coin:eula":"https://example.edu/eula",
        "coin:gadgetbaseurl":"https://example.edu",
        "coin:institution_id":"test",
        "coin:is_provision_sp":true,
        "coin:is_provision_sp_groups":true,
        "coin:no_consent_required":true,
        "coin:oauth:app_description":"App description",
        "coin:oauth:app_icon":"https://www.surfnet.nl/icon.gif",
        "coin:oauth:app_thumbnail":"https://www.surfnet.nl/thumb.png",
        "coin:oauth:app_title":"App title",
        "coin:oauth:callback_url":"https://example.edu",
        "coin:oauth:consent_not_required":true,
        "coin:oauth:consumer_key":"consumer key",
        "coin:oauth:consumer_secret":"consumer secret",
        "coin:oauth:key_type":"HMAC_SHA1",
        "coin:oauth:public_key":"' . $certData . '",
        "coin:oauth:secret":"secret",
        "coin:oauth:two_legged_allowed":true,
        "coin:provide_is_member_of":true,
        "coin:provision_admin":"admin",
        "coin:provision_domain":"domain",
        "coin:provision_password":"password",
        "coin:provision_type":"google",
        "coin:publish_in_edugain":true,
        "coin:publish_in_edugain_date":"2012-09-1",
        "coin:ss:idp_visible_only":true,
        "coin:transparant_issuer":true,
        "coin:trusted_proxy":true,
        "coin:policy_enforcement_decision_required":true,
        "coin:attribute_aggregation_required":true,
        "contacts:0:contactType":"technical",
        "contacts:0:emailAddress":"femo@surfnet.nl",
        "contacts:0:givenName":"Femke",
        "contacts:0:surName":"Morsch",
        "contacts:0:telephoneNumber":"06-123213",
        "contacts:1:contactType":"technical",
        "contacts:1:emailAddress":"boy@ibuildings.nl",
        "contacts:1:givenName":"Boy",
        "contacts:1:surName":"Baukema",
        "contacts:1:telephoneNumber":"+31 (0)118 429 550",
        "contacts:2:contactType":"technical",
        "contacts:2:emailAddress":"contact@openconext.org",
        "contacts:2:givenName":"OpenConext",
        "contacts:2:surName":"Support",
        "contacts:2:telephoneNumber":"+31 123 456 789",
        "description:en":"Test",
        "description:nl":"Test",
        "displayName:en":"Test DisplayName",
        "displayName:nl":"Test DisplayName",
        "logo:0:height":60,
        "logo:0:url":"https://.png",
        "logo:0:width":120,
        "name:en":"Test",
        "name:nl":"Test",
        "NameIDFormat":"urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified",
        "NameIDFormats:0":"urn:oasis:names:tc:SAML:2.0:nameid-format:persistent",
        "NameIDFormats:1":"urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
        "NameIDFormats:2":"urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified",
        "OrganizationDisplayName:en":"Test OrganizationDisplayName EN",
        "OrganizationDisplayName:nl":"Org DisplayName NL",
        "OrganizationName:en":"ORGname EN \u0ca0_\u0ca0",
        "OrganizationName:nl":"ORGname NL \u0ca0_\u0ca0",
        "OrganizationURL:en":"https://example.edu",
        "OrganizationURL:nl":"https://example.edu",
        "redirect.sign":true,
        "coin:signature_method":"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256",
        "SingleLogoutService_Binding":"urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
        "SingleLogoutService_Location":"https://example.edu",
        "UIInfo:DisplayName:en":"Test",
        "UIInfo:DisplayName:nl":"Test",
        "url:en":"https://example.edu",
        "url:nl":"https://example.nl",
        "workflowState":"prodaccepted"
        }', true);

        $assembler = new JanusRestV1Assembler();
        $serviceProvider = $assembler->assemble(
            'https://test2.example.edu',
            $entity
        );

        $this->assertTrue($serviceProvider instanceof ServiceProvider);
        $this->assertCount(5, $serviceProvider->assertionConsumerServices);
        $this->assertEquals(5, $serviceProvider->assertionConsumerServices[5]->serviceIndex);
        $this->assertCount(3, $serviceProvider->certificates);
        $this->assertEquals($certData, $serviceProvider->certificates[0]->toCertData());
        $this->assertEquals($serviceProvider->certificates[0]->toPem(), $serviceProvider->certificates[1]->toPem());
        $this->assertTrue($serviceProvider->additionalLogging);
        $this->assertTrue($serviceProvider->displayUnconnectedIdpsWayf);
        $this->assertTrue($serviceProvider->skipDenormalization);
        $this->assertTrue($serviceProvider->policyEnforcementDecisionRequired);
        $this->assertTrue($serviceProvider->attributeAggregationRequired);
        $this->assertEquals('https://example.edu/eula', $serviceProvider->termsOfServiceUrl);
        $this->assertFalse($serviceProvider->isConsentRequired);
        $this->assertTrue($serviceProvider->publishInEdugain);
        $this->assertEquals('2012-09-01', $serviceProvider->publishInEduGainDate->format('Y-m-d'));
        $this->assertTrue($serviceProvider->isTransparentIssuer);
        $this->assertTrue($serviceProvider->isTrustedProxy);
        $this->assertCount(3, $serviceProvider->contactPersons);
        $this->assertEquals('technical', $serviceProvider->contactPersons[1]->contactType);
        $this->assertEquals('boy@ibuildings.nl', $serviceProvider->contactPersons[1]->emailAddress);
        $this->assertEquals('Boy', $serviceProvider->contactPersons[1]->givenName);
        $this->assertEquals('Baukema', $serviceProvider->contactPersons[1]->surName);
        $this->assertEquals('Test', $serviceProvider->descriptionEn);
        $this->assertEquals('Test', $serviceProvider->descriptionNl);
        $this->assertEquals('Test DisplayName', $serviceProvider->displayNameEn);
        $this->assertEquals('Test DisplayName', $serviceProvider->displayNameNl);
        $this->assertEquals(60, $serviceProvider->logo->height);
        $this->assertEquals(120, $serviceProvider->logo->width);
        $this->assertEquals('https://.png', $serviceProvider->logo->url);
        $this->assertEquals(SAML2_Const::NAMEID_UNSPECIFIED, $serviceProvider->nameIdFormat);
        $this->assertEquals(
            array(SAML2_Const::NAMEID_PERSISTENT, SAML2_Const::NAMEID_TRANSIENT, SAML2_Const::NAMEID_UNSPECIFIED),
            $serviceProvider->supportedNameIdFormats
        );
        $this->assertEquals('Test OrganizationDisplayName EN', $serviceProvider->organizationEn->displayName);
        $this->assertEquals('Org DisplayName NL'             , $serviceProvider->organizationNl->displayName);
        $this->assertEquals('ORGname EN ಠ_ಠ', $serviceProvider->organizationEn->name);
        $this->assertEquals('ORGname NL ಠ_ಠ', $serviceProvider->organizationNl->name);
        $this->assertEquals('https://example.edu', $serviceProvider->organizationEn->url);
        $this->assertEquals('https://example.edu', $serviceProvider->organizationNl->url);
        $this->assertTrue($serviceProvider->requestsMustBeSigned);
        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $serviceProvider->signatureMethod);
        $this->assertEquals(SAML2_Const::BINDING_HTTP_REDIRECT, $serviceProvider->singleLogoutService->binding);
        $this->assertEquals('https://example.edu', $serviceProvider->singleLogoutService->location);
        $this->assertEquals(ServiceProvider::WORKFLOW_STATE_PROD, $serviceProvider->workflowState);
        $this->assertEquals('https://example.edu', $serviceProvider->supportUrlEn);
        $this->assertEquals('https://example.nl', $serviceProvider->supportUrlNl);
    }
}
