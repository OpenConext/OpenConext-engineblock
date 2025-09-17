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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RemoveOtherWorkflowStatesFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRemoveOtherWorkflowState()
    {
        $prodSp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $filter = new RemoveOtherWorkflowStatesFilter($prodSp, 'idp', 'sp');

        $prodIdp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://prod.idp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($prodIdp));

        $testIdp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://test.idp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($testIdp));

        $prodSp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $this->assertNotNull($filter->filterRole($prodSp));

        $testSp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://test.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_TEST)
        );
        $this->assertNull($filter->filterRole($testSp));

        $buggyIdp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://buggy.idp.example.edu', 'workflowState' => '')
        );
        $this->assertNull($filter->filterRole($buggyIdp));
    }

    public function testLogging()
    {
        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger
            ->shouldReceive('debug')
            ->with('Dissimilar workflow states (OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveOtherWorkflowStatesFilter -> prodaccepted)');

        $prodSp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://prod.sp.example.edu', 'workflowState' => ServiceProvider::WORKFLOW_STATE_PROD)
        );
        $filter = new RemoveOtherWorkflowStatesFilter($prodSp, 'idp', 'sp');
        $buggyIdp = Utils::instantiate(
            \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider::class,
            array('entityId' => 'https://buggy.idp.example.edu', 'workflowState' => '')
        );
        $this->assertNull($filter->filterRole($buggyIdp, $mockLogger));
    }
}
