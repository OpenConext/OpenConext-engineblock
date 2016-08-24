<?php

namespace OpenConext\EngineBlock\Authentication\Model;

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;

class User
{
    /**
     * @var CollabPersonId
     */
    private $collabPersonId;

    /**
     * @var CollabPersonUuid
     */
    private $collabPersonUuid;

    public function __construct(CollabPersonId $collabPersonId, CollabPersonUuid $collabPersonUuid)
    {
        $this->collabPersonId   = $collabPersonId;
        $this->collabPersonUuid = $collabPersonUuid;
    }

    /**
     * @return CollabPersonId
     */
    public function getCollabPersonId()
    {
        return $this->collabPersonId;
    }

    /**
     * @return CollabPersonUuid
     */
    public function getCollabPersonUuid()
    {
        return $this->collabPersonUuid;
    }
}
