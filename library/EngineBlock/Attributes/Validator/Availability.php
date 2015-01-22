<?php

class EngineBlock_Attributes_Validator_Availability extends EngineBlock_Attributes_Validator_Abstract
{
    const AVAILABILITY_UNRESERVED                = 'unreserved';
    const ERROR_ATTRIBUTE_VALIDATOR_AVAILABILITY = 'error_attribute_validator_availability';
    const SCHAC_HOME_ORGANIZATION_URN            = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

    private $metadataRepository;

    public function __construct($attributeName, $options)
    {
        parent::__construct($attributeName, $options);
        $this->metadataRepository = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getMetadataRepository();
    }

    public function validate(array $attributes)
    {
        if (!$this->_options === static::AVAILABILITY_UNRESERVED) {
            // @todo warn
            return null;
        }

        if ($this->_attributeName !== static::SCHAC_HOME_ORGANIZATION_URN) {
            // @todo warn
            return null;
        }

        if (empty($attributes[$this->_attributeName])) {
            return null;
        }

        if (count($attributes[$this->_attributeName]) > 1) {
            return null;
        }

        $schacHomeOrganization = $attributes[$this->_attributeName][0];

        $reservedSchacHomeOrganizations = $this->metadataRepository->findReservedSchacHomeOrganizations();

        if (in_array($schacHomeOrganization, $reservedSchacHomeOrganizations)) {
            return true;
        }

        $this->_messages[] = array(
            static::ERROR_ATTRIBUTE_VALIDATOR_AVAILABILITY,
            $this->_attributeAlias ? $this->_attributeAlias : $this->_attributeName,
            $this->_options,
            $schacHomeOrganization
        );

        return false;
    }
}
