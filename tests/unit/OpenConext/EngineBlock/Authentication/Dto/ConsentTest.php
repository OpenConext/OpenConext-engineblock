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

namespace OpenConext\EngineBlock\Authentication\Tests\Dto;

use DateTime;
use OpenConext\EngineBlock\Authentication\Dto\Consent;
use OpenConext\EngineBlock\Authentication\Model\Consent as ConsentModel;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;

class ConsentTest extends TestCase
{
    private function createServiceProvider(array $organizations = [], $omitDisplayName = false)
    {
        $supportContact = new ContactPerson('support');
        $supportContact->givenName = 'givenName';
        $supportContact->surName = 'surName';
        $supportContact->telephoneNumber = '+31612345678';
        $supportContact->emailAddress = 'mail@example.org';

        $enOrg = new Organization('Organization name EN', 'Organization display name EN', 'https://org.example.com');
        $nlOrg = new Organization('Organization name NL', 'Organization display name NL', 'https://org.example.nl');
        $ptOrg = new Organization('Organization name PT', 'Organization display name PT', 'https://org.example.pt');
        if (!empty($organizations)) {
            $enOrg = $organizations['en'];
            $nlOrg = $organizations['nl'];
            $ptOrg = $organizations['pt'];
        }

        $mduiJson = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"Name EN","language":"en"},"nl":{"value":"Name NL","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        if ($omitDisplayName) {
            $mduiJson = '{"DisplayName":{"name":"DisplayName"},"Description":{"name":"Description","values":{"en":{"value":"Description EN","language":"en"},"nl":{"value":"Description NL","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        }
        $mdui = Mdui::fromJson($mduiJson);

        $serviceProvider = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'entity-id',
                'mdui' => $mdui,
                'contactPersons' => [
                    $supportContact
                ],
                'supportUrlNl' => 'https://example.org/support-nl',
                'supportUrlEn' => 'https://example.org/support-en',
                'displayNameEn' => 'Display Name EN',
                'displayNameNl' => 'Display Name NL',
                'termsOfServiceUrl' => 'https://example.org/eula',
                'nameIdFormat' => Constants::NAMEID_TRANSIENT,
                'organizationEn' => $enOrg,
                'organizationNl' => $nlOrg,
                'organizationPt' => $ptOrg,
            ]
        );

        return $serviceProvider;
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Dto
     */
    public function all_values_are_serialized_to_json()
    {
        $serviceProvider = $this->createServiceProvider();
        $consentGivenOn = new DateTime('20080624 10:00:00');
        $consentType = ConsentType::explicit();

        $consent = new Consent(
            new ConsentModel(
                'user-id',
                'entity-id',
                $consentGivenOn,
                $consentType
            ),
            $serviceProvider
        );

        $json = $consent->jsonSerialize();

        $this->assertEquals($consentGivenOn->format(DateTime::ATOM), $json['consent_given_on']);
        $this->assertEquals($consentType->jsonSerialize(), $json['consent_type']);
        $this->assertArrayHasKey('service_provider', $json);

        $json = $json['service_provider'];

        $this->assertEquals($serviceProvider->entityId, $json['entity_id']);
        $this->assertArrayHasKey('en', $json['support_url']);
        $this->assertArrayHasKey('nl', $json['support_url']);
        $this->assertEquals($serviceProvider->supportUrlEn, $json['support_url']['en']);
        $this->assertEquals($serviceProvider->supportUrlNl, $json['support_url']['nl']);
        $this->assertEquals($serviceProvider->getMdui()->getDisplayName('en'), $json['display_name']['en']);
        $this->assertEquals($serviceProvider->getMdui()->getDisplayName('nl'), $json['display_name']['nl']);
        $this->assertEquals($serviceProvider->supportUrlNl, $json['support_url']['nl']);
        $this->assertEquals($serviceProvider->getCoins()->termsOfServiceUrl(), $json['eula_url']);
        $this->assertEquals($serviceProvider->contactPersons[0]->emailAddress, $json['support_email']);
        $this->assertEquals($serviceProvider->nameIdFormat, $json['name_id_format']);
        $this->assertEquals($serviceProvider->organizationEn->displayName, $json['organization_display_name']['en']);
        $this->assertEquals($serviceProvider->organizationNl->displayName, $json['organization_display_name']['nl']);
        $this->assertEquals($serviceProvider->organizationPt->displayName, $json['organization_display_name']['pt']);
    }

    /**
     * @dataProvider provideOrganizations
     */
    public function test_display_name_of_organizations_works_as_intended(
        array $organizations,
        array $expectations,
        string $errorMessage
    ) {
        $serviceProvider = $this->createServiceProvider($organizations);
        $consentGivenOn = new DateTime('20080624 10:00:00');
        $consentType = ConsentType::explicit();

        $consent = new Consent(
            new ConsentModel(
                'user-id',
                'entity-id',
                $consentGivenOn,
                $consentType
            ),
            $serviceProvider
        );

        $json = $consent->jsonSerialize()['service_provider']['organization_display_name'];
        $this->assertEquals($expectations['en'], $json['en'], $errorMessage);
        $this->assertEquals($expectations['nl'], $json['nl'], $errorMessage);
        $this->assertEquals($expectations['pt'], $json['pt'], $errorMessage);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Dto
     */
    public function display_name_falls_back_to_name_if_display_name_is_empty()
    {
        $serviceProvider = $this->createServiceProvider([], false);

        $serviceProvider->nameEn = 'Name EN';
        $serviceProvider->nameNl = 'Name NL';

        $consentGivenOn = new DateTime();
        $consentType = ConsentType::explicit();

        $consent = new Consent(
            new ConsentModel(
                'user-id',
                'entity-id',
                $consentGivenOn,
                $consentType
            ),
            $serviceProvider
        );

        $json = $consent->jsonSerialize();

        $this->assertArrayHasKey('service_provider', $json);

        $this->assertEquals($serviceProvider->nameEn, $json['service_provider']['display_name']['en']);
        $this->assertEquals($serviceProvider->nameNl, $json['service_provider']['display_name']['nl']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Dto
     */
    public function display_name_falls_back_to_entity_id_if_name_is_empty()
    {
        $serviceProvider = $this->createServiceProvider([], true);
        $serviceProvider->nameEn = '';
        $serviceProvider->nameNl = '';

        $consentGivenOn = new DateTime();
        $consentType = ConsentType::explicit();

        $consent = new Consent(
            new ConsentModel(
                'user-id',
                'entity-id',
                $consentGivenOn,
                $consentType
            ),
            $serviceProvider
        );

        $json = $consent->jsonSerialize();

        $this->assertArrayHasKey('service_provider', $json);

        $this->assertEquals($serviceProvider->entityId, $json['service_provider']['display_name']['en']);
        $this->assertEquals($serviceProvider->entityId, $json['service_provider']['display_name']['nl']);
    }

    public function provideOrganizations()
    {
        $displayNameEn = 'Organization display name EN';
        $nameEn = 'Organization name EN';
        $unknownEn = 'unknown';
        $displayNameNl = 'Organization display name NL';
        $nameNl = 'Organization name NL';
        $displayNamePt = 'Organization display name PT';
        $namePt = 'Organization name PT';

        $enOrg = new Organization($nameEn, $displayNameEn, 'https://org.example.com');
        $nlOrg = new Organization($nameNl, $displayNameNl, 'https://org.example.com');
        $ptOrg = new Organization($namePt, $displayNamePt, 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $displayNameEn, 'nl' => $displayNameNl, 'pt' => $displayNamePt];
        $exceptionMessage = 'Failed asserting rule: If OrganizationDisplayName:L is set, return OrganizationDisplayName:L';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization($nameEn, '', 'https://org.example.com');
        $nlOrg = new Organization($nameNl, '', 'https://org.example.com');
        $ptOrg = new Organization($namePt, '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $nameEn, 'nl' => $nameNl, 'pt' => $namePt];
        $exceptionMessage = 'Failed asserting rule: else if OrganizationName:L is set, returnOrganizationName:L';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization($nameEn, $displayNameEn, 'https://org.example.com');
        $nlOrg = new Organization('', '', 'https://org.example.com');
        $ptOrg = new Organization('', '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $displayNameEn, 'nl' => $displayNameEn, 'pt' => $displayNameEn];
        $exceptionMessage = 'Failed asserting rule: else if OrganizationDisplayName:"en" is set, return OrganizationDisplayName:"en"';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization($nameEn, '', 'https://org.example.com');
        $nlOrg = new Organization('', '', 'https://org.example.com');
        $ptOrg = new Organization('', '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $nameEn, 'nl' => $nameEn, 'pt' => $nameEn];
        $exceptionMessage = 'Failed asserting rule: else if OrganizationName:"en" is set, returnOrganizationName:"en"';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization('', '', 'https://org.example.com');
        $nlOrg = new Organization('', '', 'https://org.example.com');
        $ptOrg = new Organization('', '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $unknownEn, 'nl' => $unknownEn, 'pt' => $unknownEn];
        $exceptionMessage = 'Failed asserting rule: else return "unknown"';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization($nameEn, '', 'https://org.example.com');
        $nlOrg = new Organization('', $displayNameNl, 'https://org.example.com');
        $ptOrg = new Organization('', '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $nameEn, 'nl' => $displayNameNl, 'pt' => $nameEn];
        $exceptionMessage = 'Failed asserting rule: mixed';
        yield [$organizations, $expectation, $exceptionMessage];

        $enOrg = new Organization('', '', 'https://org.example.com');
        $nlOrg = new Organization('', $displayNameNl, 'https://org.example.com');
        $ptOrg = new Organization($namePt, '', 'https://org.example.com');
        $organizations = ['en' => $enOrg, 'nl' => $nlOrg, 'pt' => $ptOrg];
        $expectation = ['en' => $unknownEn, 'nl' => $displayNameNl, 'pt' => $namePt];
        $exceptionMessage = 'Failed asserting rule: mixed EN is unknown';
        yield [$organizations, $expectation, $exceptionMessage];
    }

    private function setCoin(ServiceProvider $sp, $key, $name)
    {
        $jsonData = $sp->getCoins()->toJson();
        $data = json_decode($jsonData, true);
        $data[$key] = $name;
        $jsonData = json_encode($data);

        $coins = Coins::fromJson($jsonData);

        $property = new \ReflectionProperty(ServiceProvider::class, 'coins');
        $property->setAccessible(true);
        $property->setValue(null, $coins);
    }
}
