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

/**
 *
 * Note that what Grouper calls REST isn't really REST but XML-RPC (tunneling XML over HTTP POST).
 */
class Grouper_Client_Rest implements Grouper_Client_Interface
{
    /**
     * Result in metadata when a member
     *
     * @var String
     */
    const IS_MEMBER = 'IS_MEMBER';

    /**
     * Result in metadata when not a member
     *
     * @var String
     */
    const IS_NOT_MEMBER = 'IS_NOT_MEMBER';

    /**
     * The admin user to retrieve membership information for private teams
     */
    const GROUPER_ADMIN = 'GrouperSystem';

    /**
     * @var string
     */
    protected $_endpointUrl;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var string
     */
    protected $_subjectId;

    public static function createFromConfig(Zend_Config $config)
    {
        if (!isset($config->host) || $config->host == '') {
            throw new EngineBlock_Exception(
                'No Grouper Host specified! Please set "grouper.host" in your application configuration.',
                EngineBlock_Exception::CODE_ALERT
            );
        }

        $url = $config->protocol .
               '://' .
               $config->user .
               ':' .
               $config->password .
               '@' .
               $config->host .
               (isset($config->port) ? ':' . $config->port : '') .
               $config->path .
               '/' .
               $config->version . '/';

        $grouper = new self($url, $config->toArray());
        return $grouper;
    }

    /**
     * @param string $endpointUrl
     */
    public function __construct($endpointUrl, array $options = array())
    {
        $this->_endpointUrl = $endpointUrl;
        $this->_options = $options;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     */
    public function getGroups($stem = null)
    {
        $this->_requireSubjectId();

        $subjectIdEncoded = htmlentities($this->_subjectId);
        $request = <<<XML
<WsRestGetGroupsRequest>
    <subjectLookups>
        <WsSubjectLookup>
            <subjectId>$subjectIdEncoded</subjectId>
        </WsSubjectLookup>
    </subjectLookups>
XML;
        if ($stem) {
            $stemEncoded = htmlentities($stem);
            $request .= <<<XML
    <wsStemLookup>
        <stemName>$stemEncoded</stemName>
    </wsStemLookup>
    <stemScope>ALL_IN_SUBTREE</stemScope>
XML;
        }

        $request .= <<<XML
    <actAsSubjectLookup>
        <subjectId>$subjectIdEncoded</subjectId>
    </actAsSubjectLookup>
</WsRestGetGroupsRequest>
XML;
        try {
            $result = $this->_doRest('subjects', $request);
        }
        catch (Exception $e) {
            if (strpos($e->getMessage(), "Problem with actAsSubject, SUBJECT_NOT_FOUND") !== false) {
                throw new Grouper_Client_Exception_SubjectNotFound($e->getMessage());
            }
            throw $e;
        }

        $groups = array();
        if (isset($result) and ($result !== FALSE) and (!empty($result->results->WsGetGroupsResult->wsGroups))) {
            foreach ($result->results->WsGetGroupsResult->wsGroups->WsGroup as $group) {
                $groups[] = $this->_mapXmlToGroupModel($group);
            }
        }
        return $groups;
    }

    public function getGroupsWithPrivileges($stem = null)
    {
        $groups = $this->getGroups($stem);
        /** @var $group Grouper_Model_Group */
        foreach ($groups as &$group) {
            $group->privileges = $this->getMemberPrivileges($this->_subjectId, $group->name);
        }
        return $groups;
    }

    public function getMembers($groupName)
    {
        $this->_requireSubjectId();

        $subjectIdEncoded = htmlentities($this->_subjectId);
        $groupNameEncoded = htmlentities($groupName);
        $request = <<<XML
<WsRestGetMembersRequest>
  <includeSubjectDetail>T</includeSubjectDetail>
  <wsGroupLookups>
    <WsGroupLookup>
      <groupName>$groupNameEncoded</groupName>
    </WsGroupLookup>
  </wsGroupLookups>
  <actAsSubjectLookup>
    <subjectId>$subjectIdEncoded</subjectId>
  </actAsSubjectLookup>
</WsRestGetMembersRequest>
XML;

        try {
            $result = $this->_doRest('groups', $request);
        }
        catch (Exception $e) {
            if (strpos($e->getMessage(), "Problem with actAsSubject, SUBJECT_NOT_FOUND") !== false) {
                throw new Grouper_Client_Exception_SubjectNotFound($e->getMessage());
            }
            throw $e;
        }

        $members = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->results->WsGetMembersResult->wsSubjects->WsSubject))) {
            foreach ($result->results->WsGetMembersResult->wsSubjects->WsSubject as $member) {
                $memberObject = $this->_mapXmlToSubjectModel($member);
                $members[$memberObject->id] = $memberObject;
            }
        }
        else {
            throw new Grouper_Client_Exception(__METHOD__ . ' Bad result: <pre>' . var_export($result, true));
        }
        return $members;
    }

    public function getMembersWithPrivileges($groupName)
    {
        $members = $this->getMembers($groupName);
        $membersWithPrivileges = array();
        foreach ($members as $member) {
            try {
                $member->privileges = $this->getMemberPrivileges($member->id, $groupName);
                $membersWithPrivileges[] = $member;
            }
            catch (Exception $e) {
                $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()
                    ->setUserId($member->id)
                    ->setDetails($e->getTraceAsString());
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Something wrong with user: " . var_export($member, true) .
                    'Received Exception: ' . var_export($e, true),
                    $additionalInfo
                );
            }
        }
        return $membersWithPrivileges;
    }

    public function getMemberPrivileges($subjectId, $groupName)
    {
        $subjectIdEncoded = htmlentities($subjectId);
        $groupNameEncoded = htmlentities($groupName);
        $superUser = self::GROUPER_ADMIN;
        $request = <<<XML
<WsRestGetGrouperPrivilegesLiteRequest>
  <includeSubjectDetail>F</includeSubjectDetail>
  <subjectId>$subjectIdEncoded</subjectId>
  <groupName>$groupNameEncoded</groupName>
  <actAsSubjectId>$superUser</actAsSubjectId>
</WsRestGetGrouperPrivilegesLiteRequest>
XML;

        $result = $this->_doRest('grouperPrivileges', $request);

        $privileges = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->privilegeResults->WsGrouperPrivilegeResult))) {
            foreach ($result->privilegeResults->WsGrouperPrivilegeResult as $privilege) {
                $privileges[] = (string)$privilege->privilegeName;
            }
        }
        else {
            throw new Grouper_Client_Exception(__METHOD__ . ' Bad result: <pre>' . var_export($result, true));
        }

        return $privileges;
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param $actAsSubjectId The user id to check
     * @param $groupName The group to check
     * @return boolean
     */
    public function hasMember($groupName)
    {
        $this->_requireSubjectId();

        $subjectIdEncoded = htmlentities($this->_subjectId);

        $requestXml = <<<XML
<WsRestHasMemberRequest>
  <subjectLookups>
    <WsSubjectLookup>
      <subjectId>$subjectIdEncoded</subjectId>
    </WsSubjectLookup>
  </subjectLookups>
  <actAsSubjectLookup>
    <subjectId>$subjectIdEncoded</subjectId>
  </actAsSubjectLookup>
</WsRestHasMemberRequest>
XML;

        $filter = urlencode($groupName);
        try {
            $result = $this->_doRest("groups/$filter/members", $requestXml);
            if ((String)$result->results->WsHasMemberResult->resultMetadata->resultCode == self::IS_MEMBER) {
                return true;
            }
        }
        catch (Exception $e) {

            // If there's an exception, either something went wrong, OR we're not a member (in which case we get an exception
            // instead of a clean error; since if we're not a member, we're not allowd to make this call.
            // This means we've got to use a crude way to distinguish between actual exceptions (grouper down/unreachable) and the situation
            // where we're not a member.
            $msg = $e->getMessage();
            if (strpos($msg, "GROUP_NOT_FOUND") !== false) {
                // Most likely we're not a member.
                return false;
            } else {
                // Most likely a system failure. Rethrow.
                throw $e;
            }
        }

        return false;
    }

    /**
     * Delete a group membership for a subject
     *
     * @abstract
     * @param $subjectId
     * @param $groupName
     * @return bool
     */
    public function deleteMembership($subjectId, $groupName)
    {
        $subjectIdEncoded = htmlentities($subjectId);
        $request = <<<XML
<WsRestDeleteMemberRequest>
  <includeSubjectDetail>F</includeSubjectDetail>
  <subjectLookups>
    <WsSubjectLookup>
      <subjectId>$subjectIdEncoded</subjectId>
    </WsSubjectLookup>
  </subjectLookups>
  <actAsSubjectLookup>
    <subjectId>$subjectIdEncoded</subjectId>
  </actAsSubjectLookup>
</WsRestDeleteMemberRequest>
XML;

        $filter = urlencode($groupName);
        try {
            $result = $this->_doRest("groups/$filter/members", $request);
            if (isset($result) and ($result !== FALSE) and ($result->results->WsDeleteMemberResult->wsSubject->resultCode == 'SUCCESS')) {
                return true;
            }
        }
        catch (Exception $e) {
            throw new Grouper_Client_Exception('Problem retrieving group members', Grouper_Client_Exception::CODE_ERROR, $e);
        }
        // Something went wrong
        return false;
    }


    public function deleteAllPrivileges($subjectId, $groupName) {
        foreach (array('optout', 'read', 'update', 'admin') as $privilege) {
            $this->deletePrivilege($subjectId, $groupName, $privilege);
        }
    }

    protected function deletePrivilege($subjectId, $groupName, $privilege)
    {
        $subjectIdEncoded = htmlentities($subjectId);
        $superUser = self::GROUPER_ADMIN;
        $request = <<<XML
<WsRestAssignGrouperPrivilegesLiteRequest>
  <allowed>F</allowed>
  <subjectId>$subjectIdEncoded</subjectId>
  <groupName>$groupName</groupName>
  <privilegeType>access</privilegeType>
  <privilegeName>$privilege</privilegeName>
  <actAsSubjectId>$superUser</actAsSubjectId>
</WsRestAssignGrouperPrivilegesLiteRequest>
XML;

        $expected = array('SUCCESS',
                          'SUCCESS_NOT_ALLOWED',
                          'SUCCESS_NOT_ALLOWED_DIDNT_EXIST',
                          'SUCCESS_NOT_ALLOWED_EXISTS_EFFECTIVE');
        $result = $this->_doRest('grouperPrivileges', $request, $expected);
        if (isset($result) and ($result !== FALSE)) {
            return true;
        }
        // Something went wrong
        return false;
    }

    /**
     * Implements REST calls to the Grouper Web Services API
     *
     * @copyright 2009 SURFnet BV
     * @version $Id$
     * @return SimpleXMLElement
     */
    protected function _doRest($operation, $request, $expect = array('SUCCESS'))
    {
        $url = $this->_endpointUrl . $operation;
        $config = array(
            'adapter' => 'Curl'
        );
        foreach ($this->_options as $key => $value) {
            $constantName = 'CURLOPT_' . strtoupper($key);
            if (defined($constantName)) {
                $config['curloptions'][constant($constantName)] = $value;
            }
        }

        $client = new Zend_Http_Client($url);
        $client->setHeaders('User-Agent', 'EngineBlock Grouper Client');
        $client->setHeaders('Content-Type', 'text/xml; charset=UTF-8');
        $client->setRawData($request);
        $response = $client->request();

        $this->_getLog()->debug('[GROUPER] Request: ' . $client->getLastRequest());
        $this->_getLog()->debug('[GROUPER] Response: ' .
            $response->getHeadersAsString() .
                PHP_EOL . PHP_EOL .
                $response->getBody()
        );

        // @todo do not use isSuccessful it is not very safe
        if (!$response->isSuccessful()) {
            $e = new Grouper_Client_Exception(
                'Could not execute grouper REST request]',
                Grouper_Client_Exception::CODE_ALERT
            );
            $e->description = implode(PHP_EOL, array(
                'URL: ' . $url,
                'Response: ' . $response->getHeadersAsString() . PHP_EOL . PHP_EOL . $response->getBody(),
            ));
            throw $e;
        }

        $result = simplexml_load_string($response->getBody());
        if ($result === FALSE) {
            throw new Grouper_Client_Exception(
                "Unable to parse response '$response' as XML",
                Grouper_Client_Exception::CODE_ALERT
            );
        }
        if (!in_array($result->resultMetadata->resultCode, $expect)) {
            throw new Grouper_Client_Exception_UnexpectedResultCode(
                "Unexpected result code: '{$result->resultMetadata->resultCode}'" .
                " expecting one of: " . implode(', ', $expect),
                Grouper_Client_Exception::CODE_ALERT
            );
        }
        return $result;
    }

    protected function _mapXmlToGroupModel(SimpleXMLElement $xml)
    {
        $group = new Grouper_Model_Group();

        $propertyNames = array_keys(get_object_vars($group));
        foreach ($propertyNames as $propertyName) {
            if (!empty($xml->$propertyName)) {
                $group->$propertyName = (string)$xml->$propertyName;
            }
        }
        return $group;
    }

    protected function _mapXmlToSubjectModel(SimpleXMLElement $xml)
    {
        $subject = new Grouper_Model_Subject();
        $propertyNames = array_keys(get_object_vars($subject));
        foreach ($propertyNames as $propertyName) {
            if (!empty($xml->$propertyName)) {
                $subject->$propertyName = (string)$xml->$propertyName;
            }
        }
        return $subject;
    }

    /**
     * @param string $subjectId
     * @return Grouper_Client_Rest
     */
    public function setSubjectId($subjectId)
    {
        $this->_subjectId = $subjectId;
        return $this;
    }

    protected function _requireSubjectId()
    {
        if (!isset($this->_subjectId)) {
            throw new Grouper_Client_Exception(
                "No subjectId set! Please use ->setSubjectId to set a subject on which behalf to make requests"
            );
        }
    }

    /**
     * @return EngineBlock_Log
     */
    protected function _getLog()
    {
        return EngineBlock_ApplicationSingleton::getLog();
    }
}
