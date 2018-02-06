<?php

namespace OpenConext\EngineBlock\Metadata\X509;

/**
 * Lazy Proxy for X509 Certificate.
 * Used when parsing / validation of the certificate is meant to be deferred until use
 * (useful if your upstream certificate supplier does not do checking and hands you all certificates at once).
 */
class X509CertificateLazyProxy
{
    /**
     * @var X509CertificateFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $certData;

    /**
     * @var X509Certificate
     */
    private $certificate = null;

    /**
     * @param X509CertificateFactory $factory
     * @param $certData
     */
    public function __construct(X509CertificateFactory $factory, $certData)
    {
        $this->factory = $factory;
        $this->certData = $certData;
    }

    /**
     * @param $methodName
     * @param $methodArguments
     * @return mixed
     */
    public function __call($methodName, $methodArguments)
    {
        if (!$this->certificate) {
            $this->certificate = $this->factory->fromCertData($this->certData);
        }

        return call_user_func_array(array($this->certificate, $methodName), $methodArguments);
    }

    /**
     * Take care not to serialize the openSSL resource ($this->certificate).
     */
    public function __sleep()
    {
        return array('certData', 'factory');
    }
}
