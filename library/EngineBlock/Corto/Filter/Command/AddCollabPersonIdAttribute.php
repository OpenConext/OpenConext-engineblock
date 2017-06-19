<?php

/**
 * Adds group the current user is a member of
 */
class EngineBlock_Corto_Filter_Command_AddCollabPersonIdAttribute extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const URN_OID_COLLAB_PERSON_ID  = 'urn:oid:1.3.6.1.4.1.1076.20.40.40.1';

    /**
     * {@inheritdoc}
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
