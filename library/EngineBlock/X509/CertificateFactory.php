<?php

/**
 * Create an EngineBlock compatible X.509 Certificate object.
 */
class EngineBlock_X509_CertificateFactory
{
    /**
     * Create a certificate from a file path.
     *
     * @param string $filePath
     * @return EngineBlock_X509_Certificate
     * @throws EngineBlock_Exception
     */
    public function fromFile($filePath)
    {
        $pemString = file_get_contents($filePath);

        if (!$pemString) {
            throw new EngineBlock_Exception("Unable to read file at path '$filePath'.");
        }

        try {
            $certificate = $this->fromString($pemString);
        } catch (Exception $e) {
            throw new EngineBlock_Exception(
                "File at '$filePath' does not contain a valid certificate.",
                EngineBlock_Exception::CODE_ERROR,
                $e
            );
        }
        return $certificate;
    }

    /**
     * Parse a given string as a X.509 certificate.
     *
     * @param string $x509CertificateContent
     * @return EngineBlock_X509_Certificate
     * @throws EngineBlock_Exception
     */
    public function fromString($x509CertificateContent)
    {
        $opensslCertificate = openssl_x509_read($x509CertificateContent);

        if (!$opensslCertificate) {
            throw new EngineBlock_Exception(
                "Unable to read X.509 certificate from content: '$x509CertificateContent'"
            );
        }

        return new EngineBlock_X509_Certificate($opensslCertificate);
    }

    /**
     * Parse a 'certData' (or PEM encoded without header, footer or spaces) certificate.
     *
     * @param string $certData
     * @return EngineBlock_X509_Certificate
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
        return EngineBlock_X509_Certificate::PEM_HEADER .
            PHP_EOL .
            // Chunk it in 64 character bytes
            chunk_split($certData, 64, PHP_EOL) .
            EngineBlock_X509_Certificate::PEM_FOOTER .
            PHP_EOL;
    }
}