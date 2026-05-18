<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use PHPUnit\Framework\TestCase;

/**
 * Tests the command list in EngineBlock_Corto_Filter_Output.
 */
class EngineBlock_Test_Corto_Filter_OutputFilterTest extends TestCase
{
    public function testGetCommandsReturnsNonEmptyList(): void
    {
        $server = Phake::mock(EngineBlock_Corto_ProxyServer::class);
        $filter = new EngineBlock_Corto_Filter_Output($server);
        $commands = $filter->getCommands();

        self::assertNotEmpty($commands, 'Output filter must have at least one command');
    }

    public function testGetCommandsContainsOnlyFilterCommands(): void
    {
        $server = Phake::mock(EngineBlock_Corto_ProxyServer::class);
        $filter = new EngineBlock_Corto_Filter_Output($server);
        $commands = $filter->getCommands();

        foreach ($commands as $command) {
            self::assertInstanceOf(
                EngineBlock_Corto_Filter_Command_Abstract::class,
                $command,
                'All commands must extend the abstract filter command'
            );
        }
    }
}
