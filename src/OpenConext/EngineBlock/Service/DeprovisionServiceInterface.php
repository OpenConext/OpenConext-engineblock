<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;

interface DeprovisionServiceInterface
{
    /**
     * @param CollabPersonId $id
     * @return array
     */
    public function read(CollabPersonId $id);

    /**
     * @param CollabPersonId $id
     */
    public function delete(CollabPersonId $id);
}
