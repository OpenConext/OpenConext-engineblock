<?php

/**
 * Remove any IDP set urn:collab:org:... only SURFconext is allowed to set these.
 */
class EngineBlock_Corto_Filter_Command_RemoveVoGroups extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_VO_PREFIX    = 'urn:collab:org:';
    const URN_IS_MEMBER_OF = 'urn:mace:dir:attribute-def:isMemberOf';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (!isset($this->_responseAttributes[self::URN_IS_MEMBER_OF])) {
            return;
        }

        $groups = &$this->_responseAttributes[self::URN_IS_MEMBER_OF];

        for ($i = 0; $i < count($groups); $i++) {
            $hasVoPrefix = strpos($groups[$i], self::URN_VO_PREFIX) === 0;
            if ($hasVoPrefix) {
                unset($groups[$i]);
            }
        }
    }
}