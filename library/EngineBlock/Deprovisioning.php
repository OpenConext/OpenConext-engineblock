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

    public function deprovision($previewOnly = false)
    {
        $deprovisionConfig = $this->getDeprovisionConfig();

        $deprovisionTime = strtotime('-' . $deprovisionConfig->idleTime);
        $deprovisionUsers = $this->_findUsersForWarning($deprovisionTime);
        if (!$previewOnly) {
            $this->_deprovisionUsers($deprovisionUsers);
        }

        $firstWarningTime = strtotime($deprovisionConfig->firstWarningTime, $deprovisionTime);
        $secondWarningTime = strtotime($deprovisionConfig->secondWarningTime, $deprovisionTime);

        $secondWarningUsers = array_diff($this->_findUsersForWarning($secondWarningTime), $deprovisionUsers);
        $firstWarningUsers = array_diff($this->_findUsersForWarning($firstWarningTime), $secondWarningUsers, $deprovisionUsers);

        if (!$previewOnly && $deprovisionConfig->sendDeprovisionWarning) {
            $this->_sendWarning($firstWarningUsers, $firstWarningTime);
            $this->_sendWarning($secondWarningUsers, $secondWarningTime);
        }
        if (!$previewOnly && $deprovisionConfig->sendGroupMemberWarning) {
            $this->_sendTeamMemberWarning($firstWarningUsers, $firstWarningTime);
            $this->_sendTeamMemberWarning($secondWarningUsers, $secondWarningTime);
        }
        return array("deprovisioned-users" => $deprovisionUsers,
                     "first-warners" => $firstWarningUsers,
                     "second-warners" => $secondWarningUsers);
    }


    protected function _sendWarning(array $users, $timeOffset)
    {
        $deprovisionTime = date('d-m-Y', $timeOffset);
        $mailer = new EngineBlock_Mail_Mailer();

        foreach ($users as $userId) {
            $user = $this->_fetchUser($userId);
            $replacements = array(
                '{user}' => $user['name']['formatted'],
                '{deprovision_time}' => $deprovisionTime
            );

            $emailAddress = $user['emails'][0];
            $mailer->sendMail($emailAddress,
                              EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL,
                              $replacements);
        }

    }

    protected function _sendTeamMemberWarning(array $users, $timeOffset)
    {
        $deprovisionTime = date('d-m-Y', $timeOffset);

        $mailer = new EngineBlock_Mail_Mailer();

        $grouperClient = $this->_getGrouperClient();
        foreach ($users as $userId) {
            $grouperClient->setSubjectId($userId);
            $groups = $grouperClient->getGroups();

            foreach ($groups as $group) {
                /* @var $group Grouper_Model_Group */
                $members = $grouperClient->getMembers($group->name, true);
                $currentMember = $members[$userId];
                unset($members[$userId]);
                if ($this->_isUserOnlyAdmin($currentMember, $members)) {
                    // send the actual email to group members
                    foreach ($members as $member) {
                        /* @var $member Grouper_Model_Subject */
                        $user = $this->_fetchUser($member->id);
                        var_dump($user);
                        $replacements = array(
                            '{user}' => $member->name,
                            '{team}' => $group->displayName,
                            '{deprovision_time}' => $deprovisionTime
                        );

                        $emailAddress = $user['emails'][0];
                        $mailer->sendMail($emailAddress, EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS, $replacements);
                    }
                }
                // do nothing if user is not the admin or the only admin
            }
        }
    }

    protected function _fetchUser($userId)
    {
        $mapper = new EngineBlock_SocialData_FieldMapper();
        $userDirectory = $this->_getUserDirectory();
        $users = $userDirectory->findUsersByIdentifier($userId);
        if (count($users) === 1) {
            $user = $mapper->ldapToSocialData(array_shift($users));
            return $user;
        }
        return null;
    }

    protected function _isUserOnlyAdmin(Grouper_Model_Subject $currentMember, array $otherMembers)
    {
        if (in_array(EngineBlock_Deprovisioning::ADMIN_PRIVILEGE, $currentMember->privileges)) {
            foreach ($otherMembers as $memberId => $member) {
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
        foreach ($users as $userId) {
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

        foreach($groups as $group) {
            /* @var $group Grouper_Model_Group */
            $grouperClient->deleteMembership($userId, $group->name);
        }
    }

    protected function _findUsersForWarning($warningTime)
    {
        $factory = $this->_getDatabaseConnection();

        $query = "SELECT DISTINCT userid FROM log_logins
                    WHERE loginstamp <= ?
                    AND userid NOT IN (
                      SELECT DISTINCT userid FROM log_logins
                        WHERE loginstamp >= ?)";
        $warningTimeDate = date("Y-m-d H:i:s", $warningTime);
        //var_dump($warningTimeDate);exit;
        $parameters = array(
            $warningTimeDate,
            $warningTimeDate
        );

        $statement = $factory->prepare($query);

        $statement->execute($parameters);
        $results = $statement->fetchAll();

        $users = array();
        foreach ($results as $result) {
            $users[] = $result['userid'];
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

}
