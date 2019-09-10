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

class EngineBlock_TimeProvider_Default implements EngineBlock_TimeProvider_Interface
{
    const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function timestamp($deltaSeconds = 0, $time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        return gmdate(self::TIMESTAMP_FORMAT, $time + $deltaSeconds);
    }

    public function time()
    {
        return time();
    }
}
