<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * Tests for EngineBlock_Log
 *
 * @group log
 */
class EngineBlock_Test_LogTest extends PHPUnit_Framework_TestCase
{
    public $config = array('null'=>array('writerName' => 'Null'));

    public function testCanInstantiateLogObject()
    {
        $this->assertInstanceOf(
            'EngineBlock_Log',
            EngineBlock_Log::factory($this->config)
        );
    }

    public function testCanAttachObjectsOfAnyType()
    {
        EngineBlock_Log::factory($this->config)
            ->attach(array(), 'empty')
            ->attach('string', 'string')
            ->attach((object)array(), 'object');
    }


    public function testAttachedObjectsAreLoggedAfterLogCall()
    {
        EngineBlock_Log::factory($this->config)
            ->attach(array('arrayelement'), 'object');
    }
}