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

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\Mockery\Matcher\ValueObjectEqualsMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserDirectoryAdapterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Mock
     */
    private $userDirectory;

    /**
     * @var Mock
     */
    private $logger;

    public function setUp(): void
    {
        $this->userDirectory        = m::mock(UserDirectory::class);
        $this->logger               = m::mock(LoggerInterface::class);

        // the amount of logging is not really relevant.
        $this->logger->shouldReceive('debug')->between(0, 1000);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     * @dataProvider noUidValueProvider
     * @param array $invalidAttributes
     */
    public function identification_of_a_user_requires_uid_value_to_be_set_as_attribute($invalidAttributes)
    {
        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $this->expectException(\EngineBlock_Exception_MissingRequiredFields::class);

        $userDirectoryAdapter->identifyUser($invalidAttributes);
    }

    public function noUidValueProvider()
    {
        return [
            'no uid'       => [
                [
                    SchacHomeOrganization::URN_MACE => ['openconext.org']
                ]
            ],
            'no uid value' => [
                [
                    Uid::URN_MACE                   => null,
                    SchacHomeOrganization::URN_MACE => ['openconext.org']
                ]
            ],
            'no uid value at index 0' => [
                [
                    Uid::URN_MACE                   => [1 => 'homer@domain.invalid'],
                    SchacHomeOrganization::URN_MACE => ['openconext.org']
                ]
            ]
        ];
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     * @dataProvider noSchacHomeOrganizationValueProvider
     * @param array $invalidAttributes
     */
    public function identification_of_a_user_requires_schacHomeOrganization_value_to_be_set_as_attribute($invalidAttributes)
    {
        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $this->expectException(\EngineBlock_Exception_MissingRequiredFields::class);

        $userDirectoryAdapter->identifyUser($invalidAttributes);
    }

    public function noSchacHomeOrganizationValueProvider()
    {
        return [
            'no schacHomeOrganization'                  => [
                [
                    Uid::URN_MACE => ['homer@domain.invalid']
                ]
            ],
            'no schacHomeOrganization value'            => [
                [
                    Uid::URN_MACE                   => ['homer@domain.invalid'],
                    SchacHomeOrganization::URN_MACE => null
                ]
            ],
            'no schacHomeOrganization value at index 0' => [
                [
                    Uid::URN_MACE                   => ['homer@domain.invalid'],
                    SchacHomeOrganization::URN_MACE => [1 => 'openconext.org']
                ]
            ]
        ];
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function registering_a_user_results_in_a_valid_user()
    {
        $uid                   = 'homer@invalid.org';
        $schacHomeOrganization = 'OpenConext.org';
        $expected              = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $this->userDirectory
            ->shouldReceive('register')
            ->with(
                m::on(function (User $user) use ($expected) {
                    return $user->getCollabPersonId()->equals($expected);
                })
            )
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $user = $userDirectoryAdapter->registerUser($uid, $schacHomeOrganization);

        $this->assertInstanceOf(
            User::class,
            $user,
            'Registering a user should return a \OpenConext\EngineBlock\Authentication\Model\User object'
        );
        $this->assertTrue(
            $user->getCollabPersonId()->equals($expected),
            'Registering a user returned a User with an unexpected CollabPersonId'
        );
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function when_attempting_to_find_a_non_existent_user_null_is_returned()
    {
        $collabPersonId = $this->getCollabPersonId();

        $this->userDirectory
            ->shouldReceive('findUserBy')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once()
            ->andReturnNull();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $result = $userDirectoryAdapter->findUserBy($collabPersonId);

        $this->assertNull($result);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function a_user_is_returned_when_attempting_to_find_an_existing_user()
    {
        $collabPersonId = $this->getCollabPersonId();
        $expected       = new User(new CollabPersonId($collabPersonId), CollabPersonUuid::generate());

        $this->userDirectory
            ->shouldReceive('findUserBy')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once()
            ->andReturn($expected);

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $result = $userDirectoryAdapter->findUserBy($collabPersonId);

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function a_request_for_removal_removes_the_user_from_the_database_backend()
    {
        $collabPersonId = $this->getCollabPersonId();

        $this->userDirectory
            ->shouldReceive('removeUserWith')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $userDirectoryAdapter->deleteUserWith($collabPersonId);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function a_request_for_removal_also_removes_the_user_from_the_user_directory_if_enabled()
    {
        $collabPersonId = $this->getCollabPersonId();

        $this->userDirectory
            ->shouldReceive('removeUserWith')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->logger
        );

        $userDirectoryAdapter->deleteUserWith($collabPersonId);
    }

    /**
     * Helper method to easily generate a valid collabPersonId without having to do this in the tests.
     * Doing this in the tests would only detract from the actual test.
     *
     * @return string
     */
    private function getCollabPersonId()
    {
        $collabPersonId = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($this->getHomerUid()),
            new SchacHomeOrganization($this->getOpenConextSho())
        );

        return $collabPersonId->getCollabPersonId();
    }

    /**
     * Helper method to provide fixed uid value
     *
     * @return string
     */
    private function getHomerUid()
    {
        return 'homer@invalid.org';
    }

    /**
     * Helper method to provide fixed SchacHomeOrganization value
     *
     * @return string
     */
    private function getOpenConextSho()
    {
        return 'openconext.org';
    }
}
