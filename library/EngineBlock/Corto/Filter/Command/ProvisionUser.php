<?php

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;

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
        $uid = $this->_responseAttributes['urn:mace:dir:attribute-def:uid'][0];
        $sho = $this->_responseAttributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0];
        if (!$uid || !$sho) {
            throw new EngineBlock_Exception(
                'Cannot register user due to missing uid and/or schacHomeOrganization attribute'
            );
        }
        $userId = new Uid($uid);
        $schacHomeOrganization = new SchacHomeOrganization($sho);
        $userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();

        // attempt to find the user
        $collabPersonId = CollabPersonId::generateFrom($userId, $schacHomeOrganization);
        $user = $userDirectory->findUserBy($collabPersonId->getCollabPersonId());
        if (!$user) {
            // if not found, register the user
            $user = $userDirectory->registerUser($uid, $sho);
        }

        $collabPersonIdValue = $user->getCollabPersonId()->getCollabPersonId();
        $this->setCollabPersonId($collabPersonIdValue);

        $this->_response->setCollabPersonId($collabPersonIdValue);
        $this->_response->setOriginalNameId($this->_response->getAssertion()->getNameId());

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response->getAssertion()->setNameId(array(
            'Value' => $collabPersonIdValue,
            'Format' => SAML2_Const::NAMEID_PERSISTENT,
        ));
    }
}
