<?php

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