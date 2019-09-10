<?php

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

class EngineBlock_Test_ApplicationSingletonTest extends PHPUnit_Framework_TestCase
{
    public function testIpAddress()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $_SERVER['REMOTE_ADDR'] = 'REMOTE_ADDR_TEST';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'X_FORWARDED_TEST,REMOTE_ADDR_TEST';

        // X_FORWARDED from untrusted proxy
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $application->getClientIpAddress());

        // @TODO: when one of the forwarded for addresses is a trusted proxy, the other
        //        remote addr is used. That's not easy to test because the application
        //        class does no use DI.
        //
        // Example test case:
        //     $configuration->trustedProxyIps = array('REMOTE_ADDR_TEST');
        //     $this->assertEquals('X_FORWARDED_TEST', $application->getClientIpAddress());

        // No X_FORWARDED, use REMOTE_ADDR
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $application->getClientIpAddress());

        // If neither and running on CLI mode (like unit testing) get special IP
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals(EngineBlock_ApplicationSingleton::IP_ADDRESS_CLI, $application->getClientIpAddress());
    }
}
