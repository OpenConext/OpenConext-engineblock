<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Deprovisioning
{
    const ADMIN_PRIVILEGE = 'admin';
    const MANAGER_PRIVILEGE = 'update';

    const DEPROVISION_WARNING_EMAIL = 'deprovisioning_warning_email';
    const DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS = 'deprovisioning_warning_email_group_members';

    /**
     * @todo this is a PDO not a factory
     * @var EngineBlock_Database_ConnectionFactory
     */
    protected $_factory;

    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory;

    /**
     * @var Grouper_Client_Rest
     */
    protected $_grouperClient;

    /**
     * @var EngineBlock_Mail_Mailer
     */
    protected $_mailer;

    public function deprovision($previewOnly = false)
    {
        $deprovisionConfig = $this->getDeprovisionConfig();

        $deprovisionTime = strtotime('-' . $deprovisionConfig->idleTime);
        $deprovisionUsers = $this->_findUsers($deprovisionTime);
        if (!$previewOnly) {
            $this->_deprovisionUsers($deprovisionUsers);
        }

        // Warning times
        $firstWarningTime = strtotime($deprovisionConfig->firstWarningTime, $deprovisionTime);
        $firstWarningTimeOffset = strtotime($deprovisionConfig->firstWarningTime);
        $secondWarningTime = strtotime($deprovisionConfig->secondWarningTime, $deprovisionTime);
        $secondWarningTimeOffset = strtotime($deprovisionConfig->secondWarningTime);

        $secondWarningUsers = $this->_getUsersForSecondWarning($deprovisionUsers, $secondWarningTime);
        $firstWarningUsers = $this->_getUsersForFirstWarning($secondWarningUsers, $firstWarningTime);

        if (!$previewOnly && $deprovisionConfig->sendDeprovisionWarning) {
            $this->_sendFirstWarning($firstWarningUsers, $firstWarningTimeOffset);
            $this->_sendSecondWarning($secondWarningUsers, $secondWarningTimeOffset);
        }
        if (!$previewOnly && $deprovisionConfig->sendGroupMemberWarning) {
            $this->_sendTeamMemberWarning($firstWarningUsers, $firstWarningTimeOffset);
            $this->_sendTeamMemberWarning($secondWarningUsers, $secondWarningTimeOffset);
        }
        return array("deprovisioned-users" => $deprovisionUsers,
                     "first-warners" => $firstWarningUsers,
                     "second-warners" => $secondWarningUsers);
    }

    protected function _sendFirstWarning(array $users, $timeOffset)
    {
        foreach ($users as $user) {
            $this->_sendWarning($user, $timeOffset);
            $userDirectory = $this->_getUserDirectory();
            $userDirectory->setUserFirstWarningSent($user['id']);
        }
    }

    protected function _sendSecondWarning(array $users, $timeOffset)
    {
        foreach ($users as $user) {
            $this->_sendWarning($user, $timeOffset);
            $userDirectory = $this->_getUserDirectory();
            $userDirectory->setUserSecondWarningSent($user['id']);
        }
    }

    protected function _sendWarning(array $user, $timeOffset)
    {
        $userId = $user['id'];
        $deprovisionTime = date('d-m-Y', $timeOffset);
        $mailer = $this->_getMailer();

        $groups = $this->_getGroups($userId);
        $onlyAdminGroups = $this->_getGroupDisplayNameArray($this->_getOnlyAdminGroups($groups, $userId));
        $groupDisplayNames = $this->_getGroupDisplayNameArray($groups);
        $replacements = array(
            '{user}' => $user['name']['formatted'],
            '{deprovision_time}' => $deprovisionTime,
            '{groups}' => $groupDisplayNames,
            '{onlyAdminGroups}' => $onlyAdminGroups
        );

        $emailAddress = $user['emails'][0];
        $mailer->sendMail($emailAddress,
                          EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL,
                          $replacements);
    }

    protected function _sendTeamMemberWarning(array $users, $timeOffset)
    {
        $deprovisionTime = date('d-m-Y', $timeOffset);

        $mailer = new EngineBlock_Mail_Mailer();

        $grouperClient = $this->_getGrouperClient();
        foreach ($users as $userId => $user) {
            $grouperClient->setSubjectId($userId);
            $groups = $grouperClient->getGroups();

            foreach ($groups as $group) {
                /* @var $group Grouper_Model_Group */
                $members = $grouperClient->getMembersWithPrivileges($group->name);
                if ($this->_isUserOnlyAdmin($members, $userId)) {
                    // send the actual email to group members
                    foreach ($members as $member) {
                        // Do not send the mail to the user that is to be deprovisioned
                        if ($member->id != $userId) {
                            /* @var $member Grouper_Model_Subject */
                            $user = $this->_getLdapUser($member->id);
                            $replacements = array(
                                '{user}' => $member->name,
                                '{team}' => $group->displayName,
                                '{deprovision_time}' => $deprovisionTime
                            );

                            $emailAddress = $user['emails'][0];
                            $mailer->sendMail($emailAddress, EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS, $replacements);
                        }
                    }
                }
                // do nothing if user is not the admin or the only admin
            }
        }
    }

    protected function _getLdapUser($userId)
    {
        $mapper = new EngineBlock_SocialData_FieldMapper();
        $userDirectory = $this->_getUserDirectory();
        $users = $userDirectory->findUsersByIdentifier($userId);
        if (count($users) === 1) {
            $firstWarningSent = $users[0]['collabpersonfirstwarningsent'][0] === 'TRUE' ? true : false;
            $secondWarningSent = $users[0]['collabpersonsecondwarningsent'][0] === 'TRUE' ? true : false;
            $user = $mapper->ldapToSocialData(array_shift($users));

            // add first and second warning fields
            $user['firstWarningSent'] = $firstWarningSent;
            $user['secondWarningSent'] = $secondWarningSent;

            return $user;
        }
        return null;
    }

    protected function _getGroups($userId)
    {
        $grouperClient = $this->_getGrouperClient();
        $grouperClient->setSubjectId($userId);
        return $grouperClient->getGroups();

    }

    protected function _getOnlyAdminGroups(array $groups, $userId)
    {
        $grouperClient = $this->_getGrouperClient();
        $onlyAdminGroups = array();
        foreach ($groups as $group) {
            /* @var $group Grouper_Model_Group */
            $members = $grouperClient->getMembersWithPrivileges($group->name);
            if ($this->_isUserOnlyAdmin($members, $userId)) {
                $onlyAdminGroups[$group->name] = $group;
            }
        }
        return $onlyAdminGroups;
    }

    protected function _getGroupDisplayNameArray(array $groups)
    {
        $groupNames = array();
        foreach ($groups as $group) {
            /* @var $group Grouper_Model_Group */
            $groupNames[] = $group->displayExtension;
        }
        return $groupNames;
    }

    protected function _isUserOnlyAdmin(array $members, $userId)
    {
        /* @var $currentMember Grouper_Model_Subject */
        $currentMember = $members[$userId];
        unset($members[$userId]);

        if (in_array(EngineBlock_Deprovisioning::ADMIN_PRIVILEGE, $currentMember->privileges)) {
            foreach ($members as $memberId => $member) {
                if (in_array(EngineBlock_Deprovisioning::ADMIN_PRIVILEGE, $member->privileges)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function _deprovisionUsers(array $users)
    {
        foreach ($users as $userId => $userInstance) {
            // Delete users' memberships
            $this->_removeUserFromGroups($userId);

            // Delete user (including consent, oauth, etc
            $user = new EngineBlock_User(array('nameid' => array(0 => $userId)));
            $user->delete();
        }

    }

    protected function _removeUserFromGroups($userId)
    {
        $grouperClient = $this->_getGrouperClient();
        $grouperClient->setSubjectId($userId);
        $groups = $grouperClient->getGroups();

        foreach ($groups as $group) {
            /* @var $group Grouper_Model_Group */
            $grouperClient->deleteMembership($userId, $group->name);
            $grouperClient->deleteAllPrivileges($userId, $group->name);
        }
    }

    protected function _getUsersForFirstWarning(array $intersectUsers, $firstWarningTime)
    {
        return $this->_getUsersForWarning($intersectUsers, 'firstWarningSent', $firstWarningTime);
    }

    protected function _getUsersForSecondWarning(array $intersectUsers, $secondWarningTime)
    {
        return $this->_getUsersForWarning($intersectUsers, 'secondWarningSent', $secondWarningTime);
    }

    protected function _getUsersForWarning(array $intersectUsers, $warningSentAttribute, $time)
    {
        $users = array_diff($this->_findUsers($time), $intersectUsers);

        $result = array();
        foreach ($users as $userId => $user) {
            // Filter out any users that have been warned already
            if (!$user[$warningSentAttribute]) {
                $result[$userId] = $user;
            }
        }
        return $result;
    }

    protected function _findUsers($time)
    {
        // @todo this is not a factory by a PDO
        $factory = $this->_getDatabaseConnection();

        $query = "SELECT DISTINCT userid FROM log_logins
                    WHERE loginstamp <= ?
                    AND userid NOT IN (
                      SELECT DISTINCT userid FROM log_logins
                        WHERE loginstamp >= ?)";
        $dateTime = date('Y-m-d H:i:s', $time);
        $parameters = array(
            $dateTime,
            $dateTime
        );

        $statement = $factory->prepare($query);

        $statement->execute($parameters);
        $results = $statement->fetchAll();

        $users = array();
        foreach ($results as $result) {
            $user = $this->_getLdapUser($result['userid']);
            if ($this->_getLdapUser($result['userid'])) {
                $users[$user['id']] = $user;
            }
        }
        return $users;
    }

    protected function getDeprovisionConfig()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
    }

    /**
     * @return PDO
     */
    protected function _getDatabaseConnection()
    {
        if (!isset($this->_factory)) {
            $factory = new EngineBlock_Database_ConnectionFactory();
            $this->_factory = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
        }
        return $this->_factory;
    }

    protected function _getGrouperClient()
    {
        if (!isset($this->_grouperClient)) {
            $applicationConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
            $configReader = new EngineBlock_Group_Provider_ProviderConfig();
            $config = $configReader->createFromDatabaseFor($applicationConfig->defaultGroupProvider);
            $this->_grouperClient = Grouper_Client_Rest::createFromConfig($config->current());
        }
        return $this->_grouperClient;
    }

    /**
     * @return EngineBlock_UserDirectory
     */
    protected function _getUserDirectory()
    {
        if (!isset ($this->_userDirectory)) {
            $ldapConfig = EngineBlock_ApplicationSingleton::getInstance()
                    ->getConfiguration()
            ->ldap;
            $this->_userDirectory = new EngineBlock_UserDirectory($ldapConfig);
        }
        return $this->_userDirectory;
    }

    /*
     * @return EngineBlock_Mail_Mailer
     */
    protected function _getMailer()
    {
        if (!isset($this->_mailer)) {
            $this->_mailer = new EngineBlock_Mail_Mailer();
        }
        return $this->_mailer;
    }

}
