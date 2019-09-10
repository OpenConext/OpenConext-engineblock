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

namespace OpenConext\EngineBlockBundle\Authentication\Service;

use OpenConext\EngineBlock\Authentication\Exception\RuntimeException;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlockBundle\Authentication\Entity\User as UserEntity;
use OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository;

class UserService implements UserDirectory
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository       = $userRepository;
    }

    public function register(User $user)
    {
        $userEntity                   = new UserEntity();
        $userEntity->collabPersonId   = $user->getCollabPersonId();
        $userEntity->collabPersonUuid = $user->getCollabPersonUuid();

        $this->userRepository->save($userEntity);
    }

    public function findUserBy(CollabPersonId $collabPersonId)
    {
        $userEntity = $this->userRepository->findByCollabPersonId($collabPersonId);

        if (!$userEntity) {
            return null;
        }

        return new User($collabPersonId, $userEntity->collabPersonUuid);
    }

    public function getUserBy(CollabPersonId $collabPersonId)
    {
        $user = $this->findUserBy($collabPersonId);

        if (!$user) {
            throw new RuntimeException(sprintf(
                'Attempting to get user by "%s" failed, no such user exists.',
                $collabPersonId
            ));
        }

        return $user;
    }

    public function removeUserWith(CollabPersonId $collabPersonId)
    {
        $this->userRepository->deleteUserWithCollabPersonId($collabPersonId);
    }
}
