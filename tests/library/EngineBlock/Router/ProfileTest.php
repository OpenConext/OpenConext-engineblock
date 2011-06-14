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

require_once 'Abstract.php';

class Test_EngineBlock_Router_ProfileTest extends Test_EngineBlock_Router_Abstract
{
    public function testUnroutables()
    {
        // Don't route default urls
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            '/default/index/index',
            false
        );

        // Don't route authentication urls
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            '/authentication/index/index',
            false
        );
    }

    public function testPeopleRoutables()
    {
        // No url, routes to default module / controller / action
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            ''
        );

        // No url, routes to default module / controller / action
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            '/'
        );

        // Route /profile/index/index
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            '/profile',
            true,
            'Profile',
            'Index',
            'Index'
        );

        // Route /profile/delete-user/success
        $this->_testRoute(
            'EngineBlock_Router_Profile',
            '/profile/delete-user/success',
            true,
            'Profile',
            'DeleteUser',
            'Success'
        );
    }
}