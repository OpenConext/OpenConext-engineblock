<?php
// Migrate all users from LDAP to the database

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$ldapConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->ldap;

$ldapOptions = array(
    'host'              => $ldapConfig->host,
    'useSsl'            => $ldapConfig->useSsl,
    'username'          => $ldapConfig->userName,
    'password'          => $ldapConfig->password,
    'bindRequiresDn'    => $ldapConfig->bindRequiresDn,
    'accountDomainName' => $ldapConfig->accountDomainName,
    'baseDn'            => $ldapConfig->baseDn
);

$ldapClient = new Zend_Ldap($ldapOptions);
$ldapClient->bind();

$writer->info("Retrieving all collabPerson entries from LDAP");

$filter = '(&(objectclass=collabPerson))';

$users = $ldapClient->search($filter);
$writer->info("Retrieved " . count($users) . " users from LDAP");

if (count($users) === 0) {
    $writer->info("Stopping...");
    return;
}

$writer->info("Converting ldap user entries to database records");

$userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();
foreach ($users as $counter => $user) {
    // convert user to array
    foreach ($user as $userKey => $userValue) {
        if (is_array($userValue) && count($userValue) === 1) {
            $user[$userKey] = $userValue[0];
        }
    }

    $userDirectory->registerUser(
        new \OpenConext\EngineBlock\Authentication\Value\CollabPersonId($user['collabpersonid']),
        new \OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid($user['collabpersonuuid'])
    );
}

$writer->info("Done converting ldap user entries to database records");
