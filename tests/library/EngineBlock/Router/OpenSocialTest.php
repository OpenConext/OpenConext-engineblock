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

require_once(dirname(__FILE__) . '/../../../autoloading.inc.php');

class Test_EngineBlock_Router_OpenSocialTest extends PHPUnit_Framework_TestCase
{
    public function testUnroutables()
    {
        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('');
        $this->assertFalse($routable, "OpenSocial router does not route empty uri");

        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('/');
        $this->assertFalse($routable, "OpenSocial router does not route /");

        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route('/default/index/index');
        $this->assertFalse($routable, "OpenSocial router does not route /default/index/index");
    }

    public function testPeopleRoutables()
    {
        $uri = '/social/people/1234/@all';
        $router = new EngineBlock_Router_OpenSocial();
        $routable = $router->route($uri);
        $this->assertTrue($routable, "OpenSocial router knows to route '$uri'");
        $this->assertEquals('social', $router->getModuleName()    , 'OpenSocial router routes to social module');
        $this->assertEquals('rest'  , $router->getControllerName(), 'OpenSocial people call routes to rest controller');
    }
}