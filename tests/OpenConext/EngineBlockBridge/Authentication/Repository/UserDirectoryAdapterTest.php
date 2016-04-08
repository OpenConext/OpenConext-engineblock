<?php

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use Mockery as m;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\Mockery\Matcher\ValueObjectEqualsMatcher;
use PHPUnit_Framework_TestCase as UnitTest;

class UserDirectoryAdapterTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function registering_a_user_results_in_a_valid_user()
    {
        $uid                   = 'homer@invalid.org';
        $schacHomeOrganization = 'OpenConext.org';
        $expected              = CollabPersonId::generateFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $userDirectory = m::mock(UserDirectory::class);
        $userDirectory
            ->shouldReceive('register')
            ->with(
                m::on(function (User $user) use ($expected) {
                    return $user->getCollabPersonId()->equals($expected);
                })
            )
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter($userDirectory);

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
        $userDirectory  = m::mock(UserDirectory::class);
        $collabPersonId = $this->getCollabPersonId();

        $userDirectory
            ->shouldReceive('findUserBy')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once()
            ->andReturnNull();

        $userDirectoryAdapter = new UserDirectoryAdapter($userDirectory);

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
        $userDirectory  = m::mock(UserDirectory::class);
        $collabPersonId = $this->getCollabPersonId();
        $expected       = new User(new CollabPersonId($collabPersonId), CollabPersonUuid::generate());

        $userDirectory
            ->shouldReceive('findUserBy')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once()
            ->andReturn($expected);

        $userDirectoryAdapter = new UserDirectoryAdapter($userDirectory);

        $result = $userDirectoryAdapter->findUserBy($collabPersonId);

        $this->assertSame($expected, $result);
    }

    public function when_attempting_to_get_a_user_that_does_not_exist_an_exception_is_thrown()
    {

    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function a_request_for_removal_is_handled()
    {
        $userDirectory  = m::mock(UserDirectory::class);
        $collabPersonId = $this->getCollabPersonId();

        $userDirectory
            ->shouldReceive('removeUserWith')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter($userDirectory);

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
        $uid = 'homer@invalid.org';
        $schacHomeOrganization = 'OpenConext.org';
        $collabPersonId = CollabPersonId::generateFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        return $collabPersonId->getCollabPersonId();
    }
}
