<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Disassembler;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit_Framework_TestCase;

/**
 * Class CortoDisassemblerTest
 * @package OpenConext\EngineBlock\Metadata\Entity\Disassembler
 */
class CortoDisassemblerTest extends PHPUnit_Framework_TestCase
{
    public function testSpDisassemble()
    {
        $serviceProvider = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'https://sp.example.edu',
                'displayNameNl' => 'DisplayName',
                'displayNameEn' => 'DisplayName',
                'isTransparentIssuer' => true,
                'displayUnconnectedIdpsWayf' => true,
                'isConsentRequired' => false,
                'skipDenormalization' => true,
                'policyEnforcementDecisionRequired' => true,
            ]
        );

        // set a non-idp arp rule to enable attribute aggregation
        $serviceProvider->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'source',
                ],
            ],
        ]);

        $contact = new ContactPerson('support');
        $contact->emailAddress = 't@t.t';
        $contact->telephoneNumber = '0611';

        $serviceProvider->contactPersons[] = $contact;

        $disassembler = new CortoDisassembler();
        $cortoServiceProvider = $disassembler->translateServiceProvider($serviceProvider);

        $this->assertEquals($serviceProvider->entityId              , $cortoServiceProvider['EntityID']);
        $this->assertEmpty($cortoServiceProvider['certificates']);
        $this->assertEquals($serviceProvider->supportedNameIdFormats, $cortoServiceProvider['NameIDFormats']);
        $this->assertEquals($serviceProvider->workflowState         , $cortoServiceProvider['WorkflowState']);
        $this->assertEquals($serviceProvider->displayNameNl         , $cortoServiceProvider['DisplayName']['nl']);
        $this->assertEquals($serviceProvider->displayNameNl         , $cortoServiceProvider['DisplayName']['en']);
        $this->assertEquals('yes'                                   , $cortoServiceProvider['TransparentIssuer']);
        $this->assertEquals('yes'                                   , $cortoServiceProvider['DisplayUnconnectedIdpsWayf']);
        $this->assertEquals(!$serviceProvider->getCoins()->isConsentRequired()    , $cortoServiceProvider['NoConsentRequired']);
        $this->assertEquals($serviceProvider->getCoins()->skipDenormalization()   , $cortoServiceProvider['SkipDenormalization']);
        $this->assertEquals($serviceProvider->getCoins()->policyEnforcementDecisionRequired()   , $cortoServiceProvider['PolicyEnforcementDecisionRequired']);
        $this->assertEquals($serviceProvider->isAttributeAggregationRequired()        , true);
        $this->assertEquals($serviceProvider->isAttributeAggregationRequired()        , $cortoServiceProvider['AttributeAggregationRequired']);
        $this->assertEquals($contact->contactType, $cortoServiceProvider['ContactPersons'][0]['ContactType']);
        $this->assertEquals($contact->emailAddress, $cortoServiceProvider['ContactPersons'][0]['EmailAddress']);
        $this->assertEquals($contact->telephoneNumber, $cortoServiceProvider['ContactPersons'][0]['TelephoneNumber']);
    }

    public function testIdpDisassemble()
    {
        $identityProvider = new IdentityProvider('https://idp.example.edu');

        $contact = new ContactPerson('support');
        $contact->emailAddress = 't@t.t';
        $contact->telephoneNumber = '0611';

        $identityProvider->contactPersons[] = $contact;

        $disassembler = new CortoDisassembler();
        $cortoIdentityProvider = $disassembler->translateIdentityProvider($identityProvider);

        $this->assertEquals($identityProvider->entityId, $cortoIdentityProvider['EntityID']);
        $this->assertEmpty($cortoIdentityProvider['certificates']);
        $this->assertEquals($identityProvider->supportedNameIdFormats, $cortoIdentityProvider['NameIDFormats']);
        $this->assertEquals($identityProvider->workflowState, $cortoIdentityProvider['WorkflowState']);
        $this->assertEquals($identityProvider->getCoins()->guestQualifier(), $cortoIdentityProvider['GuestQualifier']);
        $this->assertEquals($identityProvider->getConsentSettings()->getSpEntityIdsWithoutConsent(), $cortoIdentityProvider['SpsWithoutConsent']);
        $this->assertEquals($identityProvider->getCoins()->hidden(), $cortoIdentityProvider['isHidden']);
        $this->assertEmpty($cortoIdentityProvider['shibmd:scopes']);
        $this->assertEquals($contact->contactType, $cortoIdentityProvider['ContactPersons'][0]['ContactType']);
        $this->assertEquals($contact->emailAddress, $cortoIdentityProvider['ContactPersons'][0]['EmailAddress']);
        $this->assertEquals($contact->telephoneNumber, $cortoIdentityProvider['ContactPersons'][0]['TelephoneNumber']);
    }
}
