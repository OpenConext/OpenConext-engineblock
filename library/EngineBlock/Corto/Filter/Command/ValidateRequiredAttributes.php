<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_MACE_TERENA_SCHACHOMEORG = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

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
     * @todo refactor this to use EngineBlock_Attributes_Validator
     */
    public function execute()
    {
        $errors = array();

        if ($this->_identityProvider->schacHomeOrganization) {
            // ServiceRegistry override of SchacHomeOrganization, set it and skip validation
            $this->_responseAttributes[self::URN_MACE_TERENA_SCHACHOMEORG] = array(
                $this->_identityProvider->schacHomeOrganization
            );
        }
        else {
            $error = $this->_requireValidSchacHomeOrganization($this->_responseAttributes);
            if ($error) {
                $errors[] = $error;
            }
        }

        $error = $this->_requireValidUid($this->_responseAttributes);
        if ($error) {
            $errors[] = $error;
        }

        if (!empty($errors)) {
            throw new EngineBlock_Corto_Exception_MissingRequiredFields(
                'Errors validating attributes' .
                    ' errors: '     . print_r($errors, true) .
                    ' attributes: ' . print_r($this->_responseAttributes, true)
            );
        }
    }

    protected function _requireValidSchacHomeOrganization($responseAttributes)
    {
        if (!isset($responseAttributes[self::URN_MACE_TERENA_SCHACHOMEORG])) {
            return self::URN_MACE_TERENA_SCHACHOMEORG . " missing in attributes!";
        }

        $schacHomeOrganizationValues = $responseAttributes[self::URN_MACE_TERENA_SCHACHOMEORG];

        if (count($schacHomeOrganizationValues) === 0) {
            return self::URN_MACE_TERENA_SCHACHOMEORG . " has no values";
        }

        if (count($schacHomeOrganizationValues) > 1) {
            return  self::URN_MACE_TERENA_SCHACHOMEORG . " has too many values";
        }

        $schacHomeOrganization = $schacHomeOrganizationValues[0];

        $reservedSchacHomeOrganization = $this->_isReservedSchacHomeOrganization($schacHomeOrganization);
        if ($reservedSchacHomeOrganization === true) {
            return self::URN_MACE_TERENA_SCHACHOMEORG . " is reserved for another IdP!";
        }

        $validHostName = false;
        try {
            $uri = Zend_Uri_Http::fromString('http://' . $schacHomeOrganization);
            $validHostName = $uri->validateHost($schacHomeOrganization);
        } catch(Zend_Validate_Exception $e) {}
        if (!$validHostName) {
            return self::URN_MACE_TERENA_SCHACHOMEORG . " is not a valid hostname!";
        }

        // Passed all the checks, valid SHO!
        return false;
    }

    /**
     * @param string $schacHomeOrganization
     * @return bool
     */
    protected function _isReservedSchacHomeOrganization($schacHomeOrganization)
    {
        return in_array($schacHomeOrganization, $this->_server->getRepository()->findReservedSchacHomeOrganizations());
    }

    protected function _requireValidUid($responseAttributes)
    {
        if (!isset($responseAttributes['urn:mace:dir:attribute-def:uid'])) {
            return "urn:mace:dir:attribute-def:uid missing in attributes!";
        }

        if (count($responseAttributes['urn:mace:dir:attribute-def:uid']) === 0) {
            return "urn:mace:dir:attribute-def:uid has no values";
        }

        if (count($responseAttributes['urn:mace:dir:attribute-def:uid']) > 1) {
            return "urn:mace:dir:attribute-def:uid has more than one value";
        }

        $uid = $responseAttributes['urn:mace:dir:attribute-def:uid'][0];
        // Note that this is a bit naive as the spec states that this can be 256 characters
        // of UTF-8, so in theory a person could have a uid of 100 kanji characters (which take up 3 bytes)
        // and our check would fail, even though it is completely valid.
        // But we'll cross that ブリッジ when we get to it.
        if (strlen($uid) > 256) {
            return "urn:mace:dir:attribute-def:uid is more than 256 characters long!";
        }
        return false;
    }
}
