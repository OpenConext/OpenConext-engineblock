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

namespace OpenConext\EngineBlock\Metadata\Factory;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractEntityTest extends TestCase
{
    /**
     * Create an instance which could be used by decorators
     */
    public function createIdentityProviderAdapter(
        bool $emptyDisplayNameEn = false,
        bool $emptyDisplayNameNl = false,
        array $overrides = array()
    ): IdentityProviderEntity {
        $values = $this->getIdentityProviderMockProperties($emptyDisplayNameEn, $emptyDisplayNameNl);
        $values = array_merge($values, $overrides);
        $ormEntity = $this->getOrmEntityIdentityProviderMock($values);
        return new IdentityProviderEntity($ormEntity);
    }

    /**
     * Create an instance which could be used by decorators
     */
    public function createServiceProviderAdapter(array $overrides = []): ServiceProviderEntity
    {
        $values = $this->getServiceProviderMockProperties();
        $values = array_merge($values, $overrides);
        $ormEntity = $this->getOrmEntityServiceProviderMock($values);
        return new ServiceProviderEntity($ormEntity);
    }

    /**
     * Run the supplied assertions this is used to test the decorators
     */
    protected function runIdentityProviderAssertions(IdentityProviderEntityInterface $adapter, IdentityProviderEntityInterface $decorator, array $overrides = [])
    {
        $implemented = $this->getIdentityProviderValues(IdentityProviderEntityInterface::class);

        $assertions = [
            'id' => function(IdentityProviderEntityInterface $entity) { return $entity->getId(); },
            'entityId' => function(IdentityProviderEntityInterface $entity) { return $entity->getEntityId(); },
            'mdui' => function(IdentityProviderEntityInterface $entity) { return $entity->getMdui(); },
            'nameNl' => function(IdentityProviderEntityInterface $entity) { return  $entity->getName('nl'); },
            'nameEn' => function(IdentityProviderEntityInterface $entity) { return  $entity->getName('en'); },
            'namePt' => function(IdentityProviderEntityInterface $entity) { return  $entity->getName('pt'); },
            'descriptionNl' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDescription('nl'); },
            'descriptionEn' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDescription('en'); },
            'descriptionPt' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDescription('pt'); },
            'displayNameNl' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDisplayName('nl'); },
            'displayNameEn' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDisplayName('en'); },
            'displayNamePt' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDisplayName('pt'); },
            'logo' => function(IdentityProviderEntityInterface $entity) { return  $entity->getLogo(); },
            'organizationNl' => function(IdentityProviderEntityInterface $entity) { return  $entity->getOrganization('nl'); },
            'organizationEn' => function(IdentityProviderEntityInterface $entity) { return  $entity->getOrganization('en'); },
            'organizationPt' => function(IdentityProviderEntityInterface $entity) { return  $entity->getOrganization('pt'); },
            'keywordsNl' => function(IdentityProviderEntityInterface $entity) { return  $entity->getKeywords('nl'); },
            'keywordsEn' => function(IdentityProviderEntityInterface $entity) { return  $entity->getKeywords('en'); },
            'keywordsPt' => function(IdentityProviderEntityInterface $entity) { return  $entity->getKeywords('pt'); },
            'certificates' => function(IdentityProviderEntityInterface $entity) { return  $entity->getCertificates(); },
            'workflowState' => function(IdentityProviderEntityInterface $entity) { return  $entity->getWorkflowState(); },
            'contactPersons' => function(IdentityProviderEntityInterface $entity) { return  $entity->getContactPersons(); },
            'nameIdFormat' => function(IdentityProviderEntityInterface $entity) { return  $entity->getNameIdFormat(); },
            'supportedNameIdFormats' => function(IdentityProviderEntityInterface $entity) { return  $entity->getSupportedNameIdFormats(); },
            'singleLogoutService' => function(IdentityProviderEntityInterface $entity) { return  $entity->getSingleLogoutService(); },
            'requestsMustBeSigned' => function(IdentityProviderEntityInterface $entity) { return  $entity->isRequestsMustBeSigned(); },
            'manipulation' => function(IdentityProviderEntityInterface $entity) { return  $entity->getManipulation(); },
            'coins' => function(IdentityProviderEntityInterface $entity) { return  $entity->getCoins(); },
            'enabledInWayf' => function(IdentityProviderEntityInterface $entity) { return  $entity->isEnabledInWayf(); },
            'singleSignOnServices' => function(IdentityProviderEntityInterface $entity) { return  $entity->getSingleSignOnServices(); },
            'consentSettings' => function(IdentityProviderEntityInterface $entity) { return  $entity->getConsentSettings(); },
            'shibMdScopes' => function(IdentityProviderEntityInterface $entity) { return  $entity->getShibMdScopes(); },
            'discoveries' => function(IdentityProviderEntityInterface $entity) { return  $entity->getDiscoveries(); },
        ];

        $missing = array_diff_key($implemented, $assertions);
        $this->assertCount(0, $missing, 'missing tests for: '. json_encode($missing));
        $this->assertCount(33, $implemented);
        $this->assertCount(count($implemented), $assertions);

        foreach ($assertions as $name => $assertion) {
            if (array_key_exists($name, $overrides)) {
                $this->assertEquals($overrides[$name], $assertion($decorator), sprintf("Invalid expectancy in method override for property '%s'", $name));
            } else {
                $this->assertSame($assertion($adapter), $assertion($decorator), sprintf("Invalid expectancy in abstract method for property '%s'", $name));
            }
        }
    }

    /**
     * Run the supplied assertions this is used to test the decorators
     */
    protected function runServiceProviderAssertions(ServiceProviderEntityInterface $adapter, ServiceProviderEntityInterface $decorator, array $overrides = [])
    {
        $implemented = $this->getServiceProviderValues(ServiceProviderEntityInterface::class);

        $assertions = [
            'id' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getId(); },
            'entityId' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getEntityId(); },
            'mdui' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getMdui(); },
            'nameNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getName('nl'); },
            'nameEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getName('en'); },
            'namePt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getName('pt'); },
            'descriptionNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDescription('nl'); },
            'descriptionEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDescription('en'); },
            'descriptionPt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDescription('pt'); },
            'displayNameNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDisplayName('nl'); },
            'displayNameEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDisplayName('en'); },
            'displayNamePt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getDisplayName('pt'); },
            'logo' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getLogo(); },
            'organizationNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getOrganization('nl'); },
            'organizationEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getOrganization('en'); },
            'organizationPt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getOrganization('pt'); },
            'keywordsNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getKeywords('nl'); },
            'keywordsEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getKeywords('en'); },
            'keywordsPt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getKeywords('pt'); },
            'certificates' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getCertificates(); },
            'workflowState' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getWorkflowState(); },
            'contactPersons' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getContactPersons(); },
            'nameIdFormat' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getNameIdFormat(); },
            'supportedNameIdFormats' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getSupportedNameIdFormats(); },
            'singleLogoutService' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getSingleLogoutService(); },
            'requestsMustBeSigned' => function(ServiceProviderEntityInterface $decorator) { return $decorator->isRequestsMustBeSigned(); },
            'manipulation' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getManipulation(); },
            'coins' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getCoins(); },
            'attributeReleasePolicy' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getAttributeReleasePolicy(); },
            'assertionConsumerServices' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getAssertionConsumerServices(); },
            'allowedIdpEntityIds' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getAllowedIdpEntityIds(); },
            'allowAll' => function(ServiceProviderEntityInterface $decorator) { return $decorator->isAllowAll(); },
            'requestedAttributes' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getRequestedAttributes(); },
            'supportUrlEn' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getSupportUrl('en'); },
            'supportUrlNl' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getSupportUrl('nl'); },
            'supportUrlPt' => function(ServiceProviderEntityInterface $decorator) { return $decorator->getSupportUrl('pt'); },

            'allowed' => function(ServiceProviderEntityInterface $decorator, $entityId = 'entity-id-2') { return $decorator->isAllowed('entity-id-2'); },
            'attributeAggregationRequired' => function(ServiceProviderEntityInterface $decorator) { return $decorator->isAttributeAggregationRequired(); },
        ];

        $missing = array_diff_key($implemented, $assertions);
        $this->assertCount(0, $missing, 'missing tests for: ' . json_encode($missing));
        $this->assertCount(38, $implemented);
        $this->assertCount(count($implemented), $assertions);

        foreach ($assertions as $name => $assertion) {
            if (array_key_exists($name, $overrides)) {
                $this->assertEquals($overrides[$name], $assertion($decorator), sprintf("Invalid expectancy in method override for property '%s'", $name));
            } else {
                $this->assertSame($assertion($adapter), $assertion($decorator), sprintf("Invalid expectancy in abstract method for property '%s'", $name));
            }
        }
    }

    /**
     *  This is used to test if all ORM entity values are implemented in the IdentityProviderEntityInterface
     */
    protected function getOrmEntityIdentityProviderValues()
    {
        /**
         * @deprecated: These coins are no longer used in EngineBlock and will be removed in release 6.2
         */
        $skipParameters = [
            'publishInEdugain',
            'publishInEduGainDate',
        ];
        $skipMethods = [
            'accept',
            'getDisplayName',
            'setConsentSettings',
            'toggleWorkflowState',
            'hasCompleteOrganizationData',
            'setDiscoveries'
        ];

        // Get all state from the old mutable entity
        $parameters = $this->getParameters(IdentityProvider::class, $skipParameters);
        $methods = $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods(IdentityProvider::class, $skipMethods)));
        return array_merge($parameters, $methods);
    }

    /**
     * This is used to test if all ORM entity values are implemented in the IdentityProviderEntityInterface
     */
    protected function getOrmEntityServiceProviderValues()
    {
        /**
         * @deprecated: These coins are no longer used in EngineBlock and will be removed in release 6.2
         */
        $skipParameters = [
            'publishInEdugain',
            'publishInEduGainDate',
        ];
        $skipMethods = [
            'accept',
            'getDisplayName',
            'getOrganizationName',
            'toggleWorkflowState',
        ];

        // Get all state from the old mutable entity
        $parameters = $this->getParameters(ServiceProvider::class, $skipParameters);
        $methods = $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods(ServiceProvider::class, $skipMethods)));

        return array_merge($parameters, $methods);
    }

    /**
     *  This is used to return all values that are implemented in the IdentityProviderEntityInterface
     */
    protected function getIdentityProviderValues(string $identityProviderInterface)
    {

        $values = $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods($identityProviderInterface, ['hasCompleteOrganizationData'])));

        $replaceParameters = [
            'name' => [
                "nameNl",
                "nameEn",
                "namePt",
            ],
            'description' => [
                "descriptionNl",
                "descriptionEn",
                "descriptionPt",
            ],
            'displayName' => [
                "displayNameNl",
                "displayNameEn",
                "displayNamePt",
            ],
            'organization' => [
                "organizationNl",
                "organizationEn",
                "organizationPt",
            ],
            'keywords' => [
                "keywordsNl",
                "keywordsEn",
                "keywordsPt",
            ],
        ];

        // replace parameters
        foreach ($replaceParameters as $value => $replacements) {
            $this->assertArrayHasKey($value, $values);
            foreach ($replacements as $replacement) {
                $values[$replacement] = $values[$value];
            }
            unset($values[$value]);
        }

        return $values;
    }

    /**
     *  This is used to return all values that are implemented in the IdentityProviderEntityInterface
     */
    protected function getServiceProviderValues(string $serviceProviderInterface)
    {
        $values = $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods($serviceProviderInterface, ['hasCompleteOrganizationData'])));

        $replaceParameters = [
            'name' => [
                "nameNl",
                "nameEn",
                "namePt",
            ],
            'description' => [
                "descriptionNl",
                "descriptionEn",
                "descriptionPt",
            ],
            'displayName' => [
                "displayNameNl",
                "displayNameEn",
                "displayNamePt",
            ],
            'organization' => [
                "organizationNl",
                "organizationEn",
                "organizationPt",
            ],
            'keywords' => [
                "keywordsNl",
                "keywordsEn",
                "keywordsPt",
            ],
            'supportUrl' => [
                "supportUrlNl",
                "supportUrlEn",
                "supportUrlPt",
            ],
        ];

        // replace parameters
        foreach ($replaceParameters as $value => $replacements) {
            $this->assertArrayHasKey($value, $values);
            foreach ($replacements as $replacement) {
                $values[$replacement] = $values[$value];
            }
            unset($values[$value]);
        }

        return $values;
    }

    /**
     *  Mock a doctrine ORM entity to use in the adapter
     */
    protected function getOrmEntityIdentityProviderMock(array $values): IdentityProvider
    {
        $entity = new IdentityProvider('entityId');

        $reflection = new ReflectionClass(IdentityProvider::class);

        foreach($values as $key => $value){
            $reflectionProperty = $reflection->getProperty($key);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($entity, $value);
        }

        return $entity;
    }

    /**
     *  Mock a doctrine ORM entity to use in the adapter
     */
    protected function getOrmEntityServiceProviderMock(array $values): ServiceProvider
    {
        $entity = new ServiceProvider('entityId');

        $reflection = new ReflectionClass(ServiceProvider::class);

        foreach($values as $key => $value){
            $reflectionProperty = $reflection->getProperty($key);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($entity, $value);
        }

        return $entity;
    }

    /**
     *  Get the properties to use in a mocked doctrine ORM entity
     */
    protected function getIdentityProviderMockProperties(
        bool $emptyDisplayNameEn = false,
        bool $emptyDisplayNameNl = false
    ): array {
        $displayNameEn = ',"en":{"value":"display-name-en","language":"en"}';
        $displayNameNl = ',"nl":{"value":"display-name-nl","language":"nl"}';
        if ($emptyDisplayNameEn) {
            $displayNameEn = '';
        }
        if ($emptyDisplayNameNl) {
            $displayNameNl = '';
        }
        $mduiJson = sprintf(
            '{"DisplayName":{"name":"DisplayName","values":{"pt":{"value":"display-name-pt","language":"pt"}%s%s}},"Description":{"name":"Description","values":{"en":{"value":"description-en","language":"en"},"nl":{"value":"description-nl","language":"nl"},"pt":{"value":"description-pt","language":"pt"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}',
            $displayNameEn,
            $displayNameNl
        );
        $mdui = Mdui::fromJson($mduiJson);
        return [
            'id' => 12,
            'mdui' => $mdui,
            'entityId' => 'entity-id',
            'nameNl' => 'name-nl',
            'nameEn' => 'name-en',
            'namePt' => 'name-pt',
            'descriptionNl' => 'description-nl',
            'descriptionEn' => 'description-en',
            'descriptionPt' => 'description-pt',
            'displayNameNl' => 'display-name-nl',
            'displayNameEn' => 'display-name-en',
            'displayNamePt' => 'display-name-pt',
            'logo' => $this->createMock(Logo::class),
            'organizationNl' => $this->createMock(Organization::class),
            'organizationEn' => $this->createMock(Organization::class),
            'organizationPt' => $this->createMock(Organization::class),
            'keywordsNl' => 'keywords-nl',
            'keywordsEn' => 'keyword-en',
            'keywordsPt' => 'keyword-pt',
            'certificates' => [
                $this->createMock(X509Certificate::class),
                $this->createMock(X509Certificate::class),
            ],
            'workflowState' => 'workflow-state',
            'contactPersons' => [
                $this->createMock(ContactPerson::class),
                $this->createMock(ContactPerson::class),
            ],
            'nameIdFormat' => 'name-id-format',
            'supportedNameIdFormats' => [
                'name-id-format-1',
                'name-id-format-2',
            ],
            'singleLogoutService' => $this->createMock(Service::class),
            'requestsMustBeSigned' => true,
            'manipulation' => 'manipulation',
            'coins' => $this->createMock(Coins::class),
            'enabledInWayf' => true,
            'singleSignOnServices' => [
                $this->createMock(Service::class),
                $this->createMock(Service::class),
            ],
            'consentSettings' => $this->createMock(ConsentSettings::class),
            'shibMdScopes' => [
                $this->createMock(ShibMdScope::class),
                $this->createMock(ShibMdScope::class),
            ],
            'discoveries' => [
                $this->createMock(Discovery::class),
            ]
        ];
    }

    /**
     *  Get the properties to use in a mocked doctrine ORM entity
     */
    protected function getServiceProviderMockProperties()
    {
        $attributeReleasePolicy = $this->createMock(AttributeReleasePolicy::class);
        $attributeReleasePolicy->method('getRulesWithSourceSpecification')
            ->willReturn([
                ['src' => 'test'],
            ]);
        $mduiJson = '{"DisplayName":{"name":"DisplayName"},"Description":{"name":"Description","values":{"en":{"value":"Description EN","language":"en"},"nl":{"value":"Description NL","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        $mdui = Mdui::fromJson($mduiJson);
        return [
            'id' => 12,
            'mdui' => $mdui,
            'entityId' => 'entity-id',
            'nameNl' => 'name-nl',
            'nameEn' => 'name-en',
            'namePt' => 'name-pt',
            'descriptionNl' => 'description-nl',
            'descriptionEn' => 'description-en',
            'descriptionPt' => 'description-pt',
            'displayNameNl' => 'display-name-nl',
            'displayNameEn' => 'display-name-en',
            'displayNamePt' => 'display-name-pt',
            'logo' => $this->createMock(Logo::class),
            'organizationNl' => $this->createMock(Organization::class),
            'organizationEn' => $this->createMock(Organization::class),
            'keywordsNl' => 'keywords-nl',
            'keywordsEn' => 'keyword-en',
            'keywordsPt' => 'keyword-pt',
            'certificates' => [
                $this->createMock(X509Certificate::class),
                $this->createMock(X509Certificate::class),
            ],
            'workflowState' => 'workflow-state',
            'contactPersons' => [
                $this->createMock(ContactPerson::class),
                $this->createMock(ContactPerson::class),
            ],
            'nameIdFormat' => 'name-id-format',
            'supportedNameIdFormats' => [
                'name-id-format-1',
                'name-id-format-2',
            ],
            'singleLogoutService' => $this->createMock(Service::class),
            'requestsMustBeSigned' => true,
            'manipulation' => 'manipulation',
            'coins' => $this->createMock(Coins::class),
            'attributeReleasePolicy' => $attributeReleasePolicy,
            'assertionConsumerServices' => [
                $this->createMock(IndexedService::class),
                $this->createMock(IndexedService::class),
            ],
            'allowedIdpEntityIds' => [
                'entity-id-1',
                'entity-id-2',
            ],
            'allowAll' => true,
            'requestedAttributes' => [
                $this->createMock(RequestedAttribute::class),
                $this->createMock(RequestedAttribute::class),
            ],
            'supportUrlEn' => 'support-url-en',
            'supportUrlNl' => 'support-url-nl',
            'supportUrlPt' => 'support-url-pt',
        ];
    }

    private function getParameters($className, $skipParameters = [])
    {
        $results = [];
        $class = new ReflectionClass($className);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if (!$property->isStatic() && !in_array($property->getName(), $skipParameters)) {
                preg_match('/@var (.*)\n/', $property->getDocComment(), $matches);
                $results[$property->getName()] = $matches[1];
            }
        }

        return $results;
    }

    private function getMethods($className, $skipMethods = [])
    {
        $results = [];
        $class = new ReflectionClass($className);
        $methods = $class->getMethods(ReflectionProperty::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isStatic() && !in_array($method->getName(), $skipMethods)) {
                if ($method->getDocComment()) {
                    preg_match('/@return (.*)\n/', $method->getDocComment(), $matches);
                    $results[$method->getName()] = $matches[1];
                } elseif ($method->getReturnType()) {
                    $returnType = $method->getReturnType()->getName();
                    $results[$method->getName()] = $returnType;
                }
            }
        }
        return $results;
    }

    private function getGetterBaseNameFromMethodNames(array $methodNames)
    {
        $results = [];
        foreach ($methodNames as $name => $type) {
            if (substr($name, 0, 3) == 'get') {
                $name = lcfirst(substr($name, 3));
            } else if (substr($name, 0, 2) == 'is') {
                $name = lcfirst(substr($name, 2));
            } else {
                throw new \Exception('INVALID: '. $name);
            }

            $results[$name] = $type;
        }

        return $results;
    }

    private function getGettersFromMethodNames(array $methodNames)
    {
        $results = [];
        foreach ($methodNames as $name => $type) {
            if ($name == '__construct') {
                continue;
            }

            $results[$name] = $type;
        }

        return $results;
    }
}
