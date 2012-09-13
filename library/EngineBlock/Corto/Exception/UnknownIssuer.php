<?php

class EngineBlock_Corto_Exception_UnknownIssuer extends EngineBlock_Exception
{
    private $_entityId;
    private $_destination;

    function __construct($message, $entityId, $destination)
    {
        parent::__construct($message, self::CODE_NOTICE);
        $this->_entityId = $entityId;
        $this->_destination = $destination;
    }

    public function getEntityId()
    {
        return $this->_entityId;
    }

    public function getDestination()
    {
        return $this->_destination;
    }
}