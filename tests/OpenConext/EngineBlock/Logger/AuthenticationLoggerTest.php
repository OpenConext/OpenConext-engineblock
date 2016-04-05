<?php

namespace OpenConext\EngineBlock\Logger;

use EngineBlock_UserDirectory;
use Mockery as m;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class AuthenticationLoggerTest extends UnitTest
{
    /**
     * This test may seem a bit overdone, however the format and values of logging
     * are actually very valuable: they are used for statistical purpose. Other
     * tools depend on the format and contents of the log. Therefor it is worth
     * to write such a test to ensure compatibility.
     *
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function the_logged_context_contains_all_required_information()
    {
        // raw data so we can compare later on
        $serviceProviderEntityId  = 'SpEntityId';
        $identityProviderEntityId = 'IdpEntityId';
        $collabPersonIdValue      = EngineBlock_UserDirectory::URN_COLLAB_PERSON_NAMESPACE . ':openconext:some-person';
        $keyIdValue               = '20160403';

        $serviceProvider  = new Entity(new EntityId($serviceProviderEntityId), EntityType::SP());
        $identityProvider = new Entity(new EntityId($identityProviderEntityId), EntityType::IdP());
        $collabPersonId   = new CollabPersonId($collabPersonIdValue);
        $keyId            = new KeyId($keyIdValue);

        // do note we omit login_stamp here as we check presence separately, but don't want to compare the value
        $expected = [
            'sp_entity_id' => $serviceProviderEntityId,
            'idp_entity_id' => $identityProviderEntityId,
            'user_id' => $collabPersonIdValue,
            'key_id' => $keyIdValue
        ];

        $mockLogger = m::mock('\Psr\Log\LoggerInterface');
        $mockLogger
            ->shouldReceive('info')
            ->withArgs([
                m::any(),
                m::on(function ($context) use ($expected) {
                    if (!array_key_exists('login_stamp', $context)) {
                        return false;
                    }
                    foreach ($expected as $key => $value) {
                        if (!isset($context[$key])) {
                            return false;
                        }

                        if (!$context[$key] === $value) {
                            return false;
                        }
                    }

                    return true;
                })
            ])
            ->once();

        $authenticationLogger = new AuthenticationLogger($mockLogger);
        $authenticationLogger->logGrantedLogin($serviceProvider, $identityProvider, $collabPersonId, $keyId);
    }
}
