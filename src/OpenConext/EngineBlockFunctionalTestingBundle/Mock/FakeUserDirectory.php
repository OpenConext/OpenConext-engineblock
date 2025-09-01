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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use EngineBlock_Exception_MissingRequiredFields;
use OpenConext\EngineBlock\Authentication\Exception\RuntimeException as AuthenticationRuntimeException;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlockBridge\Authentication\Repository\UserDirectoryAdapter;
use Symfony\Component\Filesystem\Filesystem;

class FakeUserDirectory extends UserDirectoryAdapter
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private static $directory = '/tmp/eb-fixtures/';

    /**
     * @var string
     */
    private static $fileName = 'user_directory.json';

    /**
     * overriding constructor so we can instantiate without arguments and load a possible cached
     * userdirectory
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $filePath = self::$directory . self::$fileName;
        if (!$this->filesystem->exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException(sprintf('Cannot read UserDirectory dump from "%s"', $filePath));
        }

        $users = json_decode($content, true);
        array_walk($users, function (&$user): void {
            $user = new User(
                new CollabPersonId($user['collab_person_id']),
                new CollabPersonUuid($user['collab_person_uuid'])
            );
        });
        $this->users = $users;
    }

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

        $collabPersonUuid = CollabPersonUuid::generate();
        $collabPersonId   = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $user = new User($collabPersonId, $collabPersonUuid);
        $this->users[$collabPersonId->getCollabPersonId()] = $user;

        $this->saveToDisk();

        return $user;
    }

    public function registerUser($uid, $schacHomeOrganization)
    {
        $collabPersonId = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );
        $user = new User($collabPersonId, CollabPersonUuid::generate());

        $this->users[$collabPersonId->getCollabPersonId()] = $user;

        $this->saveToDisk();

        return $user;
    }

    public function findUserBy($collabPersonId)
    {
        if (!array_key_exists($collabPersonId, $this->users)) {
            return null;
        }

        return $this->users[$collabPersonId];
    }

    public function getUserBy($collabPersonId)
    {
        $user = $this->findUserBy($collabPersonId);

        if (!$user) {
            throw new AuthenticationRuntimeException('Cannot retrieve user that has not been set in FakeUserDirectory');
        }

        return $user;
    }

    public function deleteUserWith($collabPersonId)
    {
        unset($this->users[$collabPersonId]);

        $this->saveToDisk();
    }

    /**
     * Write the user directory so it can be reused when visiting consent etc.
     */
    private function saveToDisk()
    {
        if (!$this->filesystem->exists(self::$directory)) {
            $this->filesystem->mkdir(self::$directory);
        }

        $filePath = self::$directory . self::$fileName;

        $users = $this->users;
        array_walk($users, function (&$user): void {
            $user = [
                'collab_person_id' => $user->getCollabPersonId()->getCollabPersonId(),
                'collab_person_uuid' => $user->getCollabPersonUuid()->getUuid()
            ];
        });

        $this->filesystem->dumpFile($filePath, json_encode($users));
    }
}
