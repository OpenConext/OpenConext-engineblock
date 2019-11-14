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

namespace OpenConext\EngineBlock\Service\TimeProvider;

use PHPUnit\Framework\TestCase;

class TimeProviderTest extends TestCase
{
    public function test_renders_time_in_expected_format()
    {
        $time = 1571054255;
        $provider = new TimeProvider();
        $formatted = $provider->timestamp(0, $time);
        $this->assertEquals('2019-10-14T11:57:35Z', $formatted);
    }

    public function test_renders_time_in_the_future()
    {
        $time = 1571054255;
        $provider = new TimeProvider();
        $formatted = $provider->timestamp(604800, $time);
        $this->assertEquals('2019-10-21T11:57:35Z', $formatted);
    }
}
