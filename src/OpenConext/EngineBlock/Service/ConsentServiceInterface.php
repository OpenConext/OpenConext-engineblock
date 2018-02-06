<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Authentication\Dto\ConsentList;

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
