<?php

class EngineBlock_Corto_Filter_Command_PolicyDecisionPoint extends EngineBlock_Corto_Filter_Command_Abstract
{
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

        $requirePep = $this->requirePep();

        EngineBlock_ApplicationSingleton::getLog()->debug("Policy Enforcement Point consult " . $requirePep);

        if ($requirePep)
        {
            $validator = $this->_getValidator();
            $hasAccess = $validator->hasAccess(
                $this->_collabPersonId,
                $this->_identityProvider->entityId,
                $this->_serviceProvider->entityId,
                $this->_responseAttributes
            );
            if (!$hasAccess) {
                $message = "Policy Decision Point: access denied.";
                if ($validator->getMessage())
                {
                    $message = $validator->getMessage();
                }
                EngineBlock_ApplicationSingleton::getLog()->debug("Policy Enforcement Point access denied: " . $message);
                throw new EngineBlock_Corto_Exception_PEPNoAccess($message);
            }
        }
    }

    /**
     * @return EngineBlock_PolicyDecisionPoint_Validator
     */
    protected function _getValidator()
    {
        return new EngineBlock_PolicyDecisionPoint_Validator();
    }

    /**
     *
     * @return bool
     */
    private function requirePep()
    {
        return $this->_serviceProvider->policyEnforcementDecisionRequired ? true : false;
    }

}
