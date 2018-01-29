<?php

namespace OpenConext\EngineBlock\Metadata\X509;

/**
 * Class PrivateKeyTest
 * @package OpenConext\EngineBlock\Metadata\X509
 */
class X509PrivateKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testSigning()
    {
        $data = 'test';

        $filePath = __DIR__ . '/test.pem.key';
        $privateKey = new X509PrivateKey($filePath);
        $signature = $privateKey->sign($data);

        $publicKey = new X509Certificate(openssl_pkey_get_public('file://' . __DIR__ . '/test.pem.crt'));

        $this->assertEquals(1, openssl_verify($data, $signature, $publicKey->toResource()));
        $this->assertEquals($filePath, $privateKey->getFilePath());
    }

    public function testXmlSecurityKey()
    {
        $data = 'test';

        $filePath = __DIR__ . '/test.pem.key';
        $privateKey = new X509PrivateKey($filePath);
        $xmlSecurityKey = $privateKey->toXmlSecurityKey();

        $signature = $xmlSecurityKey->signData($data);

        $publicKey = new X509Certificate(openssl_pkey_get_public('file://' . __DIR__ . '/test.pem.crt'));

        $this->assertEquals(1, openssl_verify($data, $signature, $publicKey->toResource()));
    }
}
