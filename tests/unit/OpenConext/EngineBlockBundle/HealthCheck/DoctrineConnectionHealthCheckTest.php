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

namespace OpenConext\EngineBlockBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\HealthCheck\DoctrineConnectionHealthCheck;
use OpenConext\MonitorBundle\HealthCheck\HealthReportInterface;
use OpenConext\MonitorBundle\Value\HealthReport;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DoctrineConnectionHealthCheckTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Group('health-check')]
    #[Test]
    public function it_returns_the_original_report_when_no_entity_manager_is_set(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('error');

        $healthCheck = new DoctrineConnectionHealthCheck('SELECT 1', $logger);

        $report = HealthReport::buildStatusUp();
        $result = $healthCheck->check($report);

        $this->assertSame($report, $result);
    }

    #[Group('health-check')]
    #[Test]
    public function it_returns_the_original_report_when_the_query_succeeds(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('error');

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')->with('SELECT 1')->once();

        $entityManager = Mockery::mock(EntityManager::class);
        $entityManager->shouldReceive('getConnection')->once()->andReturn($connection);

        $healthCheck = new DoctrineConnectionHealthCheck('SELECT 1', $logger);
        $healthCheck->setEntityManager($entityManager);

        $report = HealthReport::buildStatusUp();
        $result = $healthCheck->check($report);

        $this->assertSame($report, $result);
    }

    #[Group('health-check')]
    #[Test]
    public function it_logs_an_error_and_returns_status_down_when_the_query_fails(): void
    {
        $exception = new Exception('Connection refused');

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with('Unable to execute a query on the database.', ['exception' => $exception]);

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')->with('SELECT 1')->andThrow($exception);

        $entityManager = Mockery::mock(EntityManager::class);
        $entityManager->shouldReceive('getConnection')->once()->andReturn($connection);

        $healthCheck = new DoctrineConnectionHealthCheck('SELECT 1', $logger);
        $healthCheck->setEntityManager($entityManager);

        $report = HealthReport::buildStatusUp();
        $result = $healthCheck->check($report);

        $this->assertTrue($result->isDown());
        $this->assertSame(HealthReportInterface::STATUS_CODE_DOWN, $result->getStatusCode());
    }
}
