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
class EngineBlock_Test_Router_OpenSocialTest extends EngineBlock_Test_Router_Abstract
{
    public function testUnroutables()
    {
        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_OpenSocial')
            ->uri('')
            ->notRoutable()
            ->test();

        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_OpenSocial')
            ->uri('/')
            ->notRoutable()
            ->test();

        $this->_testRoute(
            'EngineBlock_Router_OpenSocial',
            '/',
            false
        );

        $this->_testRoute(
            'EngineBlock_Router_OpenSocial',
            '/default/index/index',
            false
        );
    }

    public function testPeopleRoutables()
    {
        $this->_testRoute(
            'EngineBlock_Router_OpenSocial',
            '/social/people/1234/@all',
            true,
            'Social',
            'Rest',
            'Index',
            array(
                'people/1234/@all'
            )
        );
    }
}