<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use EngineBlock_UserDirectory as UserDirectory;
use OpenConext\EngineBlock\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class FakeUserDirectory extends UserDirectory
{
    /**
     * @var array
     */
    private $users = array();

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

        $this->users = json_decode($content, true);
    }

    public function findUsersByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, $this->users)) {
            return array();
        }

        return array($this->users[$identifier]);
    }

    public function registerUser(array $saml2attributes, $retry = true)
    {
        $ldapAttributes = $this->_getSaml2AttributesFieldMapper()->saml2AttributesToLdapAttributes($saml2attributes);
        $ldapAttributes = $this->_enrichLdapAttributes($ldapAttributes, $saml2attributes);

        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH] = $this->_getCollabPersonHash($ldapAttributes);
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_ID]   = $this->_getCollabPersonId($ldapAttributes);
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_UUID] = $this->_getCollabPersonUuid($ldapAttributes);

        $now = date(DATE_RFC822);

        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_REGISTERED]    = $now;
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;
        $ldapAttributes['objectClass']                               = $this->LDAP_OBJECT_CLASSES;

        $collabPersonId = $this->_getCollabPersonId($ldapAttributes);

        $this->users[$collabPersonId] = $ldapAttributes;

        $this->saveToDisk();

        return $this->users[$collabPersonId];
    }

    public function deleteUser($collabPersonId)
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

        $this->filesystem->dumpFile($filePath, json_encode($this->users), 0664);
    }
}
