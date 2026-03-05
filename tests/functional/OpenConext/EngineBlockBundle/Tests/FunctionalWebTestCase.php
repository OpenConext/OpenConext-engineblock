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

namespace OpenConext\EngineBlockBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional tests.
 *
 * Symfony's ErrorHandler::register() installs a set_exception_handler() during
 * kernel boot (debug mode) that is intentionally never restored. PHPUnit 11
 * tracks exception handler stack depth before and after each test and marks the
 * test as risky when the count differs. Restoring after kernel shutdown brings
 * the stack back to the state PHPUnit observed before the test ran.
 */
abstract class FunctionalWebTestCase extends WebTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }
}
