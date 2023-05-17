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

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UidTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function uid_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Uid($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function uid_can_be_retrieved()
    {
        $uidValue = md5('foobar');

        $uid = new Uid($uidValue);

        $this->assertSame($uidValue, $uid->getUid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function uid_equality_is_determined_based_on_value()
    {
        $base = new Uid('some:uid');
        $same = new Uid('some:uid');
        $different = new Uid('a:different:uid');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_uid_can_be_cast_to_string()
    {
        $uid = new Uid('some:uid');

        $this->assertIsString((string) $uid);
    }
}
