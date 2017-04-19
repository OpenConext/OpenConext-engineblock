<?php

namespace OpenConext\EngineBlock\Authentication\Repository;

use OpenConext\EngineBlock\Authentication\Model\Consent;

interface ConsentRepository
{
    /**
     * @param string $userId
     *
     * @return Consent[]
     */
    public function findAllFor($userId);
}
