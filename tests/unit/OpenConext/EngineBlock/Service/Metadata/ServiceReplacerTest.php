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

namespace OpenConext\EngineBlock\Service\Metadata;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\ServiceReplacingException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderMetadata;
use OpenConext\EngineBlock\Metadata\Service;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;

class ServiceReplacerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var IdentityProvider
     */
    private $entity;

    /**
     * @var IdentityProvider
     */
    private $proxyEntity;

    public function setUp()
    {
        $this->entity = new IdentityProvider('https://sp.example.edu');
        $this->entity->singleSignOnServices[] = new Service(Constants::BINDING_HTTP_REDIRECT, 'redirectlocation');

        $this->proxyEntity = m::mock(EngineBlockIdentityProviderMetadata::class);
    }

    public function test_services_are_replaced()
    {
        $ssoServices = [];
        $ssoServices[] = new Service('proxyRedirectLocation', Constants::BINDING_HTTP_REDIRECT);
        $ssoServices[] = new Service('proxyPostLocation', Constants::BINDING_HTTP_POST);

        $this->proxyEntity = m::mock(EngineBlockIdentityProviderMetadata::class);
        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($ssoServices);

        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', Constants::BINDING_HTTP_REDIRECT),
            new Service('newLocation', Constants::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    public function test_services_are_added()
    {
        $ssoServices = [];
        $ssoServices[] = new Service('proxyRedirectLocation', Constants::BINDING_HTTP_REDIRECT);
        $ssoServices[] = new Service('proxyPostLocation', Constants::BINDING_HTTP_POST);

        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($ssoServices);

        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        unset($this->entity->singleSignOnServices);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', Constants::BINDING_HTTP_REDIRECT),
            new Service('newLocation', Constants::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    public function test_missing_service_metadata_throws_exception()
    {
        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn([]);

        $this->expectException(ServiceReplacingException::class);
        $this->expectExceptionMessage('No "getSingleSignOnServices" service bindings configured in EngineBlock metadata');

        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);

    }

    public function test_missing_service_binding_metadata_throws_exception()
    {
        $ssoServices = [];
        $ssoServices[] = new Service('proxyRedirectLocation', Constants::BINDING_HTTP_REDIRECT);
        $ssoServices[] = new Service('proxyPostLocation', null);

        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($ssoServices);

        $this->expectException(ServiceReplacingException::class);
        $this->expectExceptionMessage('Service "SingleSignOnService" configured without a Binding in EngineBlock metadata');

        unset($this->proxyEntity->singleSignOnServices[0]->binding);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function test_invalid_service_binding_metadata_throws_exception()
    {
        $ssoServices = [];
        $ssoServices[] = new Service('proxyRedirectLocation', Constants::BINDING_HTTP_REDIRECT);
        $ssoServices[] = new Service('proxyPostLocation', 'foo');

        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($ssoServices);

        $this->expectException(ServiceReplacingException::class);
        $this->expectExceptionMessage('Service "SingleSignOnService" has an invalid binding "foo" configured in EngineBlock metadata');

        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function test_no_valid_service_bindings_found_in_metadata_throws_exception()
    {
        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn([]);

        $this->expectException(ServiceReplacingException::class);
        $this->expectExceptionMessage('No "getSingleSignOnServices" service bindings configured in EngineBlock metadata');

        $this->proxyEntity->singleSignOnServices = array();
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function test_no_valid_service_bindings_found_in_metadata_is_allowed_when_optional()
    {
        $this->proxyEntity
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn([]);

        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::OPTIONAL);
        $replacer->replace($this->entity, 'newLocation');
    }
}
