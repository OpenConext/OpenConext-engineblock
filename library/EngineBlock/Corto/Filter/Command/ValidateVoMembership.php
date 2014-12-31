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
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        // In filter stage we need to take a look at the VO context
        $vo = $this->_request->getVoContext();

        if (!$vo) {
            return;
        }

        // If in VO context, validate the user's membership

        EngineBlock_ApplicationSingleton::getLog()->debug("VO $vo membership required");

        $validator = $this->_getValidator();
        $isMember = $validator->isMember(
            $vo,
            $this->_collabPersonId,
            $this->_identityProvider->entityId
        );
        if (!$isMember) {
            throw new EngineBlock_Corto_Exception_UserNotMember("User not a member of VO $vo");
        }

        $this->_responseAttributes[self::VO_NAME_ATTRIBUTE] = array($vo);

    }

    /**
     * @return EngineBlock_VirtualOrganization_Validator
     */
    protected function _getValidator()
    {
        return new EngineBlock_VirtualOrganization_Validator();
    }

}
