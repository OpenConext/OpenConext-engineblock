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

namespace OpenConext\EngineBlock\Stepup;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidStepupConfigurationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StepupEndpointTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_should_be_successful_populated()
    {
        $fileLocation = __DIR__ . '/../../../../resources/key/engineblock.crt';
        $endpoint = new StepupEndpoint('entity-id', 'https://sso-location', $fileLocation);

        $this->assertSame('entity-id', $endpoint->getEntityId());
        $this->assertSame('https://sso-location', $endpoint->getSsoLocation());
        $this->assertSame($fileLocation, $endpoint->getKeyFile());
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_entityId_should_not_throw_an_exception_on_initialization_when_invalid()
    {
        $endpoint = new StepupEndpoint(null, 'https://sso-location', 'tests/resources/key/engineblock.crt');
        $this->assertInstanceOf(StepupEndpoint::class, $endpoint);
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_ssoLocation_should_not_throw_an_exception_on_initialization_when_invalid()
    {
        $endpoint = new StepupEndpoint('entity-id', null, 'tests/resources/key/engineblock.crt');
        $this->assertInstanceOf(StepupEndpoint::class, $endpoint);
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_keyFile_should_not_throw_an_exception_on_initialization_when_invalid()
    {
        $endpoint = new StepupEndpoint('entity-id', 'https://sso-location', null);
        $this->assertInstanceOf(StepupEndpoint::class, $endpoint);
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_entityId_should_be_a_string()
    {
        $this->expectException(InvalidStepupConfigurationException::class);
        $this->expectExceptionMessage("Invalid stepup endpoint configuration: stepup.gateway.sfo.entity_id should be a string");

        $endpoint = new StepupEndpoint(null, 'https://sso-location', 'tests/resources/key/engineblock.crt');
        $endpoint->getEntityId();
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_ssoLocation_should_be_a_string()
    {
        $this->expectException(InvalidStepupConfigurationException::class);
        $this->expectExceptionMessage("Invalid stepup endpoint configuration: stepup.gateway.sfo.sso_location should be a string");

        $endpoint = new StepupEndpoint('entity-id', null, 'tests/resources/key/engineblock.crt');
        $endpoint->getSsoLocation();
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_keyFile_should_be_a_string()
    {
        $this->expectException(InvalidStepupConfigurationException::class);
        $this->expectExceptionMessage("Invalid stepup endpoint configuration: stepup.gateway.sfo.key_file should be a string");

        $endpoint = new StepupEndpoint('entity-id', 'https://sso-location', null);
        $endpoint->getKeyFile();
    }

    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_keyFile_should_be_a_file()
    {
        $this->expectException(InvalidStepupConfigurationException::class);
        $this->expectExceptionMessage("Invalid stepup endpoint configuration: stepup.gateway.sfo.key_file should be a valid file");

        $endpoint = new StepupEndpoint('entity-id', 'https://sso-location', 'tests/resources/key/non-existent-file.key');
        $endpoint->getKeyFile();
    }

    /**
     *
     * @param string $methodName
     */
    #[DataProvider('availableMethodProvider')]
    #[Group('Stepup')]
    #[Test]
    public function the_sfo_endpoint_object_mmethods_should_throw_an_exception_when_not_validated($methodName)
    {
        $this->expectException(InvalidStepupConfigurationException::class);

        $endpoint = new StepupEndpoint('', '', '');

        $endpoint->$methodName();
    }

    public static function availableMethodProvider()
    {
        $methods = [];
        $class = new ReflectionClass(StepupEndpoint::class);
        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() && $method->isPublic()) {
                $methods[] = [$method->getName()];
            }
        }
        return $methods;
    }
}
