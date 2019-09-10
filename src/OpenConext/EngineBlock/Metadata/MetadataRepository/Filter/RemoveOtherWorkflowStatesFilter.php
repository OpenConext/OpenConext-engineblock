<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Doctrine\ORM\QueryBuilder;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
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
     * @var string
     */
    private $idpEntityId;

    /**
     * @var string
     */
    private $spEntityId;

    /**
     * @param ServiceProvider $serviceProvider
     * @param string $idpEntityId
     * @param string $spEntityId
     */
    public function __construct(ServiceProvider $serviceProvider, $idpEntityId, $spEntityId)
    {
        $this->workflowState = $serviceProvider->workflowState;
        $this->idpEntityId = $idpEntityId;
        $this->spEntityId = $spEntityId;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null)
    {
        // EngineBlock itself should always work regardless of the workflow state.
        if (($role instanceof ServiceProvider && $role->entityId === $this->spEntityId) ||
            ($role instanceof IdentityProvider && $role->entityId === $this->idpEntityId)) {
            return $role;
        }

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
        return $queryBuilder
            ->andWhere('(role.workflowState = :requiredWorkflowState OR role.entityId IN (:eb_ids))')
            ->setParameter('requiredWorkflowState', $this->workflowState)
            ->setParameter('eb_ids', [$this->idpEntityId, $this->spEntityId]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return parent::__toString() . ' -> ' . $this->workflowState;
    }
}
