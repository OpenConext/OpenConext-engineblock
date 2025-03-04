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

use SAML2\Compat\ContainerSingleton;

class EngineBlock_Application_Bootstrapper
{
    const P3P_HEADER = 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"';

    /**
     * @var EngineBlock_ApplicationSingleton
     */
    protected $_application;

    /**
     * @var bool
     */
    protected $_bootstrapped = false;

    /**
     * @param EngineBlock_ApplicationSingleton $application
     */
    public function __construct(EngineBlock_ApplicationSingleton $application)
    {
        $this->_application = $application;
    }

    /**
     * Bootstrap the application.
     *
     * Note that the order or bootstrapping is very important.
     *
     * @return EngineBlock_ApplicationSingleton Bootstrapped application singleton
     */
    public function bootstrap()
    {
        if ($this->_bootstrapped) {
            return $this;
        }

        $this->_bootstrapSessionConfiguration();

        $this->_bootstrapPhpSettings();
        $this->_bootstrapErrorReporting();

        $this->_bootstrapHttpCommunication();
        $this->_bootstrapSaml2();

        $this->_bootstrapped = true;

        return $this;
    }

    protected function _bootstrapSessionConfiguration()
    {
        $settings = $this->_application->getDiContainer();

        session_set_cookie_params(
            0,
            $settings->getCookiePath(),
            '',
            $settings->getCookieUseSecure(),
            true
        );
        session_name('main');
    }

    protected function _bootstrapHttpCommunication()
    {
        $httpRequest = EngineBlock_Http_Request::createFromEnvironment();

        $configuredHostname = $this->_application->getDiContainer()->getHostname();
        if (empty($configuredHostname)) {
            throw new RuntimeException(
                "The 'hostname' parameters.yml setting is required"
            );
        }
        $httpRequest->setHostName($configuredHostname);

        $this->_application->getLogInstance()->info(
            sprintf(
                'Handling incoming request: %s %s',
                $httpRequest->getMethod(),
                $httpRequest->getUri()
            )
        );
        $this->_application->setHttpRequest($httpRequest);

        $response = new EngineBlock_Http_Response();
        // workaround, P3P is needed to support iframes like iframe gadgets in portals
        $response->setHeader('P3P', self::P3P_HEADER);
        $this->_application->setHttpResponse($response);
    }

    private function _bootstrapSaml2()
    {
        $container = new EngineBlock_Saml2_Container($this->_application->getLogInstance());
        ContainerSingleton::setContainer($container);
    }

    protected function _bootstrapPhpSettings()
    {
        $settings = $this->_application->getDiContainer()->getPhpSettings();
        foreach ($settings as $name => $value) {
            ini_set($name, $value);
        }
    }

    protected function _bootstrapErrorReporting()
    {
        $errorHandler = new EngineBlock_Application_ErrorHandler($this->_application);
        register_shutdown_function  (array($errorHandler, 'shutdown'));
//        set_error_handler           (array($errorHandler, 'error')); // TODO REENABLE!
        set_exception_handler       (array($errorHandler, 'exception'));

        $this->_application->setErrorHandler($errorHandler);
    }
}
