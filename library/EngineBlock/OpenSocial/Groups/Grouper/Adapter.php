<?php
 
class EngineBlock_OpenSocial_Groups_Grouper_Adapter 
{
    /**
     * @var EngineBlock_Groups_Grouper
     */
    protected $_grouperClient;

    public function getGroupMembers($groupMemberUid, $groupName)
    {
        $members = $this->_getGrouperClient()->getMembers($groupMemberUid, $groupName);
        foreach ($members as &$member) {
            $member = self::_getOpenSocialPersonFromGrouperMember($member);
        }
        return $members;
    }

    protected static function _getOpenSocialPersonFromGrouperMember($member)
    {
        return $member;
    }
}
