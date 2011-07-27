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
         if (!isset($config->host) || $config->host=='') {
            throw new EngineBlock_Exception('No Grouper Host specified! Please set "grouper.host" in your application configuration.');
        }

        $url = $config->protocol .
                '://' .
                $config->user .
                ':' .
                $config->password .
                '@' .
                $config->host .
                (isset($config->port)?':'.$config->port:'') .
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
    public function getGroups()
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
    <actAsSubjectLookup>
        <subjectId>$subjectIdEncoded</subjectId>
    </actAsSubjectLookup>
</WsRestGetGroupsRequest>
XML;
        try {
            $result = $this->_doRest('subjects', $request);
        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "Problem with actAsSubject, SUBJECT_NOT_FOUND")!==false) {
                throw new Grouper_Client_Exception_SubjectNotFound();
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
        catch(Exception $e) {
            if (strpos($e->getMessage(), "Problem with actAsSubject, SUBJECT_NOT_FOUND")!==false) {
                throw new Grouper_Client_Exception_SubjectNotFound();
            }
            throw $e;
        }

        $members = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->results->WsGetMembersResult->wsSubjects->WsSubject))) {
            foreach ($result->results->WsGetMembersResult->wsSubjects->WsSubject as $member) {
                $members[] = $this->_mapXmlToSubjectModel($member);
            }
        }
        else {
            throw new EngineBlock_Exception(__METHOD__ . ' Bad result: <pre>'. var_export($result, true));
        }

        return $members;
    }

    public function getGroupsByStem($stem)
    {
        $this->_requireSubjectId();

        $subjectIdEncoded = htmlentities($this->_subjectId);
        $stemEncoded = htmlentities($stem);

        $request = <<<XML
<WsRestGetGroupsRequest>
    <subjectLookups>
        <WsSubjectLookup>
            <subjectId>$subjectIdEncoded</subjectId>
        </WsSubjectLookup>
    </subjectLookups>
    <wsStemLookup>
        <stemName>$stemEncoded</stemName>
    </wsStemLookup>
    <stemScope>ALL_IN_SUBTREE</stemScope>
    <actAsSubjectLookup>
        <subjectId>$subjectIdEncoded</subjectId>
    </actAsSubjectLookup>
</WsRestGetGroupsRequest>
XML;

        try {
            $result = $this->_doRest('subjects', $request);
        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "Problem with actAsSubject, SUBJECT_NOT_FOUND")!==false) {
                throw new Grouper_Client_Exception_SubjectNotFound();
            }
            throw $e;
        }

        $groups = array();
        if (isset($result) and ($result !== FALSE) and (! empty($result->groupResults))) {
            foreach ($result->groupResults->WsGroup as $group) {
                $groups[] = $this->_mapXmlToGroupModel($group);
            }
        }
        return $groups;
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
            if ((String)$result->results->WsHasMemberResult->resultMetadata->resultCode==self::IS_MEMBER) {
                return true;
            }
        }
        catch (Exception $e) {

            // If there's an exception, either something went wrong, OR we're not a member (in which case we get an exception
            // instead of a clean error; since if we're not a member, we're not allowd to make this call.
            // This means we've got to use a crude way to distinguish between actual exceptions (grouper down/unreachable) and the situation
            // where we're not a member.
            $msg = $e->getMessage();
            if (strpos($msg, "GROUP_NOT_FOUND")!==false) {
                // Most likely we're not a member.
                return false;
            }  else {
                // Most likely a system failure. Rethrow.
                throw $e;
            }
        }

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

        $ch = curl_init($url);

        // General
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

        // Request to be sent

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=UTF-8'
        ));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        // Response handling

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        foreach ($this->_options as $key => $value) {
            $constantName = 'CURLOPT_' . strtoupper($key);
            if (defined($constantName)) {
                curl_setopt($ch, constant($constantName), $value);
            }
        }

        $responseFailed = false;
        $response = curl_exec($ch);

        $info = array('http_code'=>'');
        $error = "";
        if ($response !== FALSE) {
            $error  = curl_error($ch);
            $info   = curl_getinfo($ch);
            if (($error != '') or ($info['http_code'] >= 300)) {
                $responseFailed = true;
            }
        }

        curl_close($ch);

        if ($response === FALSE || $responseFailed === true) {
            throw new Grouper_Client_Exception(
                'Could not execute grouper webservice request:' .
                ' [url: ' . $url . ']' .
                ' [error: ' . $error . ']' .
                ' [http code: ' . $info['http_code'] . ']' .
                ' [response: ' . $response . ']'
            );
        }
var_dump(nl2br(htmlentities($response)));
        $result = simplexml_load_string($response);
        if ($result === FALSE) {
            throw new Grouper_Client_Exception("Unable to parse response '$response' as XML");
        }
        if (!in_array($result->resultMetadata->resultCode, $expect)) {
            throw new Grouper_Client_Exception_UnexpectedResultCode(
                "Unexpected result code: '{$result->resultMetadata->resultCode}'" .
                " expecting one of: " . implode(', ', $expect)
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
            throw new Grouper_Client_Exception("No subjectId set! Please use ->setSubjectId to set a subject on which behalf to make requests");
        }
    }
}