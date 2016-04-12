<?php
// Assign a UUID to all users in LDAP

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
    'host'                 => $ldapConfig->host,
    'useSsl'               => $ldapConfig->useSsl,
    'username'             => $ldapConfig->userName,
    'password'             => $ldapConfig->password,
    'bindRequiresDn'       => $ldapConfig->bindRequiresDn,
    'accountDomainName'    => $ldapConfig->accountDomainName,
    'baseDn'               => $ldapConfig->baseDn
);

$ldapClient = new Zend_Ldap($ldapOptions);
$ldapClient->bind();

$writer->info("Retrieving all collabPerson entries from LDAP");

//$filter = '(&(objectclass=collabPerson))';
$filter = '(&(objectclass=collabPerson)(!(collabPersonUUID=*)))';

$users = $ldapClient->search($filter);
while (count($users) > 0) {
    $writer->info("Retrieved " . count($users) . " users from LDAP");
    foreach ($users as $user) {
        foreach ($user as $userKey => $userValue) {
            if (is_array($userValue) && count($userValue) === 1) {
                $user[$userKey] = $userValue[0];
            }
        }

        $user['collabpersonuuid'] = (string) \Ramsey\Uuid\Uuid::uuid4();

        $now = date(DATE_RFC822);
        $user['collabpersonlastupdated'] = $now;

        $dn = 'uid=' . $user['uid'] . ',o=' . $user['o'] . ',' . $ldapClient->getBaseDn();
        $ldapClient->update($dn, $user);

        $writer->info("Set UUID '{$user['collabpersonuuid']}' for DN: '$dn'");
    }
    $users = $ldapClient->search($filter);
}

