<?php

/**
 * Adds group the current user is a member of
 */
class EngineBlock_Corto_Filter_Command_AddCollabPersonIdAttribute extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_OID_COLLAB_PERSON_ID  = 'urn:oid:1.3.6.1.4.1.1076.20.40.40.1';

    /**
     * This command modifies the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $this->_responseAttributes[self::URN_OID_COLLAB_PERSON_ID] = array(
            0 => $this->_collabPersonId
        );
    }
}