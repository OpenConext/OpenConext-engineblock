<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

/**
 * @package OpenConext\EngineBlock\Metadata\ServiceRegistry
 */
class RepositoryFactory
{
    /**
     * @param array $config
     * @param ContainerInterface $container
     * @return MetadataRepositoryInterface
     * @throws RuntimeException
     */
    public function createFromConfig(array $config, ContainerInterface $container)
    {
        if (!isset($config['type'])) {
            throw new InvalidArgumentException('serviceRegistryAdapter config missing type!');
        }

        $namespace = isset($config['namespace']) ? $config['namespace'] : __NAMESPACE__;
        $className = $namespace . '\\' . $config['type'] . 'MetadataRepository';
        if (!class_exists($className, true)) {
            throw new InvalidArgumentException("Unable to find '$className'");
        }

        $class = new ReflectionClass($className);
        if (!$class->implementsInterface(__NAMESPACE__ . '\\MetadataRepositoryInterface')) {
            throw new RuntimeException("$className does not implement MetadataRepositoryInterface");
        }

        return call_user_func($className . '::createFromConfig', $config, $container);
    }
}
