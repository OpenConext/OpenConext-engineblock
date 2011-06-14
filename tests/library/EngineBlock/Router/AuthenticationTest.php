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

class Test_EngineBlock_Router_AuthenticationTest extends Test_EngineBlock_Router_Abstract
{
    public function testUnroutables()
    {
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/default/index/index',
            false
        );

        // no short form for module
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/auth/index/index',
            false
        );
    }

    public function testPeopleRoutables()
    {
        // No url, routes to default module / controller / action
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            ''
        );

        // No url, routes to default module / controller / action
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/'
        );

        // Short form
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/authentication/idp/metadata',
            true,
            'Authentication',
            'IdentityProvider',
            'Metadata'
        );

        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/authentication/sp/metadata',
            true,
            'Authentication',
            'ServiceProvider',
            'Metadata'
        );

        // Long form
        $this->_testRoute(
            'EngineBlock_Router_Authentication',
            '/authentication/identity-provider/metadata',
            true,
            'Authentication',
            'IdentityProvider',
            'Metadata'
        );
    }
}