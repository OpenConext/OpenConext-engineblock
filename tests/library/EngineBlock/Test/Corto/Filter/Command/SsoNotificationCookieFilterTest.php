<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Test_Corto_Filter_Command_SsoNotificationCookieFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $_domain = "domain";
    private $_path = "/";
    private $_cookieName = "ssonot";
    private $_cookieValue = "value";

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var CookieService
     */
    private $_cookieServiceMock;

    /**
     * @var EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter
     */
    private $_ssoNotificationCookieFilter;

    public function setUp(): void
    {
        $_diContainerMock = Phake::mock(EngineBlock_Application_DiContainer::class);
        $this->_cookieServiceMock = Phake::mock(CookieService::class);
        $this->_ssoNotificationCookieFilter = new EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter(
            $this->_cookieServiceMock,
            $_diContainerMock
        );
        $this->_request = new Request();

        Phake::when($_diContainerMock)
            ->getSymfonyRequest(Phake::anyParameters())
            ->thenReturn($this->_request);
        Phake::when($_diContainerMock)
            ->getCookiePath(Phake::anyParameters())
            ->thenReturn($this->_path);
        Phake::when($_diContainerMock)
            ->getCookieDomain(Phake::anyParameters())
            ->thenReturn($this->_domain);

        $featureConfiguration = Phake::mock(FeatureConfiguration::class);
        Phake::when($featureConfiguration)
            ->isEnabled('eb.enable_sso_notification')
            ->thenReturn(true);
        Phake::when($_diContainerMock)
            ->getFeatureConfiguration()
            ->thenReturn($featureConfiguration);
    }

    public function testCookieFound()
    {
        $this->_request->cookies->add([ $this->_cookieName => $this->_cookieValue ]);

        $this->_ssoNotificationCookieFilter->execute();

        Phake::verify($this->_cookieServiceMock)->clearCookie($this->_cookieName, $this->_path, $this->_domain);
    }

    public function testCookieNotFound()
    {
        $this->_ssoNotificationCookieFilter->execute();

        Phake::verifyNoInteraction($this->_cookieServiceMock);
    }
}
