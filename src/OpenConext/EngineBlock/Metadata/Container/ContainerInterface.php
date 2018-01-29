<?php

namespace OpenConext\EngineBlock\Metadata\Container;

use Doctrine\ORM\EntityManager;
use OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface;

/**
 * Interface ContainerInterface
 * @package OpenConext\EngineBlock\Metadata\Container
 */
interface ContainerInterface
{
    /**
     * @return RestClientInterface
     */
    public function getServiceRegistryClient();

    /**
     * @return EntityManager
     */
    public function getEntityManager();
}
