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

    const DEPROVISION_WARING_EMAIL = 'deprovisioning_warning_email';
    const DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS = 'deprovisioning_warning_email_group_members';
    const DEPROVISION_EMAIL = 'deprovisioning_email';

    protected $_userDirectory;
    protected $_grouperClient;

    public function deprovision()
    {
        $deprovisionUsers = $this->_findUsersForDeprovision();
        $secondWarningUsers = array_diff($this->_findUsersForSecondWarning(), $deprovisionUsers);
        $firstWarningUsers = array_diff($this->_findUsersForFirstWarning(), $secondWarningUsers, $deprovisionUsers);

        $this->_sendWarning($firstWarningUsers, true);
        $this->_sendWarning($secondWarningUsers);
        $this->_sendTeamMemberWarning($secondWarningUsers);
    }

    protected function _sendWarning(array $users, $firstWarning = false)
    {
        $deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
        $timeOffset = $firstWarning ? $deprovisionConfig->firstWarningTime : $deprovisionConfig->secondWarningTime;
        $deprovisionTime = date('d-m-Y', time() + $timeOffset);
        $mailer = new EngineBlock_Mail_Mailer();

        foreach ($users as $userId) {
            $user = $this->_fetchUser($userId);
            $replacements = array(
                '{user}' => $user['name']['formatted'],
                '{deprovision_time}' => $deprovisionTime
            );

            $emailAddress = $user['emails'][0];
            $mailer->sendMail($emailAddress,
                              EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS,
                              $replacements);
        }

    }

    protected function _sendTeamMemberWarning(array $users, $firstWarning = false)
    {
        $deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
        if (!$deprovisionConfig->sendGroupMemberWarning) {
            return;
        }

        $timeOffset = $firstWarning ? $deprovisionConfig->firstWarningTime : $deprovisionConfig->secondWarningTime;
        $deprovisionTime = date('d-m-Y', time() + $timeOffset);

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

                        // We have to fake the samlattributes since this is what the sendmail function uses....
                        $samlAttributes = array('urn:mace:dir:attribute-def:mail' => array($user['emails'][0]));
                        $mailer->sendMail($samlAttributes, EngineBlock_Deprovisioning::DEPROVISION_WARNING_EMAIL_GROUP_MEMBERS, $replacements);
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
            $user = new EngineBlock_User(array('nameid' => array(0 => $userId)));
            $user->delete();
        }

    }

    protected function _findUsersForFirstWarning()
    {
        $deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
        $warningTime = time() - $deprovisionConfig->idleTime + $deprovisionConfig->firstWarningTime; // deprovisioning time + four weeks

        $factory = $this->_getDatabaseConnection();

        $query = "SELECT DISTINCT userid FROM log_logins
                    WHERE loginstamp <= ?
                    AND userid NOT IN (
                      SELECT DISTINCT userid FROM log_logins
                        WHERE loginstamp >= ?)";
        $parameters = array(
            date("Y-m-d H:i:s", $warningTime),
            date("Y-m-d H:i:s", $warningTime)
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

    protected function _findUsersForSecondWarning()
    {
        $deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
        $warningTime = time() - $deprovisionConfig->idleTime + $deprovisionConfig->secondWarningTime; // deprovisioning time + two weeks

        $factory = $this->_getDatabaseConnection();

        $query = "SELECT DISTINCT userid FROM log_logins
                    WHERE loginstamp <= ?
                    AND userid NOT IN (
                      SELECT DISTINCT userid FROM log_logins
                        WHERE loginstamp >= ?)";
        $parameters = array(
            date("Y-m-d H:i:s", $warningTime),
            date("Y-m-d H:i:s", $warningTime)
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

    protected function _findUsersForDeprovision()
    {
        $deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
        $deprovisionTime = time() - $deprovisionConfig->idleTime;

        $factory = $this->_getDatabaseConnection();

        $query = "SELECT DISTINCT userid FROM log_logins
                    WHERE loginstamp <= ?
                    AND userid NOT IN (
                      SELECT DISTINCT userid FROM log_logins
                        WHERE loginstamp >= ?)";
        $parameters = array(
            date("Y-m-d H:i:s", $deprovisionTime),
            date("Y-m-d H:i:s", $deprovisionTime)
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

    /**
     * @return PDO
     */
    protected function _getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
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
