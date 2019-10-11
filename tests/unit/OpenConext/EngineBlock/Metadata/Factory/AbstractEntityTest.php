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
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractEntityTest extends TestCase
{
    /**
     * Create an instance which could be used by decorators
     */
    public function createIdentityProviderAdapter()
    {
        $values = $this->getIdentityProviderMockProperties();
        $ormEntity = $this->getOrmEntityIdentityProviderMock($values);
        return new IdentityProviderEntity($ormEntity);
    }

    /**
     * Create an instance which could be used by decorators
     */
    public function createServiceProviderAdapter()
    {
        $values = $this->getServiceProviderMockProperties();
        $ormEntity = $this->getOrmEntityServiceProviderMock($values);
        return new ServiceProviderEntity($ormEntity);
    }

    /**
     * Run the supplied assertions this is used to test the decorators
     */
    protected function runIdentityProviderAssertions(array $assertions)
    {
        $implemented = $this->getIdentityProviderValues(IdentityProviderEntityInterface::class);

        foreach ($assertions as $name => $assertion) {
            $this->assertSame($assertion[0], $assertion[1], 'Invalid '.$name);
        }

        $missing = array_diff_key($implemented, $assertions);
        $this->assertCount(0, $missing, 'missing tests for: ' . json_encode($missing));
        $this->assertCount(27, $implemented);
        $this->assertCount(count($implemented), $assertions);
    }

    /**
     * Run the supplied assertions this is used to test the decorators
     */
    protected function runServiceProviderAssertions(array $assertions)
    {
        $implemented = $this->getIdentityProviderValues(ServiceProviderEntityInterface::class);

        foreach ($assertions as $name => $assertion) {
            $this->assertSame($assertion[0], $assertion[1], 'Invalid '.$name);
        }

        $missing = array_diff_key($implemented, $assertions);
        $this->assertCount(0, $missing, 'missing tests for: ' . json_encode($missing));
        $this->assertCount(33, $implemented);
        $this->assertCount(count($implemented), $assertions);
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
        return $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods($identityProviderInterface)));
    }

    /**
     *  This is used to return all values that are implemented in the IdentityProviderEntityInterface
     */
    protected function getServiceProviderValues(string $serviceProviderInterface)
    {
        return $this->getGetterBaseNameFromMethodNames($this->getGettersFromMethodNames($this->getMethods($serviceProviderInterface)));
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
    protected function getIdentityProviderMockProperties()
    {
        return [
            'id' => 12,
            'entityId' => 'entity-id',
            'nameNl' => 'name-nl',
            'nameEn' => 'name-en',
            'descriptionNl' => 'description-nl',
            'descriptionEn' => 'description-en',
            'displayNameNl' => 'display-name-nl',
            'displayNameEn' => 'display-name-en',
            'logo' => $this->createMock(Logo::class),
            'organizationNl' => $this->createMock(Organization::class),
            'organizationEn' => $this->createMock(Organization::class),
            'keywordsNl' => 'keywords-nl',
            'keywordsEn' => 'keyword-en',
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
            'responseProcessingService' => $this->createMock(Service::class),
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

        return [
            'id' => 12,
            'entityId' => 'entity-id',
            'nameNl' => 'name-nl',
            'nameEn' => 'name-en',
            'descriptionNl' => 'description-nl',
            'descriptionEn' => 'description-en',
            'displayNameNl' => 'display-name-nl',
            'displayNameEn' => 'display-name-en',
            'logo' => $this->createMock(Logo::class),
            'organizationNl' => $this->createMock(Organization::class),
            'organizationEn' => $this->createMock(Organization::class),
            'keywordsNl' => 'keywords-nl',
            'keywordsEn' => 'keyword-en',
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
            'responseProcessingService' => $this->createMock(Service::class),
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
                preg_match('/@return (.*)\n/', $method->getDocComment(), $matches);
                $results[$method->getName()] = $matches[1];
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
