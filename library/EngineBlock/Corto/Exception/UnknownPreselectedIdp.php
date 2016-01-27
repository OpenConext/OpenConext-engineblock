<?php

class EngineBlock_Corto_Exception_UnknownPreselectedIdp extends EngineBlock_Exception
{
    private $_remoteIdpMd5Hash;

    public function __construct($message, $remoteIdpMd5Hash)
    {
        parent::__construct($message, self::CODE_NOTICE);
        $this->_remoteIdpMd5Hash = $remoteIdpMd5Hash;
    }

    /**
     * @return string
     */
    public function getRemoteIdpMd5Hash()
    {
        return $this->_remoteIdpMd5Hash;
    }
}
