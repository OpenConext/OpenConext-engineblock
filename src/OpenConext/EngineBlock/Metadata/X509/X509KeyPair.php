<?php

namespace OpenConext\EngineBlock\Metadata\X509;

/**
 * Class X509KeyPair
 * @package OpenConext\EngineBlock\Metadata
 */
class X509KeyPair
{
    /**
     * @var X509Certificate
     */
    private $certificate;

    /**
     * @var X509PrivateKey
     */
    private $private;

    /**
     * @param X509Certificate $certificate
     * @param X509PrivateKey $private
     */
    public function __construct(
        X509Certificate $certificate = null,
        X509PrivateKey $private = null
    ) {
        $this->certificate = $certificate;
        $this->private = $private;
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return X509PrivateKey
     */
    public function getPrivateKey()
    {
        return $this->private;
    }
}
