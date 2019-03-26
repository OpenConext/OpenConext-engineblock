<?php

namespace OpenConext\EngineBlock\Metadata\X509;

use RuntimeException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * X.509 private key representation.
 */
class X509PrivateKey
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param $filePath
     * @throws RuntimeException
     */
    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('Private key file "%s" does not exist.', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Private key file "%s" exists but is not readable.', $filePath));
        }

        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return XMLSecurityKey
     */
    public function toXmlSecurityKey()
    {
        $privateKeyObj = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        $privateKeyObj->loadKey($this->filePath, true);
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
        $privateKeyResource = openssl_pkey_get_private('file://' . $this->filePath);

        $signature = null;
        openssl_sign($data, $signature, $privateKeyResource);

        openssl_free_key($privateKeyResource);

        return $signature;
    }
}
