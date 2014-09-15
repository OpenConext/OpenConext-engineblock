<?php

class EngineBlock_X509_PrivateKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testSigning()
    {
        $data = 'test';

        $privateKey = new EngineBlock_X509_PrivateKey(__DIR__ . '/test.pem.key');
        $signature = $privateKey->sign($data);

        $publicKey = new EngineBlock_X509_Certificate(openssl_pkey_get_public('file://' . __DIR__ . '/test.pem.crt'));

        $this->assertEquals(1, openssl_verify($data, $signature, $publicKey->toResource()));
    }
}