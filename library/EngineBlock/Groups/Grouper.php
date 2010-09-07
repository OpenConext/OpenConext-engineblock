<?php

class EngineBlock_Groups_Grouper
{
    /**
     * Retrieve the list of groups that the specified subject is a member of.
     */
    public function getGroups($userIdentifier)
    {
        $request = <<<XML
<WsRestGetGroupsRequest>
    <subjectLookups>
        <WsSubjectLookup>
            <subjectId>$userIdentifier</subjectId>
        </WsSubjectLookup>
    </subjectLookups>
    <actAsSubjectLookup>
        <subjectId>$userIdentifier</subjectId>
    </actAsSubjectLookup>
</WsRestGetGroupsRequest>
XML;
        $result = $this->_doRest('subjects', $request);
        #   print_r($result);
        $groups = array();
        if (isset($result) and ($result !== FALSE) and (! empty($result->results->WsGetGroupsResult->wsGroups))) {
            foreach ($result->results->WsGetGroupsResult->wsGroups->WsGroup as $group) {
                $groups[] = $this->_groupXmlToArray($group);
            }
        }
        return $groups;
    }

    function getMembers($userId, $groupName)
    {
        $userIdEncoded   = htmlentities($userId);
        $groupXmlEncoded = htmlentities($groupName);
        $request = <<<XML
<WsRestGetMembersRequest>
  <includeSubjectDetail>T</includeSubjectDetail>
  <wsGroupLookups>
    <WsGroupLookup>
      <groupName>$groupXmlEncoded</groupName>
    </WsGroupLookup>
  </wsGroupLookups>
  <actAsSubjectLookup>
    <subjectId>$userIdEncoded</subjectId>
  </actAsSubjectLookup>
</WsRestGetMembersRequest>
XML;
        
        $result = $this->_doRest('groups', $request);

        $members = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->results->WsGetMembersResult->wsSubjects->WsSubject))) {
            foreach ($result->results->WsGetMembersResult->wsSubjects->WsSubject as $member) {
                $members[] = $this->_memberXmlToArray($member);
            }
        }
        else {
            throw new EngineBlock_Exception(__METHOD__ . ' Bad result: <pre>'. var_export($result, true));
        }
        
        return $members;
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
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $grouperConfig = $application->getConfiguration()->grouper;
        
        if (!isset($grouperConfig->host) || $grouperConfig->host=='') {
            throw new EngineBlock_Exception('No Grouper Host specified! Please set "grouper.Host" in your application configuration.');     
        }
       
        $url = $grouperConfig->protocol .
                '://' .
                $grouperConfig->user .
                ':' .
                $grouperConfig->password .
                '@' .
                $grouperConfig->host .
                (isset($grouperConfig->port)?':'.$grouperConfig->port:'') .
                $grouperConfig->path .
                '/' .
                $grouperConfig->version . '/' .
                $operation;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=UTF-8'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

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
            throw new Exception('Could not execute grouper webservice request:' .
                                ' [url: ' . $url . ']' .
                                ' [error: ' . $error . ']' .
                                ' [http code: ' . $info['http_code'] . ']' .
                                ' [response: ' . $response . ']');
        }
        
        $result = @simplexml_load_string($response);
        if ($result === FALSE) {
            print_r($response);
            exit();
        }
        if (! in_array($result->resultMetadata->resultCode, $expect)) {
            print_r($response);
            throw new Exception($result->resultMetadata->resultCode);
        }
        return $result;
    }

    protected function _groupXmlToArray(SimpleXMLElement $group)
    {
        $result = array();
        if (! empty($group->name)) {
            $result['name'] = (string) $group->name;
        }
        $result['description'] = (! empty($group->description)) ? (string) $group->description : "";
        if (! empty($group->extension)) {
            $result['extension'] = (string) $group->extension;
        }
        if (! empty($group->displayExtension)) {
            $result['displayExtension'] = (string) $group->displayExtension;
        }
        if (! empty($group->uuid)) {
            $result['uuid'] = (string) $group->uuid;
        }
        return $result;
    }

    protected function _memberXmlToArray(SimpleXMLElement $member)
    {
        $result = array();
        if (! empty($member->id)) {
            $result['id'] = (string) $member->id;
        }
        if (! empty($member->name)) {
            $result['name'] = (string) $member->name;
        }
        return $result;
    }

    /*
     * ALL METHODS BELOW HERE ARE FROM SAMPLE PROVIDED BY HANS. UNTESTED AND SHOULD BE CONVERTED TO
     * METHODS AS NEEDED (e.g. grouper_get_group should be named ->getGroup)
     * 
     * 
function grouper_get_group($id, $act_as = NULL) {
    $request = '<WsRestGroupSaveLiteRequest>';
    $request .= '<groupName>' . $id . '</groupName>';
    if (isset($act_as)) {
        $request .= '<actAsSubjectId>' . $act_as . '</actAsSubjectId>';
    }
    $request .= '</WsRestGroupSaveLiteRequest>';

    $result = $this->_doRest('groups/' . urlencode($id) , $request, array('SUCCESS_UPDATED', 'SUCCESS_NO_CHANGES_NEEDED'));
    
    $group = NULL;
    if (isset($result) and ($result !== FALSE) and (!empty($result->wsGroup))) {
        $group = array(
            'description' => (string)$result->wsGroup->description,
            'name' => (string)$result->wsGroup->name,
        );
    }
    return $group;
}
*/
    /**
     * Add a member to a number of groups on behalf of the specified origin
     */
 /*   function grouper_add_member_to_groups($invitee, $origin, $groups)
    {
        $request = '<WsRestAddMemberRequest>
  <subjectLookups>
    <WsSubjectLookup>
      <subjectId>' . $invitee . '</subjectId>
    </WsSubjectLookup>
  </subjectLookups>
  <replaceAllExisting>F</replaceAllExisting>
  <actAsSubjectLookup>
    <subjectId>' . $origin . '</subjectId>
  </actAsSubjectLookup>
</WsRestAddMemberRequest>';
        foreach ($groups as $group) {
            $this->_doRest('groups/' . $group . '/members', $request);
        }
    }
*/
    /**
     * Delete a member from a number of groups on behalf of the specified origin
     */
  /*  function grouper_delete_member_from_groups($subject, $origin, $groups)
    {
        $request = '<WsRestDeleteMemberRequest>
  <subjectLookups>
    <WsSubjectLookup>
      <subjectId>' . $subject . '</subjectId>
    </WsSubjectLookup>
  </subjectLookups>
  <actAsSubjectLookup>
    <subjectId>' . $origin . '</subjectId>
  </actAsSubjectLookup>
</WsRestDeleteMemberRequest>';
        foreach ($groups as $group) {
            $this->_doRest('groups/' . $group . '/members', $request);
        }
    }

    function grouper_create_groups($groups, $origin = NULL)
    {
        #   print_r($groups);
        $request = '<WsRestGroupSaveRequest><wsGroupToSaves>';
        foreach ($groups as $group) {
            $request .= '<WsGroupToSave><wsGroupLookup><groupName>';
            $request .= htmlentities($group['name']);
            $request .= '</groupName></wsGroupLookup><wsGroup><extension>';
            $request .= array_key_exists('extension', $group) ? htmlentities($group['extension']) : htmlentities($group['name']);
            $request .= '</extension><displayExtension>';
            $request .= array_key_exists('displayExtension', $group) ? htmlentities($group['displayExtension']) : htmlentities($group['name']);
            $request .= '</displayExtension><description>';
            $request .= array_key_exists('description', $group) ? htmlentities($group['description']) : htmlentities($group['name']);
            $request .= '</description><name>';
            $request .= htmlentities($group['name']);
            $request .= '</name></wsGroup></WsGroupToSave>';
        }
        $request .= '</wsGroupToSaves>';
        if (isset($origin)) {
            $request .= '<actAsSubjectLookup><subjectId>' . $origin . '</subjectId></actAsSubjectLookup>';
        }
        $request .= '</WsRestGroupSaveRequest>';
        return $this->_doRest('groups', $request);
    }

    function grouper_create_stems($stems)
    {
        $request = '<WsRestStemSaveRequest><wsStemToSaves>';
        foreach ($stems as $stem) {
            $request .= '<WsStemToSave><wsStemLookup><stemName>';
            $request .= $stem;
            $request .= '</stemName></wsStemLookup><wsStem><extension>';
            $request .= $stem;
            $request .= '</extension><displayExtension>';
            $request .= (strrchr($stem, ':') ? substr(strrchr($stem, ':'), 1) : $stem);
            $request .= '</displayExtension><description>';
            $request .= $stem;
            $request .= '</description><name>';
            $request .= $stem;
            $request .= '</name></wsStem></WsStemToSave>';
        }
        $request .= '</wsStemToSaves></WsRestStemSaveRequest>';
        return $this->_doRest('stems', $request);
    }

    function grouper_delete_stems($stems)
    {
        $request = '<WsRestStemDeleteRequest><wsStemLookups>';
        foreach ($stems as $stem) {
            $request .= '<WsStemLookup><stemName>';
            $request .= $stem;
            $request .= '</stemName></WsStemLookup>';
        }
        $request .= '</wsStemLookups></WsRestStemDeleteRequest>';
        return $this->_doRest('stems', $request);
    }

    function grouper_delete_groups($stem, $groups)
    {
        $request = '<WsRestGroupDeleteRequest><wsGroupLookups>';
        foreach ($groups as $group) {
            $request .= '<WsGroupLookup><groupName>';
            $request .= $stem . ':' . $group;
            $request .= '</groupName></WsGroupLookup>';
        }
        $request .= '</wsGroupLookups></WsRestGroupDeleteRequest>';
        return $this->_doRest('groups', $request);
    }

    function grouper_find_groups_impl($request)
    {
        $result = $this->_doRest('groups', $request);
        #print_r($result);
        $groups = array();
        if (isset($result) and ($result !== FALSE) and (! empty($result->groupResults))) {
            foreach ($result->groupResults->WsGroup as $group) {
                $groups[] = $this->_x2pGroup($group);
            }
        }
        return $groups;
    }

    function grouper_find_groups($match = NULL, $stem = NULL)
    {
        $request = '<WsRestFindGroupsRequest>';
        if (($match !== NULL) or ($stem !== NULL)) {
            $request .= '<wsQueryFilter><queryFilterType>';
            if ($match !== NULL)
                $request .= 'FIND_BY_GROUP_NAME_APPROXIMATE';
            if (($match !== NULL) and ($stem !== NULL))
                $request .= ' AND ';
            if ($stem !== NULL)
                $request .= 'FIND_BY_STEM_NAME';
            $request .= '</queryFilterType>';
            if ($match !== NULL)
                $request .= '<groupName>' . $match . '</groupName>';
            if ($stem !== NULL)
                $request .= '<stemName>' . $stem . '</stemName>';
            $request .= '</wsQueryFilter>';
        }
        #   $request .= '<actAsSubjectLookup><subjectId>GrouperSystem</subjectId></actAsSubjectLookup>';
        $request .= '</WsRestFindGroupsRequest>';
        return grouper_find_groups_impl($request);
    }

    function grouper_get_group_by_uuid($match)
    {
        $request = '<WsRestFindGroupsRequest>';
        $request .= '<wsQueryFilter><queryFilterType>';
        $request .= 'FIND_BY_GROUP_UUID';
        $request .= '</queryFilterType>';
        $request .= '<groupUuid>' . $match . '</groupUuid>';
        $request .= '</wsQueryFilter>';
        $request .= '</WsRestFindGroupsRequest>';
        $result = grouper_find_groups_impl($request);
        return (count($result) > 0) ? $result[0] : FALSE;
    }

    function grouper_get_members($group)
    {
        $request = '<WsRestGetMembersRequest>
  <includeSubjectDetail>T</includeSubjectDetail>
  <wsGroupLookups>
    <WsGroupLookup>
      <groupName>' . htmlentities($group) . '</groupName>
    </WsGroupLookup>
  </wsGroupLookups>
</WsRestGetMembersRequest>';
        $result = $this->_doRest('groups', $request);
        $members = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->results->WsGetMembersResult->wsSubjects->WsSubject))) {
            foreach ($result->results->WsGetMembersResult->wsSubjects->WsSubject as $member) {
                $members[] = $this->_x2pMember($member);
            }
        } else {
            print_r($result);
        }
        return $members;
    }

    function grouper_get_group_privileges($group, $subject = NULL)
    {
        $request = '<WsRestGetGrouperPrivilegesLiteRequest>
  <groupName>' . htmlentities($group) . '</groupName>';
        if (isset($subject)) {
            $request .= '<subjectId>' . $subject . '</subjectId>';
        }
        $request .= '</WsRestGetGrouperPrivilegesLiteRequest>';
        #  <privilegeType>access</privilegeType>
        #  <privilegeName>admin</privilegeName>
        #  <actAsSubjectId>GrouperSystem</actAsSubjectId>
        $result = $this->_doRest('grouperPrivileges', $request);
        $privs = array();
        if (isset($result) and ($result !== FALSE) and (isset($result->privilegeResults->WsGrouperPrivilegeResult))) {
            foreach ($result->privilegeResults->WsGrouperPrivilegeResult as $member) {
                $privs[(string) $member->wsSubject->id][] = (string) $member->privilegeName;
            }
        } else {
            print_r($result);
        }
        return $privs;
    }

    function grouper_set_group_privileges($group, $subject, $privilege, $allowed = TRUE)
    {
        $request = '<WsRestAssignGrouperPrivilegesLiteRequest>
  <allowed>' . ($allowed ? 'T' : 'F') . '</allowed>
  <subjectId>' . $subject . '</subjectId>
  <groupName>' . $group . '</groupName>
  <privilegeType>access</privilegeType>
  <privilegeName>' . $privilege . '</privilegeName>
</WsRestAssignGrouperPrivilegesLiteRequest>';
        #  <actAsSubjectId>GrouperSystem</actAsSubjectId>
        return $this->_doRest('grouperPrivileges', $request, $allowed ? array(
            'SUCCESS' , 
            'SUCCESS_ALLOWED' , 
            'SUCCESS_ALLOWED_ALREADY_EXISTED'
        ) : array(
            
            'SUCCESS_NOT_ALLOWED' , 
            'SUCCESS_NOT_ALLOWED_DIDNT_EXIST'
        ));
    } */
}
