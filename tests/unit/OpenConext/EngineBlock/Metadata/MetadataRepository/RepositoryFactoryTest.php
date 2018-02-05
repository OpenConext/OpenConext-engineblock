<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use InvalidArgumentException;
use Mockery;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * Class RepositoryFactoryTest
 */
class RepositoryFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateWithoutConfig()
    {
        $factory = new RepositoryFactory();
        $factory->createFromConfig(
            array(),
            Mockery::mock('OpenConext\EngineBlock\Metadata\Container\ContainerInterface')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateWithNonExistingType()
    {
        $factory = new RepositoryFactory();
        $factory->createFromConfig(
            array('type' => 'invalid'),
            Mockery::mock('OpenConext\EngineBlock\Metadata\Container\ContainerInterface')
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateDoesNotImplementInterface()
    {
        require 'InvalidMetadataRepository.php';

        $factory = new RepositoryFactory();
        $factory->createFromConfig(
            array('type' => 'Invalid'),
            Mockery::mock('OpenConext\EngineBlock\Metadata\Container\ContainerInterface')
        );
    }

    public function testCreateInMemory()
    {
        $factory = new RepositoryFactory();
        $repository = $factory->createFromConfig(
            array('type' => 'InMemory'),
            Mockery::mock('OpenConext\EngineBlock\Metadata\Container\ContainerInterface')
        );
        $this->assertTrue($repository instanceof InMemoryMetadataRepository);
    }
}
