<?php

class EngineBlock_X509_PrivateKey
{
    private $_filePath;

    function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new EngineBlock_Exception("Private key file '$filePath' does not exist.");
        }

        if (!is_readable($filePath)) {
            throw new EngineBlock_Exception("Private key file '$filePath' exists but is not readable.");
        }

        $this->_filePath = $filePath;
    }

    public function filePath()
    {
        return $this->_filePath;
    }

    public function toXmlSecurityKey()
    {
        $privateKeyObj = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $privateKeyObj->loadKey($this->_filePath, true);
        return $privateKeyObj;
    }

    /**
     * Sign some data with this private key.
     *
     * Note how we never actually load the private key into memory, we let OpenSSL do this and afterwards immediately
     * tell OpenSSL to forget the key to reduce chances of leakage.
     *
     * @param string $data
     * @return string
     */
    public function sign($data)
    {
        $privateKeyResource = openssl_pkey_get_private('file://' . $this->_filePath);

        $signature = null;
        openssl_sign($data, $signature, $privateKeyResource);

        openssl_free_key($privateKeyResource);

        return $signature;
    }
}
