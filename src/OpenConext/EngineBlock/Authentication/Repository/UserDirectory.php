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

namespace OpenConext\EngineBlock\Authentication\Repository;

use OpenConext\EngineBlock\Authentication\Exception\RuntimeException;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;

interface UserDirectory
{
    /**
     * @param User $user
     * @return void
     */
    public function register(User $user);

    /**
     * @param CollabPersonId $collabPersonId
     * @return null|User
     */
    public function findUserBy(CollabPersonId $collabPersonId);

    /**
     * @param CollabPersonId $collabPersonId
     * @return User
     * @throws RuntimeException when the requested user cannot be found
     */
    public function getUserBy(CollabPersonId $collabPersonId);

    /**
     * @param CollabPersonId $collabPersonId
     * @return void
     */
    public function removeUserWith(CollabPersonId $collabPersonId);
}
