<?php

/**
 * Lazy Proxy for X509 Certificate.
 * Used when parsing / validation of the certificate is meant to be deferred until use
 * (bad idea in theory, sometimes useful in practice).
 */
class EngineBlock_X509_CertificateLazyProxy
{
    /**
     * @var EngineBlock_X509_CertificateFactory
     */
    private $_factory;

    /**
     * @var string
     */
    private $_certData;

    /**
     * @var EngineBlock_X509_Certificate
     */
    private $_certificate = null;

    /**
     * @param EngineBlock_X509_CertificateFactory $factory
     * @param $certData
     */
    function __construct(EngineBlock_X509_CertificateFactory $factory, $certData)
    {
        $this->_factory = $factory;
        $this->_certData = $certData;
    }

    public function __call($methodName, $methodArguments)
    {
        if (!$this->_certificate) {
            $this->_certificate = $this->_factory->fromCertData($this->_certData);
        }

        return call_user_func_array(array($this->_certificate, $methodName), $methodArguments);
    }
}