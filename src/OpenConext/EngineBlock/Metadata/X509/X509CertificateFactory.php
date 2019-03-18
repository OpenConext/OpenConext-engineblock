<?php

namespace OpenConext\EngineBlock\Metadata\X509;

use Exception;
use RuntimeException;

/**
 * Create an EngineBlock compatible X.509 Certificate object.
 */
class X509CertificateFactory
{
    /**
     * Create a certificate from a file path.
     *
     * @param string $filePath
     * @return X509Certificate
     * @throws RuntimeException
     */
    public function fromFile($filePath)
    {
        $pemString = file_get_contents($filePath);

        if (!$pemString) {
            throw new RuntimeException(sprintf('Unable to read file at path "%s".', $filePath));
        }

        try {
            $certificate = $this->fromString($pemString);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('File at "%s" does not contain a valid certificate.', $filePath), 0, $e);
        }
        return $certificate;
    }

    /**
     * Parse a given string as a X.509 certificate.
     *
     * @param string $x509CertificateContent
     * @return X509Certificate
     * @throws RuntimeException
     */
    public function fromString($x509CertificateContent)
    {
        $opensslCertificate = openssl_x509_read($x509CertificateContent);

        if (!$opensslCertificate) {
            throw new RuntimeException(
                sprintf('Unable to read X.509 certificate from content: "%s"', $x509CertificateContent)
            );
        }

        return new X509Certificate($opensslCertificate);
    }

    /**
     * Parse a 'certData' (or PEM encoded without header, footer or spaces) certificate.
     *
     * @param string $certData
     * @return X509Certificate
     */
    public function fromCertData($certData)
    {
        $certData = $this->cleanCertData($certData);

        $certificatePem = $this->formatKey($certData);

        return $this->fromString($certificatePem);
    }

    /**
     * Clean the 'certData' string of forbidden characters (whitespace).
     *
     * @param $certData
     * @return mixed
     */
    private function cleanCertData($certData)
    {
        return str_replace(array("\n", " ", "\t"), "", $certData);
    }

    /**
     * Given the 'certData' string, turn it back into a proper PEM encoded certificate.
     *
     * @param string $certData
     * @return string
     */
    private function formatKey($certData)
    {
        return X509Certificate::PEM_HEADER .
            PHP_EOL .
            // Chunk it in 64 character bytes
            chunk_split($certData, 64, PHP_EOL) .
            X509Certificate::PEM_FOOTER .
            PHP_EOL;
    }
}
