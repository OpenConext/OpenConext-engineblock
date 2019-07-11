<?php

namespace OpenConext\EngineBlock\Tests;

use OpenConext\EngineBlock\Metadata\Entity\Assembler\PushMetadataAssembler;
use OpenConext\EngineBlock\Validator\AllowedSchemeValidator;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class PushMetadataAssemblerTest extends PHPUnit_Framework_TestCase
{
    private $assembler;

    public function setUp()
    {
        $this->assembler = new PushMetadataAssembler(new AllowedSchemeValidator(['http', 'https']));
    }

    /**
     * @dataProvider invalidAcsLocations
     */
    public function test_it_rejects_invalid_acs_location_schemes($acsLocation)
    {
        $this->expectException(\RuntimeException::class);

        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49523": {
                "allow_all_entities": true,
                "allowed_connections": [],
                "metadata": {
                    "AssertionConsumerService": [{
                        "Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                        "Index": "0",
                        "Location": "%s"
                    }],
                    "NameIDFormat": "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified",
                    "NameIDFormats": ["urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified", "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent", "urn:oasis:names:tc:SAML:2.0:nameid-format:transient"],
                    "contacts": [{
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "technical"
                    }, {
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "support"
                    }, {
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "administrative"
                    }],
                    "description": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    },
                    "displayName": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    },
                    "logo": [{
                        "width": "96",
                        "url": "https:\/\/static.vm.openconext.org\/media\/conext_logo.png",
                        "height": "96"
                    }],
                    "name": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    }
                },
                "name": "https:\/\/serviceregistry.vm.openconext.org\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-sp",
                "state": "prodaccepted",
                "type": "saml20-sp"
            }
	    }';

        $input = sprintf($connection, addslashes($acsLocation));
        $input = json_decode($input);

        $this->assembler->assemble($input);
    }

    /**
     * @dataProvider validCoins
     */
    public function testCoins($coinName, $roleType, $parameter, $type) {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49523": {
                "allow_all_entities": true,
                "allowed_connections": [],
                "metadata": {
                    "coin": {}
                },
                "name": "https:\/\/role/sp",
                "state": "prodaccepted",
                "type": "saml20-sp"
            }
        }';

        $input = json_decode($connection);
        $input->{"2d96e27a-76cf-4ca2-ac70-ece5d4c49523"}->type = $roleType;

        switch ($type) {
            case 'bool';
                $values = $this->validCoinValuesBool();
                break;
            case 'bool-negative';
                $values = $this->validCoinValuesBoolNegative();
                break;
            case 'string';
                $values = $this->validCoinValuesString();
                break;
            case 'string-signature-method';
                $values = $this->validCoinValuesStringSignatureMethod();
                break;
            case 'string-guest-qualifier';
                $values = $this->validCoinValuesStringGuestQualifier();
                break;
            default:
                throw new RuntimeException('Unknown coin type');
        }

        foreach ($values as $assertion) {
            $input->{"2d96e27a-76cf-4ca2-ac70-ece5d4c49523"}->metadata->coin->$coinName = $assertion[0];

            $roles = $this->assembler->assemble($input);

            $this->assertSame($assertion[1], $roles[0]->getCoins()->{$parameter}(), "Invalid coin conversion for {$roleType}:{$coinName}($type) expected '{$assertion[1]}' but encountered '{$roles[0]->getCoins()->{$parameter}()}'");
        }
    }

    public function invalidAcsLocations()
    {
        return [
            'invalid-scheme' => ['javascript:alert("hello world");'],
            'no-scheme' => ['www.sp.example.com'],
        ];
    }

    public function validCoins()
    {
        return [
            // SP
            ['no_consent_required', 'saml20-sp', 'isConsentRequired', 'bool-negative'],
            ['transparant_issuer', 'saml20-sp', 'isTransparentIssuer', 'bool'],
            ['trusted_proxy', 'saml20-sp', 'isTrustedProxy', 'bool'],
            ['display_unconnected_idps_wayf', 'saml20-sp', 'displayUnconnectedIdpsWayf', 'bool'],
            ['eula', 'saml20-sp', 'termsOfServiceUrl', 'string'],
            ['do_not_add_attribute_aliases', 'saml20-sp', 'skipDenormalization', 'bool'],
            ['policy_enforcement_decision_required', 'saml20-sp', 'policyEnforcementDecisionRequired', 'bool'],
            ['requesterid_required', 'saml20-sp', 'requesteridRequired', 'bool'],
            ['sign_response', 'saml20-sp', 'signResponse', 'bool'],

            // IDP
            ['guest_qualifier', 'saml20-idp', 'guestQualifier', 'string-guest-qualifier'],
            ['schachomeorganization', 'saml20-idp', 'schacHomeOrganization', 'string'],
            ['hidden', 'saml20-idp', 'hidden', 'bool'],

            // Abstract
            ['disable_scoping', 'saml20-idp', 'disableScoping', 'bool'],
            ['additional_logging', 'saml20-idp', 'additionalLogging', 'bool'],
            ['signature_method', 'saml20-idp', 'signatureMethod', 'string-signature-method'],
        ];
    }

    private function validCoinValuesBool() {
        return [
            [null, false],
            [true, true],
            [false, false],
            ["1", true],
            ["0", false],
            ["-1", true],
        ];
    }

    private function validCoinValuesBoolNegative() {
        return [
            [null, true],
            [true, false],
            [false, true],
            ["1", false],
            ["0", true],
            ["-1", false],
        ];
    }

    private function validCoinValuesString() {
        return [
            [null, null],
            ["", ""],
            ["string", "string"],
        ];
    }

    private function validCoinValuesStringSignatureMethod() {
        return [
            [null, "http://www.w3.org/2000/09/xmldsig#rsa-sha1"],
            ["", ""],
            ["string", "string"],
        ];
    }

    private function validCoinValuesStringGuestQualifier() {
        return [
            [null, "All"],
            ["", ""],
            ["string", "string"],
        ];
    }
}
