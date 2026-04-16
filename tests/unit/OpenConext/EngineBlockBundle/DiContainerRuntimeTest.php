<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace Tests\OpenConext\EngineBlockBundle;

use OpenConext\EngineBlock\Service\Wayf\IdpSplitter;
use OpenConext\EngineBlockBundle\Bridge\DiContainerRuntime;
use OpenConext\EngineBlockBundle\Service\WayfViewModelFactory;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class DiContainerRuntimeTest extends TestCase
{
    public function testGetPreferredIdpEntityIdsReturnsEmptyArrayByDefault(): void
    {
        $runtime = new DiContainerRuntime(
            $this->createStub(Environment::class),
            new IdpSplitter(),
            $this->createStub(WayfViewModelFactory::class),
        );

        $this->assertSame([], $runtime->getPreferredIdpEntityIds());
    }

    public function testGetPreferredIdpEntityIdsReturnsConfiguredList(): void
    {
        $entityIds = ['https://idp1.example.org', 'https://idp2.example.org'];

        $runtime = new DiContainerRuntime(
            $this->createStub(Environment::class),
            new IdpSplitter(),
            $this->createStub(WayfViewModelFactory::class),
            $entityIds,
        );

        $this->assertSame($entityIds, $runtime->getPreferredIdpEntityIds());
    }
}
