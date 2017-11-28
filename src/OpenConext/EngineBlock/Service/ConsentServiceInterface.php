<?php

namespace OpenConext\EngineBlock\Service;

use Exception;
use OpenConext\EngineBlock\Authentication\Dto\Consent;
use OpenConext\EngineBlock\Authentication\Dto\ConsentList;
use OpenConext\EngineBlock\Authentication\Model\Consent as ConsentEntity;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\Value\Saml\EntityId;
use Psr\Log\LoggerInterface;

interface ConsentServiceInterface
{
    /**
     * @param string $userId
     * @return ConsentList
     */
    public function findAllFor($userId);

    /**
     * @param string $userId
     * @return int
     */
    public function countAllFor($userId);
}
