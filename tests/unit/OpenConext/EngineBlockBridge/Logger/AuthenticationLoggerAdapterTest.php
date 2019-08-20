<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBridge\Logger;

use EngineBlock_UserDirectory;
use Mockery as m;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\EngineBlock\Logger\AuthenticationLogger;
use OpenConext\Mockery\Matcher\ValueObjectEqualsMatcher;
use OpenConext\Mockery\Matcher\ValueObjectListEqualsMatcher;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class AuthenticationLoggerAdapterTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBridge
     * @group Logger
     */
    public function arguments_are_converted_correctly()
    {
        $serviceProviderEntityId  = 'SpEntityId';
        $identityProviderEntityId = 'IdpEntityId';
        $collabPersonIdValue      = 'urn:collab:person:openconext:some-person';
        $keyIdValue               = '20160403';
        $spProxy1EntityId         = 'SpProxy1EntityId';
        $spProxy2EntityId         = 'SpProxy2EntityId';

        $mockAuthenticationLogger = m::mock(AuthenticationLogger::class);
        $mockAuthenticationLogger
            ->shouldReceive('logGrantedLogin')
            ->withArgs(
                [
                    new ValueObjectEqualsMatcher(new Entity(new EntityId($serviceProviderEntityId), EntityType::SP())),
                    new ValueObjectEqualsMatcher(
                        new Entity(new EntityId($identityProviderEntityId), EntityType::IdP())
                    ),
                    new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonIdValue)),
                    new ValueObjectListEqualsMatcher(
                        [
                            new Entity(new EntityId($spProxy1EntityId), EntityType::SP()),
                            new Entity(new EntityId($spProxy2EntityId), EntityType::SP()),
                        ]
                    ),
                    AbstractRole::WORKFLOW_STATE_PROD,
                    new ValueObjectEqualsMatcher(new KeyId($keyIdValue)),
                ]
            )
            ->once();

        $authenticationLoggerAdapter = new AuthenticationLoggerAdapter($mockAuthenticationLogger);
        $authenticationLoggerAdapter->logLogin(
            new ServiceProvider($serviceProviderEntityId),
            new IdentityProvider($identityProviderEntityId),
            $collabPersonIdValue,
            $keyIdValue,
            [
                new ServiceProvider($spProxy1EntityId),
                new ServiceProvider($spProxy2EntityId),
            ]
        );
    }
}
