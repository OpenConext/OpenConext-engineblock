<?php

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
