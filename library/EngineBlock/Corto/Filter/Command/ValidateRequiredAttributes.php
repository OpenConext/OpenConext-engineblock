<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;

class EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const URN_MACE_TERENA_SCHACHOMEORG = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

    /**
     * {@inheritdoc}
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
