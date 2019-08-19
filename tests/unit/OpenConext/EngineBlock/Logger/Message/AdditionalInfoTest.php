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

namespace OpenConext\EngineBlock\Logger\Message;

use EngineBlock_Exception;
use PHPUnit_Framework_TestCase as TestCase;

final class AdditionalInfoTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function message_has_correct_severity()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_ALERT);
        $additionalInfo = AdditionalInfo::createFromException($exception);

        $this->assertSame('ALERT', $additionalInfo->getSeverity());
    }
}
