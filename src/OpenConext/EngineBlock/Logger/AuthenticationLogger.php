<?php

namespace OpenConext\EngineBlock\Logger;

use DateTime;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\Value\Saml\Entity;
use Psr\Log\LoggerInterface;

class AuthenticationLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * KeyId is nullable in order to be able to differentiate between asking no specific key,
     * the default key KeyId('default') and a specific key.
     *
     * @param Entity         $serviceProvider
     * @param Entity         $identityProvider
     * @param CollabPersonId $collabPersonId
     * @param array          $proxiedServiceProviders
     * @param string         $workflowState
     * @param KeyId|null     $keyId
     */
    public function logGrantedLogin(
        Entity $serviceProvider,
        Entity $identityProvider,
        CollabPersonId $collabPersonId,
        array $proxiedServiceProviders,
        $workflowState,
        KeyId $keyId = null
    ) {
        $proxiedServiceProviderEntityIds = array_map(
            function (Entity $entity) {
                return $entity->getEntityId()->getEntityId();
            },
            $proxiedServiceProviders
        );

        $this->logger->info(
            'login granted',
            [
                //This is actually ISO 8601, the DateTime::ISO8601 misses the colon in the TZ part (known bug)
                'login_stamp'           => (new DateTime())->format(DateTime::ATOM),
                'user_id'               => $collabPersonId->getCollabPersonId(),
                'sp_entity_id'          => $serviceProvider->getEntityId()->getEntityId(),
                'idp_entity_id'         => $identityProvider->getEntityId()->getEntityId(),
                'key_id'                => $keyId ? $keyId->getKeyId() : null,
                'proxied_sp_entity_ids' => $proxiedServiceProviderEntityIds,
                'workflow_state'        => $workflowState
            ]
        );
    }
}
