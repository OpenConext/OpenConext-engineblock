<?php

namespace OpenConext\EngineBlock\Metadata\X509;

use RuntimeException;

/**
 * Class X509Certificate
 * @package OpenConext\EngineBlock\Metadata
 */
class X509Certificate
{
    const PEM_HEADER = '-----BEGIN CERTIFICATE-----';
    const PEM_FOOTER = '-----END CERTIFICATE-----';

    /**
     * @var resource
     */
    private $opensslResource;

    /**
     * @param $opensslResource
     * @throws RuntimeException
     */
    public function __construct($opensslResource)
    {
        if (empty($opensslResource)) {
            throw new RuntimeException('Invalid OpenSSL key!');
        }

        $this->opensslResource = $opensslResource;
    }

    /**
     * @return resource
     */
    public function toResource()
    {
        return $this->opensslResource;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function toPem()
    {
        $pem = '';
        $exported = openssl_x509_export($this->opensslResource, $pem);

        if (!$exported) {
            throw new RuntimeException("Unable to convert certificate to PEM?");
        }

        return $pem;
    }

    /**
     * @return string
     */
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
