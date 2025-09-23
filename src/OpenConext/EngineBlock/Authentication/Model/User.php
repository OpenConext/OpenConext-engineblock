<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
    public function jsonSerialize(): array
    {
        return [
            'collab_person_id' => $this->getCollabPersonId()->getCollabPersonId(),
            'uuid' => $this->getCollabPersonUuid()->getUuid(),
        ];
    }
}
