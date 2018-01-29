<?php

namespace OpenConext\EngineBlock\Metadata\Entity;

use PHPUnit_Framework_TestCase;

/**
 * Class IdentityProviderTest
 * @package OpenConext\EngineBlock\Metadata\Entity
 */
class IdentityProviderTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityId = 'https://idp.example.edu';
        $idp = new IdentityProvider($entityId);
        $this->assertEquals($entityId, $idp->entityId);
    }
}
