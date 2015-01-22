<?php

class EngineBlock_Corto_Filter_Command_ProvisionUser extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * This command modifies the response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * This command modifies the collabPersonId
     *
     * @return string
     */
    public function getCollabPersonId()
    {
        return $this->_collabPersonId;
    }

    public function execute()
    {
        $userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();
        $user = $userDirectory->registerUser($this->_responseAttributes);

        $subjectIdField = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue(
            'subjectIdAttribute',
            EngineBlock_UserDirectory::LDAP_ATTR_COLLAB_PERSON_ID
        );
        if (empty($user[$subjectIdField])) {
            throw new EngineBlock_Exception(
                "SubjectIdField '$subjectIdField' does not contain data for user: " . var_export($user, true)
            );
        }
        $subjectId = $user[$subjectIdField];

        $this->setCollabPersonId($subjectId);

        $this->_response->setCollabPersonId($subjectId);
        $this->_response->setOriginalNameId($this->_response->getAssertion()->getNameId());

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response->getAssertion()->setNameId(array(
            'Value' => $subjectId,
            'Format' => SAML2_Const::NAMEID_PERSISTENT,
        ));
    }
}
