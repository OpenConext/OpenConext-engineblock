<?php

class EngineBlock_X509_KeyPair
{
    /**
     * @var EngineBlock_X509_PublicKey
     */
    private $public;

    /**
     * @var EngineBlock_X509_PrivateKey
     */
    private $private;

    public function __construct(EngineBlock_X509_PrivateKey $private, EngineBlock_X509_PublicKey $public)
    {
        $this->private = $private;
        $this->public = $public;
    }

    public function getPublicKey()
    {
        return $this->public;
    }

    public function getPrivateKey()
    {
        return $this->private;
    }
}