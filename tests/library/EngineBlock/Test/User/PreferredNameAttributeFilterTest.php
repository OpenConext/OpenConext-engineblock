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

class EngineBlock_Test_User_PreferredNameAttributeFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_User_PreferredpreferredNameAttributeFilter
     */
    private $preferredNameAttributeFilter;

    public function setup()
    {
        $this->preferredNameAttributeFilter = new EngineBlock_User_PreferredNameAttributeFilter();
    }

    /**
     * @param string $expectedUserName
     * @param array $attributes
     * @dataProvider namesProvider
     */
    public function testCorrectNameIsReturned($expectedUserName, array $attributes)
    {
        $this->assertEquals($expectedUserName, $this->preferredNameAttributeFilter->getAttribute($attributes));
    }

    /**
     * @return array
     */
    public function namesProvider()
    {
        return array(
            array(
                'name' => 'testGivenName testSn',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:givenName' => array('testGivenName'),
                    'urn:mace:dir:attribute-def:sn' => array('testSn')
                )
            ),
            array(
                'name' => 'testCn',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:cn' => array('testCn')
                )
            ),
            array(
                'name' => 'testDisplayName',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:displayName' => array('testDisplayName')
                )
            ),
            array(
                'name' => 'testGivenName',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:givenName' => array('testGivenName')
                )
            ),
            array(
                'name' => 'testSn',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:sn' => array('testSn')
                )
            ),
            array(
                'name' => 'testMail',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:mail' => array('testMail')
                )
            ),
            array(
                'name' => 'testUid',
                'attributes' => array(
                    'urn:mace:dir:attribute-def:uid' => array('testUid')
                )
            )
        );
    }
}
