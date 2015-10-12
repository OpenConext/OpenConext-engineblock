<?php

class EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException extends EngineBlock_Corto_ProxyServer_Exception
{
    /**
     * @var string
     */
    protected $_entityId;

    /**
     * @param string         $entityId
     * @param Exception|null $previous
     */
    public function __construct($entityId, \Exception $previous = null)
    {
        $this->_entityId = $entityId;
        $message = "Unknown remote entity with entityid '$entityId'";

        parent::__construct($message, self::CODE_NOTICE, $previous);
    }

    /**
     * @param string $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->_entityId = $entityId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->_entityId;
    }
}
