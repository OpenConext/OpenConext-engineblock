<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\ORM\QueryBuilder;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use Psr\Log\LoggerInterface;

/**
 * Class CompositeFilter
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Helper
 */
class CompositeFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters = array();

    /**
     * @var string
     */
    private $disallowedByFilter;

    /**
     * @param AbstractRole[] $roles
     * @return AbstractRole[]
     */
    public function filterRoles($roles)
    {
        $newRoles = array();
        foreach ($roles as $key => $role) {
            $role = $this->filterRole($role);

            if (!$role) {
                continue;
            }

            $newRoles[$key] = $role;
        }
        return $newRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null)
    {
        foreach ($this->filters as $filter) {
            $role = $filter->filterRole($role, $logger);

            if (!$role) {
                $this->disallowedByFilter = $filter->__toString();
                return null;
            }
        }
        return $role;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function add(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param string $repositoryClassName
     * @return Criteria|null
     */
    public function toCriteria($repositoryClassName)
    {
        $criteria = Criteria::create();
        if (empty($this->filters)) {
            return $criteria;
        }

        $expression = $this->toExpression($repositoryClassName);
        if (!$expression) {
            return $criteria;
        }

        return $criteria->where($expression);
    }

    /**
     * {@inheritdoc}
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder, $repositoryClassName)
    {
        foreach ($this->filters as $filter) {
            $filter->toQueryBuilder($queryBuilder, $repositoryClassName);
        }
        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toExpression($repositoryClassName)
    {
        $expressions = array();

        foreach ($this->filters as $filter) {
            $expression = $filter->toExpression($repositoryClassName);

            if (!$expression) {
                continue;
            }

            $expressions[] = $expression;
        }

        if (count($expression) === 0) {
            return null;
        }

        if (count($expression) === 1) {
            return $expression;
        }

        return new CompositeExpression(CompositeExpression::TYPE_AND, $expressions);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $filterStrings = array();
        foreach ($this->filters as $filter) {
            $filterStrings[] = $filter->__toString();
        }

        return '[' . implode(', ', $filterStrings) . ']';
    }

    /**
     * @return string
     */
    public function getDisallowedByFilter()
    {
        return $this->disallowedByFilter;
    }
}
