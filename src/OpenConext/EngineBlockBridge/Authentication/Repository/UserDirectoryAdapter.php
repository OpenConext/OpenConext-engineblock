<?php

namespace OpenConext\EngineBlockBridge\Authentication\Repository;

use EngineBlock_Exception;
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
    public function identifyUser(array $attributes, $subjectIdField)
    {
        switch ($subjectIdField) {
            case "uid+sho":
                $this->logger->notice("case uid+sho");

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

                $uid          = $attributes[Uid::URN_MACE][0];
                $organization = $attributes[SchacHomeOrganization::URN_MACE][0];

                break;
            case "eppn":
                $this->logger->notice("case eppn");

               if ($this->featureConfiguration->isEnabled('eb.ldap_integration')) {
                     throw new EngineBlock_Exception(
                       'eppn subjectIdField in conjunction with LDAP integration is not supported'
                     );
                }

                if (!isset($attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'])) {
                     throw new EngineBlock_Exception_MissingRequiredFields(
                       'Missing required SAML2 field urn:mace:dir:attribute-def:eduPersonPrincipalName in attributes'
                     );
                }

               list($uid, $organization) = explode('@', $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0]);

                break;
            default:
                throw new EngineBlock_Exception(
                    "SubjectIdField '$subjectIdField' does not contain valid value"
                );

        }

        if ($this->featureConfiguration->isEnabled('eb.ldap_integration')) {
            $this->logger->debug('LDAP integration enabled, registering user in LDAP');

            $userData         = $this->ldapUserDirectory->registerUser($attributes);

            $collabPersonUuid = new CollabPersonUuid($userData[LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_UUID]);
            $collabPersonId   = new CollabPersonId($userData[LdapUserDirectory::LDAP_ATTR_COLLAB_PERSON_ID]);
        } else {
            $this->logger->debug('LDAP integration not enabled, generating value objects based on saml attributes');

            $collabPersonUuid      = CollabPersonUuid::generate();
            $collabPersonId        = CollabPersonId::generateWithReplacedAtSignFrom(
                new Uid($uid),
                new SchacHomeOrganization($organization)
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
        if ($this->featureConfiguration->isEnabled('eb.ldap_integration')) {
            $this->ldapUserDirectory->deleteUser($collabPersonId);
        }

        $this->userDirectory->removeUserWith(new CollabPersonId($collabPersonId));
    }
}
