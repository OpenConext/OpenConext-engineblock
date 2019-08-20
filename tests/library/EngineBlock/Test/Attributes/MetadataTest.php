<?php

/**
 * Copyright 2014 SURFnet B.V.
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

class EngineBlock_Test_Attributes_MetadataTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $metadata = new EngineBlock_Attributes_Metadata(
            array(
                'a' => array(
                    'Name' => array(
                        'en' => 'The a name',
                        'nl' => 'De a naam',
                    ),
                    'Description' => array(
                        'en' => 'The a desc',
                        'nl' => 'De a omsch',
                    )
                )
            ),
            new Psr\Log\NullLogger()
        );
        $this->assertEquals($metadata->getName('a')       , 'The a name', 'Get Name');
        $this->assertEquals($metadata->getDescription('a'), 'The a desc', 'Get Name');
    }
}
