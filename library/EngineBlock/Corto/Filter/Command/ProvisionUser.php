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
        // Provisioning of the user account
        $subjectId = $this->_getProvisioning()->provisionUser($this->_responseAttributes);

        $this->setCollabPersonId($subjectId);

        $this->_response->setCollabPersonId($subjectId);
        $this->_response->setOriginalNameId($this->_response->getAssertion()->getNameId());

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response->getAssertion()->setNameId(array(
            'Value' => $subjectId,
            'Format' => SAML2_Const::NAMEID_PERSISTENT,
        ));
    }

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }
}
