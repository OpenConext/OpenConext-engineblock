<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use EngineBlock_Exception_MissingRequiredFields;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use Psr\Log\LoggerInterface;

class UserDirectoryAdapter
{
    /**
     * @var \OpenConext\EngineBlock\Authentication\Repository\UserDirectory
     */
    private $userDirectory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(UserDirectory $userDirectory, LoggerInterface $logger)
    {
        $this->userDirectory = $userDirectory;
        $this->logger = $logger;
    }

    /**
     * @param array $attributes
     * @return null|User
     * @throws EngineBlock_Exception_MissingRequiredFields
     *
     * @deprecated This method is only introduced to allow for a graceful rollover of LDAP to database backed
     *             UserDirectory. It contains Backwards Compatible code that should not be relied on (e.g. the throwing
     *             of an EngineBlock_Exception)
     */
    public function identifyUser(array $attributes)
    {
        if (!isset($attributes[Uid::URN_MACE][0])) {
            throw new EngineBlock_Exception_MissingRequiredFields(sprintf(
                'Missing required SAML2 field "%s" in attributes',
                Uid::URN_MACE
            ));
        }
        if (!isset($attributes[SchacHomeOrganization::URN_MACE][0])) {
            throw new EngineBlock_Exception_MissingRequiredFields(sprintf(
                'Missing required SAML2 field "%s" in attributes',
                SchacHomeOrganization::URN_MACE
            ));
        }

        $uid                   = $attributes[Uid::URN_MACE][0];
        $schacHomeOrganization = $attributes[SchacHomeOrganization::URN_MACE][0];

        $collabPersonUuid      = CollabPersonUuid::generate();
        $collabPersonId        = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $user = $this->userDirectory->findUserBy($collabPersonId);
        if ($user === null) {
            $this->logger->debug('User not found in database UserDirectory, registering User in database');

            $user = new User($collabPersonId, $collabPersonUuid);
            $this->userDirectory->register($user);
        }

        return $user;
    }

    /**
     * @param string $uid
     * @param string $schacHomeOrganization
     * @return User
     */
    public function registerUser($uid, $schacHomeOrganization)
    {
        $collabPersonId = CollabPersonId::generateWithReplacedAtSignFrom(
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
