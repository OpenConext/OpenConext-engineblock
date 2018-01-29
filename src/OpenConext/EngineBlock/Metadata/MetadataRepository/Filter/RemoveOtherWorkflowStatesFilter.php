<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveOtherWorkflowStatesFilter
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class RemoveOtherWorkflowStatesFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $workflowState;

    /**
     * @param ServiceProvider $serviceProvider
     */
    public function __construct(ServiceProvider $serviceProvider)
    {
        $this->workflowState = $serviceProvider->workflowState;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null)
    {
        $result = $role->workflowState === $this->workflowState ? $role : null;
        if (!is_null($logger) && is_null($result)) {
            $logger->debug(sprintf('Dissimilar workflow states (%s)', $this->__toString()));
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder, $repositoryClassName)
    {
        $queryBuilder
            ->andWhere('role.workflowState = :requiredWorkflowState')
            ->setParameter('requiredWorkflowState', $this->workflowState);
    }

    /**
     * {@inheritdoc}
     */
    public function toExpression($repositoryClassName)
    {
        return Criteria::expr()->eq('workflowState', $this->workflowState);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return parent::__toString() . ' -> ' . $this->workflowState;
    }
}
