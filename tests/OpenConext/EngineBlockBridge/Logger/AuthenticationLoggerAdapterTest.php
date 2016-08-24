<?php

namespace OpenConext\EngineBlockBridge\Logger;

use EngineBlock_UserDirectory;
use Mockery as m;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\EngineBlock\Logger\AuthenticationLogger;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use OpenConext\Mockery\Matcher\ValueObjectEqualsMatcher;
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
        $collabPersonIdValue      = EngineBlock_UserDirectory::URN_COLLAB_PERSON_NAMESPACE . ':openconext:some-person';
        $keyIdValue               = '20160403';

        $mockAuthenticationLogger = m::mock(AuthenticationLogger::class);
        $mockAuthenticationLogger
            ->shouldReceive('logGrantedLogin')
            ->withArgs([
                new ValueObjectEqualsMatcher(new Entity(new EntityId($serviceProviderEntityId), EntityType::SP())),
                new ValueObjectEqualsMatcher(new Entity(new EntityId($identityProviderEntityId), EntityType::IdP())),
                new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonIdValue)),
                new ValueObjectEqualsMatcher(new KeyId($keyIdValue))
            ])
            ->once();

        $authenticationLoggerAdapter = new AuthenticationLoggerAdapter($mockAuthenticationLogger);
        $authenticationLoggerAdapter->logLogin(
            new ServiceProvider($serviceProviderEntityId),
            new IdentityProvider($identityProviderEntityId),
            $collabPersonIdValue,
            $keyIdValue
        );
    }
}
