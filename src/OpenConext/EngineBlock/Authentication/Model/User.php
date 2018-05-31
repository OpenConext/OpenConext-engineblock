<?php

namespace OpenConext\EngineBlock\Authentication\Model;

use JsonSerializable;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;

class User implements JsonSerializable
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

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'collab_person_id' => $this->getCollabPersonId()->getCollabPersonId(),
            'uuid' => $this->getCollabPersonUuid()->getUuid(),
        ];
    }
}
