<?php
 
class EngineBlock_X509Certificate 
{
    public static function getPemFromCertData($certData)
    {
        $publicKey = implode('', explode("\n", $certData));
        $publicKey = "-----BEGIN CERTIFICATE-----" . PHP_EOL .
                chunk_split($publicKey, 64, PHP_EOL) .
                "-----END CERTIFICATE-----" . PHP_EOL;

        $openSslPubKey = openssl_pkey_get_public($publicKey);
        if ($openSslPubKey === false){
            throw new EngineBlock_Exception("Pub key $publicKey is not a valid public key!");
        }

        return $publicKey;
    }
}
