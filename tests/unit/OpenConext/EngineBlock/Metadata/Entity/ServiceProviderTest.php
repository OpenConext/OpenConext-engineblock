<?php

namespace OpenConext\EngineBlock\Metadata\Entity;

use PHPUnit_Framework_TestCase;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityId = 'https://sp.example.edu';
        $sp = new ServiceProvider($entityId);
        $this->assertEquals($entityId, $sp->entityId);
    }
}
