<?php

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use EngineBlock_UserDirectory as LdapUserDirectory;
use Mockery as m;
use Mockery\Mock;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\Mockery\Matcher\ValueObjectEqualsMatcher;
use PHPUnit_Framework_TestCase as UnitTest;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UserDirectoryAdapterTest extends UnitTest
{
    /**
     * @var Mock
     */
    private $ldapUserDirectory;

    /**
     * @var Mock
     */
    private $userDirectory;

    /**
     * @var Mock
     */
    private $featureConfiguration;

    /**
     * @var Mock
     */
    private $logger;

    public function setUp()
    {
        $this->ldapUserDirectory    = m::mock(LdapUserDirectory::class);
        $this->userDirectory        = m::mock(UserDirectory::class);
        $this->featureConfiguration = m::mock(FeatureConfiguration::class);
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
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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
    public function if_ldap_is_disabled_no_calls_are_made_to_the_ldap_user_directory()
    {
        $attibutes = [
            Uid::URN_MACE                   => [$this->getHomerUid()],
            SchacHomeOrganization::URN_MACE => [$this->getOpenConextSho()]
        ];
        $user = new User(new CollabPersonId($this->getCollabPersonId()), CollabPersonUuid::generate());

        $this->ldapUserDirectory->shouldNotReceive('registerUser');

        $this->featureConfiguration
            ->shouldReceive('isEnabled')
            ->once()
            ->andReturn(false);
        $this->userDirectory
            ->shouldReceive('findUserBy')
            ->once()
            ->with(new ValueObjectEqualsMatcher(new CollabPersonId($this->getCollabPersonId())))
            ->andReturn($user);

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->ldapUserDirectory,
            $this->featureConfiguration,
            $this->logger
        );

        $identified = $userDirectoryAdapter->identifyUser($attibutes);
        $this->assertSame($user, $identified);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function if_ldap_is_enabled_the_user_is_registered_in_the_ldap_directory()
    {
        $attibutes = [
            Uid::URN_MACE                   => [$this->getHomerUid()],
            SchacHomeOrganization::URN_MACE => [$this->getOpenConextSho()]
        ];
        $user      = new User(new CollabPersonId($this->getCollabPersonId()), CollabPersonUuid::generate());

        $this->ldapUserDirectory
            ->shouldReceive('registerUser')
            ->with($attibutes)
            ->once()
            ->andReturn(
                [
                    LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_UUID => (string) Uuid::uuid4(),
                    LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_ID => $this->getCollabPersonId()
                ]
            );

        $this->featureConfiguration
            ->shouldReceive('isEnabled')
            ->once()
            ->andReturn(true);
        $this->userDirectory
            ->shouldReceive('findUserBy')
            ->once()
            ->with(new ValueObjectEqualsMatcher(new CollabPersonId($this->getCollabPersonId())))
            ->andReturn($user);

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->ldapUserDirectory,
            $this->featureConfiguration,
            $this->logger
        );

        $identified = $userDirectoryAdapter->identifyUser($attibutes);
        $this->assertSame($user, $identified);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function if_the_user_is_not_found_in_the_user_directory_it_is_stored_in_the_user_directory()
    {
        $attibutes = [
            Uid::URN_MACE                   => [$this->getHomerUid()],
            SchacHomeOrganization::URN_MACE => [$this->getOpenConextSho()]
        ];
        $ldapAttributes = [
            LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_UUID => (string) Uuid::uuid4(),
            LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_ID   => $this->getCollabPersonId()
        ];
        $user = new User(
            new CollabPersonId($this->getCollabPersonId()),
            new CollabPersonUuid($ldapAttributes[LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_UUID])
        );

        $this->featureConfiguration
            ->shouldReceive('isEnabled')
            ->once()
            ->andReturn(true);
        $this->ldapUserDirectory
            ->shouldReceive('registerUser')
            ->with($attibutes)
            ->once()
            ->andReturn($ldapAttributes);
        $this->userDirectory
            ->shouldReceive('findUserBy')
            ->once()
            ->andReturnNull();
        $this->userDirectory
            ->shouldReceive('register')
            ->once()
            ->with(m::on(
                function ($argument) use ($user) {
                    return $user == $argument;
                }
            ))
            ->andReturn($user);

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->ldapUserDirectory,
            $this->featureConfiguration,
            $this->logger
        );

        $identified = $userDirectoryAdapter->identifyUser($attibutes);
        $this->assertEquals($user, $identified);
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
        $expected              = CollabPersonId::generateFrom(
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
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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

        $this->featureConfiguration
            ->shouldReceive('isEnabled')
            ->once()
            ->andReturn(false);
        $this->userDirectory
            ->shouldReceive('removeUserWith')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->ldapUserDirectory,
            $this->featureConfiguration,
            $this->logger
        );

        $userDirectoryAdapter->deleteUserWith($collabPersonId);
    }

    /**
     * @test
     * @group EngineBlockBridge
     * @group Authentication
     */
    public function a_request_for_removal_also_removes_the_user_from_the_ldap_backend_if_enabled()
    {
        $collabPersonId = $this->getCollabPersonId();

        $this->featureConfiguration
            ->shouldReceive('isEnabled')
            ->once()
            ->andReturn(true);
        $this->ldapUserDirectory
            ->shouldReceive('deleteUser')
            ->with($collabPersonId)
            ->once();
        $this->userDirectory
            ->shouldReceive('removeUserWith')
            ->withArgs([new ValueObjectEqualsMatcher(new CollabPersonId($collabPersonId))])
            ->once();

        $userDirectoryAdapter = new UserDirectoryAdapter(
            $this->userDirectory,
            $this->ldapUserDirectory,
            $this->featureConfiguration,
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
        $collabPersonId = CollabPersonId::generateFrom(
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
