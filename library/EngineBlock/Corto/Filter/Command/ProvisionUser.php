<?php

use OpenConext\Value\Saml\NameIdFormat;

class EngineBlock_Corto_Filter_Command_ProvisionUser extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseModificationInterface,
    EngineBlock_Corto_Filter_Command_CollabPersonIdModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollabPersonId()
    {
        return $this->_collabPersonId;
    }

    public function execute()
    {
        $userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();
        $user = $userDirectory->identifyUser($this->_responseAttributes);

        $collabPersonIdValue = $user->getCollabPersonId()->getCollabPersonId();
        $this->setCollabPersonId($collabPersonIdValue);

        $resolver = new EngineBlock_Saml2_NameIdResolver();
        $nameId = $resolver->resolve(
            $this->_request,
            $this->_response,
            $this->_serviceProvider,
            $this->_collabPersonId
        );

        // To actually set the collabPersonIdValue, override it with the one we just generated. The resolver used the
        // intended name id format for this purpose (but this is not set yet)
        if ($nameId->Format == NameIdFormat::UNSPECIFIED) {
            $nameId->value = $collabPersonIdValue;
        }

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response->getAssertion()->setNameId($nameId);
    }
}
