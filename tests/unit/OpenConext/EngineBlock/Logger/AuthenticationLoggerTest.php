<?php

namespace OpenConext\EngineBlock\Logger;

use DateTime;
use EngineBlock_UserDirectory;
use Mockery as m;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
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
        $collabPersonIdValue      = 'urn:collab:person:openconext:some-person';
        $keyIdValue               = '20160403';
        $spProxy1EntityId         = 'SpProxy1EntityId';
        $spProxy2EntityId         = 'SpProxy2EntityId';

        $serviceProvider       = new Entity(new EntityId($serviceProviderEntityId), EntityType::SP());
        $identityProvider      = new Entity(new EntityId($identityProviderEntityId), EntityType::IdP());
        $collabPersonId        = new CollabPersonId($collabPersonIdValue);
        $keyId                 = new KeyId($keyIdValue);
        $serviceProviderProxy1 = new Entity(new EntityId($spProxy1EntityId), EntityType::SP());
        $serviceProviderProxy2 = new Entity(new EntityId($spProxy2EntityId), EntityType::SP());

        // do note we omit login_stamp here as we check presence separately, but don't want to compare the value
        $expected = [
            'sp_entity_id' => $serviceProviderEntityId,
            'idp_entity_id' => $identityProviderEntityId,
            'user_id' => $collabPersonIdValue,
            'key_id' => $keyIdValue,
            'proxied_sp_entity_ids' => [$spProxy1EntityId, $spProxy2EntityId],
            'workflow_state' => AbstractRole::WORKFLOW_STATE_PROD,
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

                    if (!$this->assertFormatting($context['login_stamp'], 'Y-m-d\TH:i:s.uP')) {
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
        $authenticationLogger->logGrantedLogin(
            $serviceProvider,
            $identityProvider,
            $collabPersonId,
            [$serviceProviderProxy1, $serviceProviderProxy2],
            AbstractRole::WORKFLOW_STATE_PROD,
            $keyId
        );
    }

    private function assertFormatting($loginStamp, $format)
    {
        $dateTime = DateTime::createFromFormat($format, $loginStamp);

        return $dateTime && $dateTime->format($format) === $loginStamp;
    }
}
