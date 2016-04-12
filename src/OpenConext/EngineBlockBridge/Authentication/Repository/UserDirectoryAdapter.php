<?php

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use EngineBlock_Exception_MissingRequiredFields;
use EngineBlock_UserDirectory as LdapUserDirectory;
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Psr\Log\LoggerInterface;

class UserDirectoryAdapter
{
    /**
     * @var \OpenConext\EngineBlock\Authentication\Repository\UserDirectory
     */
    private $userDirectory;

    /**
     * @var LdapUserDirectory
     */
    private $ldapUserDirectory;

    /**
     * @var \OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration
     */
    private $featureConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        UserDirectory $userDirectory,
        LdapUserDirectory $ldapUserDirectory,
        FeatureConfiguration $featureConfiguration,
        LoggerInterface $logger
    ) {
        $this->userDirectory        = $userDirectory;
        $this->ldapUserDirectory    = $ldapUserDirectory;
        $this->featureConfiguration = $featureConfiguration;
        $this->logger               = $logger;
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
        if (!isset($attributes[Uid::URN_MACE][0]) || !isset($attributes[SchacHomeOrganization::URN_MACE][0])) {
            throw new EngineBlock_Exception_MissingRequiredFields('Missing required SAML2 fields in attributes');
        }

        if ($this->featureConfiguration->isEnabled('eb.ldap_integration')) {
            $this->logger->debug('LDAP integration enabled, registering user in LDAP');

            $userData         = $this->ldapUserDirectory->registerUser($attributes);

            $collabPersonUuid = new CollabPersonUuid($userData[LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_UUID]);
            $collabPersonId   = new CollabPersonId($userData[LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_ID]);
        } else {
            $this->logger->debug('LDAP integration not enabled, generating value objects based on saml attributes');

            $uid                   = $attributes[Uid::URN_MACE][0];
            $schacHomeOrganization = $attributes[SchacHomeOrganization::URN_MACE][0];

            $collabPersonUuid      = CollabPersonUuid::generate();
            $collabPersonId        = CollabPersonId::generateFrom(
                new Uid($uid),
                new SchacHomeOrganization($schacHomeOrganization)
            );
        }

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
