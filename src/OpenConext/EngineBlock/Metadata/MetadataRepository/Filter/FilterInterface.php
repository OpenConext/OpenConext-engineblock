<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\ORM\QueryBuilder;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use Psr\Log\LoggerInterface;

/**
 * Interface FilterInterface
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
interface FilterInterface
{
    /**
     * @param AbstractRole $role
     * @param LoggerInterface|null $logger
     * @return null|AbstractRole
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null);

    /**
     * @param string $repositoryClassName
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder, $repositoryClassName);

    /**
     * @return string
     */
    public function __toString();
}
