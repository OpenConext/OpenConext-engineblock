<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;

class EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_MACE_TERENA_SCHACHOMEORG = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';
    const URN_MACE_DIR_UID = 'urn:mace:dir:attribute-def:uid';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    /**
     * @throws EngineBlock_Corto_Exception_MissingRequiredFields
     */
    public function execute()
    {
        // ServiceRegistry override of SchacHomeOrganization, set it and skip validation
        $excluded = array();
        if ($this->_identityProvider->schacHomeOrganization) {
            $this->_responseAttributes[self::URN_MACE_TERENA_SCHACHOMEORG] = array(
                $this->_identityProvider->schacHomeOrganization
            );
            $excluded[] = static::URN_MACE_TERENA_SCHACHOMEORG;
        }

        $subjectIdField = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue(
            'subjectIdAttribute',
           'uid+sho'
        );

       // If engineblock is configured to use eppn as subjectIdField, sho and uid should not be mandatory
       if ($subjectIdField == 'eppn') {
          $excluded[] = static::URN_MACE_TERENA_SCHACHOMEORG;
          $excluded[] = static::URN_MACE_DIR_UID;
        }

        $validationResult = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAttributeValidator()
            ->validate($this->_responseAttributes, $excluded);

        if ($validationResult->hasErrors()) {
            throw new EngineBlock_Corto_Exception_MissingRequiredFields(
                'Errors validating attributes' .
                    ' errors: '     . print_r($validationResult->getErrors(), true) .
                    ' attributes: ' . print_r($this->_responseAttributes, true)
            );
        }
    }
}
