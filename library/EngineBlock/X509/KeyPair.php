<?php

class EngineBlock_X509_KeyPair
{
    /**
     * @var EngineBlock_X509_Certificate
     */
    private $certificate;

    /**
     * @var EngineBlock_X509_PrivateKey
     */
    private $private;

    public function __construct(
        EngineBlock_X509_Certificate $certificate = null,
        EngineBlock_X509_PrivateKey $private = null
    ) {
        $this->certificate = $certificate;
        $this->private = $private;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function getPrivateKey()
    {
        return $this->private;
    }
}