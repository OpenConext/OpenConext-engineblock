<?php

/**
 * Copyright 2014 SURFnet B.V.
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
