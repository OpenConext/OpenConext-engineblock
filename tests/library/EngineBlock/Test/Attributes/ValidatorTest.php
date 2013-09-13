<?php

class EngineBlock_Test_Attributes_ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidator()
    {
        $validator = new EngineBlock_Attributes_Validator(array(
        ));
        $validator->setDefinitions(array(
            'a' => array(
                'Conditions' => array(
                    'warning' => array(
                        'min' => 1,
                    ),
                )
            ),
        ));

        $this->assertFalse($validator->validate(), "Min is 1 for non-existing attribute, attribute set is invalid");
        $this->assertFalse($validator->isValid('a'), "Min is 1 for non-existing attribute, attribute is invalid");
        $this->assertEmpty($validator->getErrors(), 'Triggering a warning does not also trigger an error');
        $this->assertNotEmpty($validator->getWarnings(), 'Min is 1 for non-existing attribute triggers a warning');
    }
}