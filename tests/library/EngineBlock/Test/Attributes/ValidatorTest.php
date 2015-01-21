<?php

class EngineBlock_Test_Attributes_ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidator()
    {
        $validator = new EngineBlock_Attributes_Validator(
            array(
                'a' => array(
                    'Conditions' => array(
                        'warning' => array(
                            'min' => 1,
                        ),
                    )
                ),
            ),
            new EngineBlock_Attributes_Validator_Factory()
        );

        $validationResult = $validator->validate(array());

        $this->assertFalse($validationResult->isValid(), "Min is 1 for non-existing attribute, attribute set is invalid");
        $this->assertFalse($validationResult->isValid('a'), "Min is 1 for non-existing attribute, attribute is invalid");
        $this->assertEmpty($validationResult->getErrors(), 'Triggering a warning does not also trigger an error');
        $this->assertNotEmpty($validationResult->getWarnings(), 'Min is 1 for non-existing attribute triggers a warning');
    }
}
