<?php

class EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException extends EngineBlock_Corto_ProxyServer_Exception
{
    protected $_entityId;

    public function __construct($entityId)
    {
        $this->_entityId = $entityId;
        $this->message = "Unknown remote entity with entityid '$entityId'";
    }

    public function setEntityId($entityId)
    {
        $this->_entityId = $entityId;
        return $this;
    }

    public function getEntityId()
    {
        return $this->_entityId;
    }
}