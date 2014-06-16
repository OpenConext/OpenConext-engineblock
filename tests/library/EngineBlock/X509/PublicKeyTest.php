<?php

class EngineBlock_X509_KeyTest extends PHPUnit_Framework_TestCase
{
    private $filePath;

    function __construct()
    {
        $this->filePath = 'file://' . __DIR__ . '/test.pem';;
        $this->filePath2 = 'file://' . __DIR__ . '/test2.crt';;
    }

    public function testToPem()
    {
        $key = new EngineBlock_X509_PublicKey(openssl_pkey_get_public($this->filePath));
        $this->assertEquals(file_get_contents($this->filePath), $key->toPem());
    }

    public function testToCertData()
    {
        $key = new EngineBlock_X509_PublicKey(openssl_pkey_get_public($this->filePath));
        $this->assertEquals(
            "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHikastc8+I81zCg/qWW8dMr8mqvXQ3qbPAmu0RjxoZVI47tvskYlFAXOf0sPrhO2nUuooJngnHV0639iTTEYG1vckNaW2R6U5QTdQ5Rq5u+uV3pMk7w7Vs4n3urQ6jnqt2rTXbC1DNa/PFeAZatbf7ffBBy0IGO0zc128IshYcwIDAQAB",
            $key->toCertData()
        );
    }

    public function testFromCert()
    {
        $key = new EngineBlock_X509_PublicKey(openssl_pkey_get_public($this->filePath2));
        $this->assertEquals(
           "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtmkPTmLAAElkf2jeTQdpkKhbAXNd7jdJ0m45JNVJvFaLhqOzpkjNHorq+ZsvFLz2LYdQ10DOrtsIhatfUbX1WViTpq8wH1R3PVUY/V4+E/jFaUeGw1nLb8VU9Tj5vS9tykCr2qg5lKO7kj7AQ9+hCB+3RTgcnQ7Ul32U1tInp/pWa+guJz7scw2xyfiZaNm64noplAUDXrV/3rbZwIJSfed+rCQ5IbGtdtJpGD4VZ73qWzrcT8lOWQ1KQzZjEEdv0jkQgSXH7y0lWeCLeDuu5evxCZ5BUS3qyKjqZP1vI2N1EkGizNljO7FgRsRBSg1XExYKzh+dGfeCtjPysHmNmwIDAQAB",
            $key->toCertData()
        );
    }
}