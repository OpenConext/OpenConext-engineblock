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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Corto_Mapper_Metadata_Entity_OrganizationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testOrganizationIsCorrectlyAddedToRootElement()
    {
        $entity = $this->factoryEntity();
        $organizationMapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization($entity);
        $rootElement = array();

        $expectedRootElement = array(
            'md:Organization' => array(
                'md:OrganizationName' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse naam',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English Name',
                    ),
                ),
                'md:OrganizationDisplayName' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse weergavenaam',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English displayname',
                    ),
                ),
                'md:OrganizationURL' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse url',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English url',
                    ),
                )
            )
        );
        $this->assertEquals($expectedRootElement, $organizationMapper->mapTo($rootElement));
    }

    /**
     * @return IdentityProvider
     */
    private function factoryEntity()
    {
        $entity = new IdentityProvider('https://idp.example.edu');
        $entity->organizationEn = new Organization('English Name', 'English displayname', 'English url');
        $entity->organizationNl = new Organization('Nederlandse naam', 'Nederlandse weergavenaam', 'Nederlandse url');

        return $entity;
    }
}
