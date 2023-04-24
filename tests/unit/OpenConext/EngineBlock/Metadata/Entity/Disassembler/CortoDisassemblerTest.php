<?php

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

namespace OpenConext\EngineBlock\Metadata\Entity\Disassembler;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit\Framework\TestCase;

class CortoDisassemblerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSpDisassemble()
    {
        $mduiJson = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"DisplayName","language":"en"},"nl":{"value":"DisplayName","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        $mdui = Mdui::fromJson($mduiJson);
        $serviceProvider = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'https://sp.example.edu',
                'mdui' => $mdui,
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
