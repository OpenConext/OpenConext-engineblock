<?php

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;

class UserDirectoryAdapter
{
    /**
     * @var UserDirectory
     */
    private $userDirectory;

    public function __construct(UserDirectory $userDirectory)
    {
        $this->userDirectory = $userDirectory;
    }

    /**
     * @param string $uid
     * @param string $schacHomeOrganization
     * @return User
     */
    public function registerUser($uid, $schacHomeOrganization)
    {
        $collabPersonId = CollabPersonId::generateFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $user = new User($collabPersonId, CollabPersonUuid::generate());

        $this->userDirectory->register($user);

        return $user;
    }

    /**
     * @param string $collabPersonId
     * @return null|User
     */
    public function findUserBy($collabPersonId)
    {
        return $this->userDirectory->findUserBy(new CollabPersonId($collabPersonId));
    }

    /**
     * @param string $collabPersonId
     * @return User
     */
    public function getUserBy($collabPersonId)
    {
        return $this->userDirectory->getUserBy(new CollabPersonId($collabPersonId));
    }

    /**
     * @param string $collabPersonId
     */
    public function deleteUserWith($collabPersonId)
    {
        $this->userDirectory->removeUserWith(new CollabPersonId($collabPersonId));
    }
}
