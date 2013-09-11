<?php

class EngineBlock_Test_Attributes_MaxTest extends PHPUnit_Framework_TestCase
{
    public function testMaxEmpty()
    {
        $validator = new EngineBlock_Attributes_Validator_Max('a', '3');

        $this->assertTrue($validator->validate(array()), 'Maximum of 3 on a non-existing attribute validates.');
        $this->assertEmpty($validator->getMessages(), 'Maximum of 3 on a non-existing attribute does not give messages');
    }

    public function testMaxLess()
    {
        $validator = new EngineBlock_Attributes_Validator_Max('a', '3');

        $this->assertTrue($validator->validate(array('a' => array(1,2))), 'Maximum of 3 with only 2 values validates');
        $this->assertEmpty($validator->getMessages(), 'Maximum of 3 with only 2 values does not give messages');
    }

    public function testMaxEquals()
    {
        $validator = new EngineBlock_Attributes_Validator_Max('a', '3');

        $this->assertTrue($validator->validate(array('a' => array(1,2,3))), 'Maximum of 3 with 3 values validates');
        $this->assertEmpty($validator->getMessages(), 'Maximum of 3 with 3 values does not give messages');
    }

    public function testMaxMore()
    {
        $validator = new EngineBlock_Attributes_Validator_Max('a', '3');

        $this->assertFalse($validator->validate(array('a' => array(1,2,3,4))), 'Maximum of 3 with 4 values fails validation');
        $this->assertNotEmpty($validator->getMessages(), 'Maximum of 3 with 4 values gives a message');
    }
}