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

class IdentityProviderEntityTest extends AbstractEntityTest
{
    /**
     * This test will test if all methods work
     */
    public function test_if_all_methods_will_work()
    {
        $values = $this->getIdentityProviderMockProperties();
        $ormEntity = $this->getOrmEntityIdentityProviderMock($values);

        $adapter = new IdentityProviderEntity($ormEntity);

        $assertions = [
            'id' => [$ormEntity->id, $adapter->getId()],
            'entityId' => [$ormEntity->entityId, $adapter->getEntityId()],
            'nameNl' => [$ormEntity->nameNl, $adapter->getNameNl()],
            'nameEn' => [$ormEntity->nameEn, $adapter->getNameEn()],
            'descriptionNl' => [$ormEntity->descriptionNl, $adapter->getDescriptionNl()],
            'descriptionEn' => [$ormEntity->descriptionEn, $adapter->getDescriptionEn()],
            'displayNameNl' => [$ormEntity->displayNameNl, $adapter->getDisplayNameNl()],
            'displayNameEn' => [$ormEntity->displayNameEn, $adapter->getDisplayNameEn()],
            'logo' => [$ormEntity->logo, $adapter->getLogo()],
            'organizationNl' => [$ormEntity->organizationNl, $adapter->getOrganizationNl()],
            'organizationEn' => [$ormEntity->organizationEn, $adapter->getOrganizationEn()],
            'keywordsNl' => [$ormEntity->keywordsNl, $adapter->getKeywordsNl()],
            'keywordsEn' => [$ormEntity->keywordsEn, $adapter->getKeywordsEn()],
            'certificates' => [$ormEntity->certificates, $adapter->getCertificates()],
            'workflowState' => [$ormEntity->workflowState, $adapter->getWorkflowState()],
            'contactPersons' => [$ormEntity->contactPersons, $adapter->getContactPersons()],
            'nameIdFormat' => [$ormEntity->nameIdFormat, $adapter->getNameIdFormat()],
            'supportedNameIdFormats' => [$ormEntity->supportedNameIdFormats, $adapter->getSupportedNameIdFormats()],
            'singleLogoutService' => [$ormEntity->singleLogoutService, $adapter->getSingleLogoutService()],
            'requestsMustBeSigned' => [$ormEntity->requestsMustBeSigned, $adapter->isRequestsMustBeSigned()],
            'responseProcessingService' => [$ormEntity->responseProcessingService, $adapter->getResponseProcessingService()],
            'manipulation' => [$ormEntity->manipulation, $adapter->getManipulation()],
            'coins' => [$ormEntity->getCoins(), $adapter->getCoins()],
            'enabledInWayf' => [$ormEntity->enabledInWayf, $adapter->isEnabledInWayf()],
            'singleSignOnServices' => [$ormEntity->singleSignOnServices, $adapter->getSingleSignOnServices()],
            'consentSettings' => [$ormEntity->getConsentSettings(), $adapter->getConsentSettings()],
            'shibMdScopes' => [$ormEntity->shibMdScopes, $adapter->getShibMdScopes()],
        ];

        $this->runIdentityProviderAssertions($assertions);
    }
}
