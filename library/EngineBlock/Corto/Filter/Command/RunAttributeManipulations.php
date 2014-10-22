<?php

/**
 * Attribute / NameId / Response manipulation / mangling
 */
class EngineBlock_Corto_Filter_Command_RunAttributeManipulations extends EngineBlock_Corto_Filter_Command_Abstract
{
    const TYPE_SP  = 'sp';
    const TYPE_REQUESTER_SP = 'requester-sp';
    const TYPE_IDP = 'idp';

    private $_type;

    function __construct($type)
    {
        if (!in_array($type, array(self::TYPE_SP, self::TYPE_IDP, self::TYPE_REQUESTER_SP))) {
            throw new \EngineBlock_Exception("Invalid type for Attribute Manipulation: '$type'");
        }
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

        if ($this->_type === self::TYPE_IDP) {
            $entityId = $this->_response->getIssuer();
            $spMetadata = $this->_spMetadata;
        }
        else if ($this->_type === self::TYPE_SP) {
            $entityId = $this->_request->getIssuer();
            $spMetadata = $this->_spMetadata;
        }
        else if ($this->_type === self::TYPE_REQUESTER_SP) {
            $spMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata($this->_spMetadata, $this->_request, $this->_server);
            if ($spMetadata['EntityID'] === $this->_spMetadata['EntityID']) {
                return;
            }

            $entityId = $spMetadata['EntityID'];
        }
        else {
            throw new EngineBlock_Exception('Attribute Manipulator encountered an unexpected type: ' . $this->_type);
        }

        // Try entity specific file based manipulation from Service Registry
        $manipulator = new EngineBlock_Attributes_Manipulator_ServiceRegistry($this->_type);
        $manipulator->manipulate(
            $entityId,
            $this->_collabPersonId,
            $this->_responseAttributes,
            $this->_response,
            $this->_idpMetadata,
            $spMetadata
        );

        $this->_response->setIntendedNameId($this->_collabPersonId);
    }
}
