<?php

class EngineBlock_X509_CertificateFactory
{
    public function fromCertData($certData)
    {
        // Remove newlines, spaces and tabs
        $certData = $this->_cleanCertData($certData);

        // Chunk it in 64 character bytes
        $certificatePem = $this->formatKey(
            $certData,
            EngineBlock_X509_Certificate::PEM_HEADER,
            EngineBlock_X509_Certificate::PEM_FOOTER
        );

        $openSslCertificate = openssl_x509_read($certificatePem);
        if ($openSslCertificate === false){
            throw new EngineBlock_Exception("Pub key $certificatePem is not a valid public key!");
        }

        return new EngineBlock_X509_Certificate($openSslCertificate);
    }

    /**
     * 
     * @param $file
     * @return EngineBlock_X509_Certificate
     * @throws EngineBlock_Exception
     */
    public function fromFile($file)
    {
        $opensslCertificate = openssl_x509_read(file_get_contents($file));
        if (!$opensslCertificate) {
            throw new EngineBlock_Exception("File '$file' does not contain a valid certificate!");
        }

        return new EngineBlock_X509_Certificate($opensslCertificate);
    }

    /**
     * @param $certData
     * @return mixed
     */
    private function _cleanCertData($certData)
    {
        $certData = str_replace(array("\n", " ", "\t", "\x09"), "", $certData);
        return $certData;
    }

    /**
     * @param string $certData
     * @param string $header
     * @param string $footer
     * @return string
     */
    private function formatKey($certData, $header, $footer)
    {
        return $header .
            PHP_EOL .
            // Chunk it in 64 character bytes
            chunk_split($certData, 64, PHP_EOL) .
            $footer .
            PHP_EOL;
    }
}