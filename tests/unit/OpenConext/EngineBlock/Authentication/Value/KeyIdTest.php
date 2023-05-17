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

class KeyIdTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function key_id_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new KeyId($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function key_id_can_be_retrieved()
    {
        $keyIdValue = '20160403';

        $keyId = new KeyId($keyIdValue);

        $this->assertEquals($keyIdValue, $keyId->getKeyId());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function key_ids_are_only_equal_if_created_with_the_same_value()
    {
        $firstId  = '20160403';
        $secondId = 'default';

        $base      = new KeyId($firstId);
        $same      = new KeyId($firstId);
        $different = new KeyId($secondId);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_key_id_can_be_cast_to_string()
    {
        $keyId = new KeyId('20160403');

        $this->assertIsString((string) $keyId);
    }
}
