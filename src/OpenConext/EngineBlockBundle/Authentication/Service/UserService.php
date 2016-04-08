<?php

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

    /**
     * @var CollabPersonIdHasher
     */
    private $collabPersonIdHasher;

    public function __construct(UserRepository $userRepository, CollabPersonIdHasher $collabPersonIdHasher)
    {
        $this->userRepository       = $userRepository;
        $this->collabPersonIdHasher = $collabPersonIdHasher;
    }

    public function register(User $user)
    {
        $userEntity                   = new UserEntity();
        $userEntity->collabPersonId   = $this->collabPersonIdHasher->hash($user->getCollabPersonId());
        $userEntity->collabPersonUuid = $user->getCollabPersonUuid();

        $this->userRepository->save($userEntity);
    }

    public function findUserBy(CollabPersonId $collabPersonId)
    {
        $hashedCollabPersonId = $this->collabPersonIdHasher->hash($collabPersonId);

        $userEntity = $this->userRepository->findByCollabPersonId($hashedCollabPersonId);

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
                'Attempting to get user by %s failed, no such user exists.',
                $collabPersonId
            ));
        }

        return $user;
    }

    public function removeUserWith(CollabPersonId $collabPersonId)
    {
        $hashedCollabPersonId = $this->collabPersonIdHasher->hash($collabPersonId);

        $this->userRepository->deleteUserWithCollabPersonId($hashedCollabPersonId);
    }
}
