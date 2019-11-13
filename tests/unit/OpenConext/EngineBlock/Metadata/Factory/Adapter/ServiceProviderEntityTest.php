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

namespace OpenConext\EngineBlock\Metadata\Factory\Adapter;

use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;

class ServiceProviderEntityTest extends AbstractEntityTest
{
    /**
     * This test will test if all methods work
     */
    public function test_if_all_methods_will_work()
    {
        $values = $this->getServiceProviderMockProperties();
        $ormEntity = $this->getOrmEntityServiceProviderMock($values);

        $adapter = new ServiceProviderEntity($ormEntity);

        $assertions = [
            'id' =>  $ormEntity->id,
            'entityId' => $ormEntity->entityId,
            'nameNl' => $ormEntity->nameNl,
            'nameEn' => $ormEntity->nameEn,
            'descriptionNl' => $ormEntity->descriptionNl,
            'descriptionEn' => $ormEntity->descriptionEn,
            'displayNameNl' => $ormEntity->displayNameNl,
            'displayNameEn' => $ormEntity->displayNameEn,
            'logo' => $ormEntity->logo,
            'organizationNl' => $ormEntity->organizationNl,
            'organizationEn' => $ormEntity->organizationEn,
            'keywordsNl' => $ormEntity->keywordsNl,
            'keywordsEn' => $ormEntity->keywordsEn,
            'certificates' => $ormEntity->certificates,
            'workflowState' => $ormEntity->workflowState,
            'contactPersons' => $ormEntity->contactPersons,
            'nameIdFormat' => $ormEntity->nameIdFormat,
            'supportedNameIdFormats' => $ormEntity->supportedNameIdFormats,
            'singleLogoutService' => $ormEntity->singleLogoutService,
            'requestsMustBeSigned' => $ormEntity->requestsMustBeSigned,
            'manipulation' => $ormEntity->manipulation,
            'coins' => $ormEntity->getCoins(),

            'requestedAttributes' => $ormEntity->requestedAttributes,
            'supportUrlEn' => $ormEntity->supportUrlEn,
            'supportUrlNl' => $ormEntity->supportUrlNl,
            'attributeReleasePolicy' => $ormEntity->attributeReleasePolicy,
            'assertionConsumerServices' => $ormEntity->assertionConsumerServices,
            'allowedIdpEntityIds' => $ormEntity->allowedIdpEntityIds,
            'allowAll' => $ormEntity->allowAll,
            'attributeAggregationRequired' => $ormEntity->isAttributeAggregationRequired(),
            'allowed' => $ormEntity->isAllowed('entity-id'),
            'displayName' => $ormEntity->getDisplayName('EN')
        ];

        $this->runServiceProviderAssertions($adapter, $adapter, $assertions);
    }
}
