<?php

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
