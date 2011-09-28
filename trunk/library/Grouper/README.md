# PHP Internet2 Grouper Client #

Client to access Internet2 Grouper, converted from code given by Hans Zandbelt of SURFnet.

## Unconverted methods ##


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
    }
