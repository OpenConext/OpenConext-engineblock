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

namespace OpenConext\EngineBlock\Metadata\Factory\ValueObject;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class EngineBlockConfigurationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_configuration_creation()
    {
        $suitName = 'OpenestConext';
        $orgUrl = 'https://www.example.org';
        $orgName = 'OpenestConext Company';
        $orgDisplayName = 'OpenestConext Company Inc.';

        $translator = m::mock(TranslatorInterface::class);
        $translator
            ->shouldReceive('trans')
            ->with('suite_name')->once()
            ->andReturn($suitName);
        $translator
            ->shouldReceive('trans')
            ->with('metadata_organization_name')->once()
            ->andReturn($orgName);
        $translator
            ->shouldReceive('trans')
            ->with('metadata_organization_displayname')->once()
            ->andReturn($orgDisplayName);
        $translator
            ->shouldReceive('trans')
            ->with('metadata_organization_url')->once()
            ->andReturn($orgUrl);

        $mail = 'mail@example.org';
        $description = 'The EngineBlock';
        $logo = '/images/logo.png';
        $height = 120;
        $width = 120;

        $configuration = new EngineBlockConfiguration(
            $translator,
            $mail,
            $description,
            'example.org',
            $logo,
            $width,
            $height
        );

        $this->assertEquals('OpenestConext EngineBlock', $configuration->getName());
        $this->assertEquals('example.org', $configuration->getHostname());
        $this->assertEquals($description, $configuration->getDescription());

        /** @var ContactPerson[] $contactPersons */
        $contactPersons = $configuration->getContactPersons();
        $this->assertCount(3, $contactPersons);
        $this->assertEquals($mail, $contactPersons[0]->emailAddress);
        $this->assertEquals('Support', $contactPersons[0]->surName);
        $this->assertEquals($orgName, $contactPersons[0]->givenName);
        $this->assertEquals('support', $contactPersons[0]->contactType);

        $this->assertEquals($mail, $contactPersons[1]->emailAddress);
        $this->assertEquals('Support', $contactPersons[1]->surName);
        $this->assertEquals($orgName, $contactPersons[1]->givenName);
        $this->assertEquals('technical', $contactPersons[1]->contactType);

        $this->assertEquals($mail, $contactPersons[2]->emailAddress);
        $this->assertEquals('Support', $contactPersons[2]->surName);
        $this->assertEquals($orgName, $contactPersons[2]->givenName);
        $this->assertEquals('administrative', $contactPersons[2]->contactType);

        $this->assertInstanceOf(Logo::class, $configuration->getLogo());
        $this->assertEquals('https://example.org/images/logo.png', $configuration->getLogo()->url);
        $this->assertEquals($width, $configuration->getLogo()->width);
        $this->assertEquals($height, $configuration->getLogo()->height);

        $this->assertInstanceOf(Organization::class, $configuration->getOrganization());
        $this->assertEquals($orgUrl, $configuration->getOrganization()->url);
        $this->assertEquals($orgName, $configuration->getOrganization()->name);
        $this->assertEquals($orgDisplayName, $configuration->getOrganization()->displayName);
    }
}
