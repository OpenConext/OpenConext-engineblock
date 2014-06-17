<?php

class EngineBlock_X509_Certificate
{
    const PEM_HEADER = '-----BEGIN CERTIFICATE-----';
    const PEM_FOOTER = '-----END CERTIFICATE-----';

    /**
     * @var resource
     */
    private $opensslResource;

    function __construct($opensslResource)
    {
        if (empty($opensslResource)) {
            throw new EngineBlock_Exception('Invalid OpenSSL key!');
        }

        $this->opensslResource = $opensslResource;
    }

    public function toResource()
    {
        return $this->opensslResource;
    }

    public function toPem()
    {
        $pem = '';
        $exported = openssl_x509_export($this->opensslResource, $pem);

        if (!$exported) {
            throw new EngineBlock_Exception("Unable to convert certificate to PEM?");
        }

        return $pem;
    }

    public function toCertData()
    {
        $pemKey = $this->toPem();

        $lines = explode("\n", $pemKey);
        $data = '';
        foreach ($lines as $line) {
            $line = rtrim($line);

            // Skip the header
            if ($line === self::PEM_HEADER) {
                continue;
            }

            // End transformation on footer
            if ($line === self::PEM_FOOTER) {
                break;
            }

            $data .= $line;
        }

        return $data;
    }
}