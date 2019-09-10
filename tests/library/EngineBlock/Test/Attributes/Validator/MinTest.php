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

class EngineBlock_Test_Attributes_MinTest extends PHPUnit_Framework_TestCase
{
    public function testMinEmpty()
    {
        $validator = new EngineBlock_Attributes_Validator_Min('a', '3');

        $this->assertFalse($validator->validate(array()), 'Minimum of 3 on a non-existing attribute fails validation');
        $this->assertNotEmpty($validator->getMessages(), 'Minimum of 3 on a non-existing attribute gives messages');
    }

    public function testMinLess()
    {
        $validator = new EngineBlock_Attributes_Validator_Min('a', '3');

        $this->assertFalse($validator->validate(array('a' => array(1,2))), 'Minimum of 3 with only 2 values does not validate');
        $this->assertNotEmpty($validator->getMessages(), 'Minimum of 3 with only 2 values gives a message');
    }

    public function testMinEquals()
    {
        $validator = new EngineBlock_Attributes_Validator_Min('a', '3');

        $this->assertTrue($validator->validate(array('a' => array(1,2,3))), 'Minimum of 3 with 3 values validates');
        $this->assertEmpty($validator->getMessages(), 'Minimum of 3 with 3 values does not give messages');
    }

    public function testMinMore()
    {
        $validator = new EngineBlock_Attributes_Validator_Min('a', '3');

        $this->assertTrue($validator->validate(array('a' => array(1,2,3,4))), 'Minimum of 3 with 4 values validates');
        $this->assertEmpty($validator->getMessages(), 'Minimum of 3 with 4 values does not give a message');
    }

    public function testMinZero()
    {
        $validator = new EngineBlock_Attributes_Validator_Min('a', '0');

        $this->assertTrue($validator->validate(array()), 'Minimum of 0 with non-existing attribute validates');
        $this->assertEmpty($validator->getMessages(), 'Minimum of 0 with non-existing attribute does not give messages');
    }
}
