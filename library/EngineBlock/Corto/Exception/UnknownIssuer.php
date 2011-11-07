<?php

class EngineBlock_Corto_Exception_UnknownIssuerException extends Exception
{
    private $_entityId;
    private $_destination;

    function __construct($message, $entityId, $destination)
    {
        parent::__construct($message);
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