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

namespace OpenConext\EngineBlockBundle\HealthCheck;

use Doctrine\ORM\EntityManager;
use Exception;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\MonitorBundle\HealthCheck\HealthCheckInterface;
use OpenConext\MonitorBundle\HealthCheck\HealthReportInterface;
use OpenConext\MonitorBundle\Value\HealthReport;

/**
 * Test if there is a working database connection.
 */
class DoctrineConnectionHealthCheck implements HealthCheckInterface
{
    /**
     * @var EntityManager|null
     */
    private $entityManager;

    /**
     * @var string
     */
    private $query;

    public function __construct($query)
    {
        Assertion::nonEmptyString($query, 'health check query');
        $this->query = $query;
    }

    /**
     * Run the doctrine connection health check.
     *
     * @param HealthReportInterface $report
     * @return HealthReportInterface
     */
    public function check(HealthReportInterface $report): HealthReportInterface
    {
        // Was the entityManager injected? When it is not the project does not use Doctrine ORM
        if (!is_null($this->entityManager)) {
            try {
                $this->entityManager->getConnection()->query($this->query);
            } catch (Exception $e) {
                return HealthReport::buildStatusDown('Unable to execute a query on the database.');
            }
        }

        return $report;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
