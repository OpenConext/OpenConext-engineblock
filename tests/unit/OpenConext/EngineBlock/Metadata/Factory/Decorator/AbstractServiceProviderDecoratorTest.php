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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;


use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;

abstract class AbstractServiceProviderDecoratorTest extends AbstractEntityTest
{
    public function getServiceProviderAssertions(ServiceProviderEntityInterface $adapter, ServiceProviderEntityInterface $decorator)
    {
        $implemented = $this->getServiceProviderValues(ServiceProviderEntityInterface::class);

        $assertions = [
            'id' => [$adapter->getId(), $decorator->getId()],
            'entityId' => [$adapter->getEntityId(), $decorator->getEntityId()],
            'nameNl' => [$adapter->getNameNl(),  $decorator->getNameNl()],
            'nameEn' => [$adapter->getNameEn(),  $decorator->getNameEn()],
            'descriptionNl' => [$adapter->getDescriptionNl(),  $decorator->getDescriptionNl()],
            'descriptionEn' => [$adapter->getDescriptionEn(),  $decorator->getDescriptionEn()],
            'displayNameNl' => [$adapter->getDisplayNameNl(),  $decorator->getDisplayNameNl()],
            'displayNameEn' => [$adapter->getDisplayNameEn(),  $decorator->getDisplayNameEn()],
            'logo' => [$adapter->getLogo(),  $decorator->getLogo()],
            'organizationNl' => [$adapter->getOrganizationNl(),  $decorator->getOrganizationNl()],
            'organizationEn' => [$adapter->getOrganizationEn(),  $decorator->getOrganizationEn()],
            'keywordsNl' => [$adapter->getKeywordsNl(),  $decorator->getKeywordsNl()],
            'keywordsEn' => [$adapter->getKeywordsEn(),  $decorator->getKeywordsEn()],
            'certificates' => [$adapter->getCertificates(),  $decorator->getCertificates()],
            'workflowState' => [$adapter->getWorkflowState(),  $decorator->getWorkflowState()],
            'contactPersons' => [$adapter->getContactPersons(),  $decorator->getContactPersons()],
            'nameIdFormat' => [$adapter->getNameIdFormat(),  $decorator->getNameIdFormat()],
            'supportedNameIdFormats' => [$adapter->getSupportedNameIdFormats(),  $decorator->getSupportedNameIdFormats()],
            'singleLogoutService' => [$adapter->getSingleLogoutService(),  $decorator->getSingleLogoutService()],
            'requestsMustBeSigned' => [$adapter->isRequestsMustBeSigned(),  $decorator->isRequestsMustBeSigned()],
            'responseProcessingService' => [$adapter->getResponseProcessingService(),  $decorator->getResponseProcessingService()],
            'manipulation' => [$adapter->getManipulation(),  $decorator->getManipulation()],
            'coins' => [$adapter->getCoins(),  $decorator->getCoins()],
            'attributeReleasePolicy' => [$adapter->getAttributeReleasePolicy(),  $decorator->getAttributeReleasePolicy()],
            'assertionConsumerServices' => [$adapter->getAssertionConsumerServices(),  $decorator->getAssertionConsumerServices()],
            'allowedIdpEntityIds' => [$adapter->getAllowedIdpEntityIds(),  $decorator->getAllowedIdpEntityIds()],
            'allowAll' => [$adapter->isAllowAll(),  $decorator->isAllowAll()],
            'requestedAttributes' => [$adapter->getRequestedAttributes(),  $decorator->getRequestedAttributes()],
            'supportUrlEn' => [$adapter->getSupportUrlEn(),  $decorator->getSupportUrlEn()],
            'supportUrlNl' => [$adapter->getSupportUrlNl(),  $decorator->getSupportUrlNl()],

            'allowed' => [$adapter->isAllowed('entity-id-2'),  $decorator->isAllowed('entity-id-2')],
            'displayName'  => [$adapter->getDisplayName('EN'),  $decorator->getDisplayName('EN')],
            'attributeAggregationRequired' => [$adapter->isAttributeAggregationRequired(),  $decorator->isAttributeAggregationRequired()],
        ];

        $missing = array_diff_key($implemented, $assertions);
        $this->assertCount(0, $missing, 'missing tests for: '. json_encode($missing));
        $this->assertCount(33, $implemented);
        $this->assertCount(count($implemented), $assertions);

        return $assertions;
    }
}
