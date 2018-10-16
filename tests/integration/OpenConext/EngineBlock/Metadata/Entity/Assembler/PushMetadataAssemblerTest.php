<?php

namespace OpenConext\EngineBlock\Tests;

use OpenConext\EngineBlock\Metadata\Entity\Assembler\PushMetadataAssembler;
use OpenConext\EngineBlock\Validator\AllowedSchemeValidator;
use PHPUnit_Framework_TestCase;

class PushMetadataAssemblerTest extends PHPUnit_Framework_TestCase
{
    private $assembler;

    public function setUp()
    {
        $this->assembler = new PushMetadataAssembler(new AllowedSchemeValidator(['http', 'https']));
    }

    /**
     * @dataProvider invalidAcsLocations
     * @expectedException \RuntimeException
     */
    public function test_it_rejects_invalid_acs_location_schemes($acsLocation)
    {
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

    public function invalidAcsLocations()
    {
        return [
            'invalid-scheme' => ['javascript:alert("hello world");'],
            'no-scheme' => ['www.sp.example.com'],
        ];
    }
}
