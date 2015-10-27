<?php

class EngineBlock_Corto_Filter_Command_ValidateVoMembership extends EngineBlock_Corto_Filter_Command_Abstract
{
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

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
        // @todo determine if we need to go to PEP/PDP
        if ($this->requirePep())
        {
            EngineBlock_ApplicationSingleton::getLog()->debug("Executing PEP decision.");

            $validator = $this->_getValidator();
            $isMember = $validator->isMember(
                $this->_collabPersonId,
                $this->_identityProvider->entityId,
                $this->_serviceProvider->entityId,
                $this->_responseAttributes
            );
            if (!$isMember) {
                $message = "PDP: Access denied.";
                if ($validator->getMessage())
                {
                    $message = $validator->getMessage();
                }
                throw new EngineBlock_Corto_Exception_UserNotMember($message);
            }
        }
    }

    /**
     * @return EngineBlock_VirtualOrganization_Validator
     */
    protected function _getValidator()
    {
        return new EngineBlock_VirtualOrganization_Validator();
    }

    /**
     * @todo Metadata field from SP check !!!!
     *   coin:policy_enforcement_decision_required
     *
     * @return bool
     */
    private function requirePep()
    {
        return true;
    }

}
