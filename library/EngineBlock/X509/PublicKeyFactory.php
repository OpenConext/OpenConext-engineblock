<?php

class EngineBlock_X509_PublicKeyFactory
{
    const CERT_HEADER = '-----BEGIN CERTIFICATE-----';
    const CERT_FOOTER = '-----END CERTIFICATE-----';

    public function fromCertData($certData)
    {
        // Remove newlines, spaces and tabs
        $certData = $this->_cleanCertData($certData);

        // Chunk it in 64 character bytes
        $publicKey = $this->formatKey(
            $certData,
            static::CERT_HEADER,
            static::CERT_FOOTER
        );

        $openSslPubKey = openssl_pkey_get_public($publicKey);
        if ($openSslPubKey === false){
            throw new EngineBlock_Exception("Pub key $publicKey is not a valid public key!");
        }

        return new EngineBlock_X509_PublicKey($openSslPubKey);
    }

    public function fromFile($file)
    {
        $opensslPublicKey = openssl_pkey_get_public(file_get_contents($file));
        if (!$opensslPublicKey) {
            throw new EngineBlock_Exception("File '$file' does not contain a valid public key!");
        }

        return new EngineBlock_X509_PublicKey($opensslPublicKey);
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