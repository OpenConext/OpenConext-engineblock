<?php

/**
 * Attribute / NameId / Response manipulation / mangling
 */
class EngineBlock_Corto_Filter_Command_RunAttributeManipulations extends EngineBlock_Corto_Filter_Command_Abstract
{
    const TYPE_SP  = 'sp';
    const TYPE_IDP = 'idp';

    private $_type;

    function __construct($type = '')
    {
        assert('in_array($type, array(self::TYPE_SP, self::TYPE_IDP, ""))');
        $this->_type = $type;
    }

    public function getResponse()
    {
        return $this->_response;
    }

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
        $this->_response->setIntendedNameId($this->_collabPersonId);

        $entityId = ($this->_type === self::TYPE_IDP) ?
            $this->_response->getIssuer() :
            $this->_request->getIssuer();

        // Try entity specific file based manipulation from Service Registry
        $manipulator = new EngineBlock_Attributes_Manipulator_ServiceRegistry($this->_type);
        $manipulated = $manipulator->manipulate(
            $entityId,
            $this->_collabPersonId,
            $this->_responseAttributes,
            $this->_response,
            $this->_identityProvider,
            $this->_serviceProvider
        );

        $this->_response->setIntendedNameId($this->_collabPersonId);

        return (bool)$manipulated;
    }
}
