<?php

use PHPUnit\Framework\TestCase;

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

class EngineBlock_Test_Attributes_ValidatorTest extends TestCase
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
