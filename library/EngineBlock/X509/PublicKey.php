<?php

class EngineBlock_X509_PublicKey
{
    const PEM_HEADER = '-----BEGIN PUBLIC KEY-----';
    const PEM_FOOTER = '-----END PUBLIC KEY-----';

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
        $keyDetails = openssl_pkey_get_details($this->opensslResource);
        if (!$keyDetails || !$keyDetails['key']) {
            throw new EngineBlock_Exception("Unable to convert key to PEM?");
        }

        return $keyDetails['key'];
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