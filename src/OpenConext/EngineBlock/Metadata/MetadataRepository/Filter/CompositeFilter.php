<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

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
