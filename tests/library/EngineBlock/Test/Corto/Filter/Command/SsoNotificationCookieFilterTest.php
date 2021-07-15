<?php

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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
     * @var EngineBlock_Corto_Filter_Command_Helpers_CookieHandler
     */
    private $_cookieHandlerMock;

    /**
     * @var EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter
     */
    private $_ssoNotificationCookieFilter;

    public function setUp()
    {
        $_diContainerMock = Phake::mock(EngineBlock_Application_DiContainer::class);
        $this->_cookieHandlerMock = Phake::mock(EngineBlock_Corto_Filter_Command_Helpers_CookieHandler::class);
        $this->_ssoNotificationCookieFilter = new EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter(
            $this->_cookieHandlerMock,
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

        Phake::verify($this->_cookieHandlerMock)->clearCookie($this->_cookieName, $this->_path, $this->_domain);
    }

    public function testCookieNotFound()
    {
        $this->_ssoNotificationCookieFilter->execute();

        Phake::verifyNoInteraction($this->_cookieHandlerMock);
    }
}
