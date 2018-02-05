<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveEntityByEntityIdFilter
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class RemoveEntityByEntityIdFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @param string $entityId
     */
    public function __construct($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null)
    {
        $result = $role->entityId === $this->entityId ? null : $role;

        if (!is_null($logger) && is_null($result)) {
            $logger->debug(sprintf('Invalid EntityId found (%s)', $this->__toString()));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder, $repositoryClassName)
    {
        return $queryBuilder
            ->andWhere('role.entityId <> :removeEntityId')
            ->setParameter('removeEntityId', $this->entityId);
    }

    /**
     * {@inheritdoc}
     */
    public function toExpression($repositoryClassName)
    {
        return Criteria::expr()->neq('entityId', $this->entityId);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString() . ' -> ' . $this->entityId;
    }
}
