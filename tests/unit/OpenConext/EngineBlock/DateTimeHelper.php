<?php

/**
 * Copyright 2014 SURFnet bv
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

namespace OpenConext\EngineBlock;

use OpenConext\EngineBlock\DateTime\DateTime;
use ReflectionProperty;

class DateTimeHelper
{
    /**
     * Fixes the `DateTime` returned by `DateTime::now()`.
     *
     * @param DateTime|null $now
     */
    public static function setCurrentTime(DateTime $now = null)
    {
        $nowProperty = new ReflectionProperty(DateTime::class, 'now');
        $nowProperty->setAccessible(true);
        $nowProperty->setValue($now);
    }
}
