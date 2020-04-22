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

        $overrides = [
            'id' =>  $ormEntity->id,
            'entityId' => $ormEntity->entityId,
            'nameNl' => $ormEntity->nameNl,
            'nameEn' => $ormEntity->nameEn,
            'namePt' => $ormEntity->namePt,
            'descriptionNl' => $ormEntity->descriptionNl,
            'descriptionEn' => $ormEntity->descriptionEn,
            'descriptionPt' => $ormEntity->descriptionPt,
            'displayNameNl' => $ormEntity->displayNameNl,
            'displayNameEn' => $ormEntity->displayNameEn,
            'displayNamePt' => $ormEntity->displayNamePt,
            'logo' => $ormEntity->logo,
            'organizationNl' => $ormEntity->organizationNl,
            'organizationEn' => $ormEntity->organizationEn,
            'organizationPt' => $ormEntity->organizationPt,
            'keywordsNl' => $ormEntity->keywordsNl,
            'keywordsEn' => $ormEntity->keywordsEn,
            'keywordsPt' => $ormEntity->keywordsPt,
            'certificates' => $ormEntity->certificates,
            'workflowState' => $ormEntity->workflowState,
            'contactPersons' => $ormEntity->contactPersons,
            'nameIdFormat' => $ormEntity->nameIdFormat,
            'supportedNameIdFormats' => $ormEntity->supportedNameIdFormats,
            'singleLogoutService' => $ormEntity->singleLogoutService,
            'requestsMustBeSigned' => $ormEntity->requestsMustBeSigned,
            'manipulation' => $ormEntity->manipulation,
            'coins' => $ormEntity->getCoins(),
            'enabledInWayf' => $ormEntity->enabledInWayf,
            'singleSignOnServices' => $ormEntity->singleSignOnServices,
            'consentSettings' => $ormEntity->getConsentSettings(),
            'shibMdScopes' => $ormEntity->shibMdScopes,
        ];

        $this->runIdentityProviderAssertions($adapter, $adapter, $overrides);
    }
}
