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
class EngineBlock_Test_Router_DefaultTest extends EngineBlock_Test_Router_Abstract
{
    public function testRoutables()
    {
        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_Default')
            ->uri('')
            ->routable()
            ->test();

        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_Default')
            ->uri('///')
            ->routable()
            ->test();

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '//////module/',
            true,
            'Module'
        );
        
        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module//',
            true,
            'Module'
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module////controller////action',
            true,
            'Module',
            'Controller',
            'Action'
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/'
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module/controller',
            true,
            'Module',
            'Controller'
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module/controller/action',
            true,
            'Module',
            'Controller',
            'Action'
        );
    }

    public function testArguments()
    {
        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module/controller/action/arg1',
            true,
            'Module',
            'Controller',
            'Action',
            array(
                'arg1'
            )
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module/controller/action/arg1/arg2',
            true,
            'Module',
            'Controller',
            'Action',
            array(
                'arg1',
                'arg2'
            )
        );

        $this->_testRoute(
            "EngineBlock_Router_Default",
            '/module/controller/action/~!@#$%^&*()+-={}[]\|;:\'",<>.?',
            true,
            'Module',
            'Controller',
            'Action',
            array(
                '~!@#$%^&*()+-={}[]\|;:\'",<>.?'
            )
        );
    }

    public function testRequired()
    {
        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_Default')
            ->uri('/default/index/index')
            ->routable()
            ->test();

        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_Default')
            ->uri('/default/index/index')
            ->requireModule('Test')
            ->notRoutable()
            ->test();

        EngineBlock_Test_Router_AssertionBuilder::create($this)
            ->routerClass('EngineBlock_Router_Default')
            ->uri('/test/index/index')
            ->requireModule('Test')
            ->routable()
            ->expectModule('Test')
            ->test();
    }
}
