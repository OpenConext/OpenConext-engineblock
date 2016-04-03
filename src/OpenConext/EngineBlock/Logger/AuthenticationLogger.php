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
     * @param Entity         $serviceProvider
     * @param Entity         $identityProvider
     * @param CollabPersonId $collabPersonId
     * @param KeyId          $keyId
     */
    public function logGrantedLogin(
        Entity $serviceProvider,
        Entity $identityProvider,
        CollabPersonId $collabPersonId,
        KeyId $keyId = null
    ) {
        $this->logger->info(
            'login granted',
            [
                'login_stamp'   => (new DateTime())->format(DateTime::ISO8601),
                'user_id'       => $collabPersonId->getCollabPersonId(),
                'sp_entity_id'  => $serviceProvider->getEntityId()->getEntityId(),
                'idp_entity_id' => $identityProvider->getEntityId()->getEntityId(),
                'key_id'        => $keyId ? $keyId->getKeyId() : '',
            ]
        );
    }
}
