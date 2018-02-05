<?php

namespace OpenConext\EngineBlock\Metadata\X509;

use PHPUnit_Framework_TestCase;

/**
 * Class X509KeyPairTest
 * @package OpenConext\EngineBlock\Metadata\X509
 */
class X509KeyPairTest extends PHPUnit_Framework_TestCase
{
    public function testNullInput()
    {
        $keyPair = new X509KeyPair();
        $this->assertNull($keyPair->getCertificate());
        $this->assertNull($keyPair->getPrivateKey());
    }
}
