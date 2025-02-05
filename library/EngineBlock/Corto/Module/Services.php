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

class EngineBlock_Corto_Module_Services_Exception extends EngineBlock_Corto_ProxyServer_Exception
{
    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

}

class EngineBlock_Corto_Module_Services_SessionLostException extends EngineBlock_Corto_Module_Services_Exception
{
}

class EngineBlock_Corto_Module_Services_SessionNotStartedException extends EngineBlock_Corto_Module_Services_Exception
{
}

class EngineBlock_Corto_Module_Services extends EngineBlock_Corto_Module_Abstract
{
    protected $_aliases = array(
        'unsolicitedSingleSignOnService'=> 'singleSignOn',
        'debugSingleSignOnService'      => 'singleSignOn',
    );

    const BINDING_TYPE_HTTP_REDIRECT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const BINDING_TYPE_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const DEFAULT_REQUEST_BINDING  = self::BINDING_TYPE_HTTP_REDIRECT;
    const DEFAULT_RESPONSE_BINDING = self::BINDING_TYPE_HTTP_POST;

    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    /**
     * @param string $serviceName
     * @throws EngineBlock_Corto_Module_Services_Exception
     */
    public function serve($serviceName)
    {
        // If we have an alias, use the alias
        $resolvedServiceName = $serviceName;
        if (isset($this->_aliases[$serviceName])) {
            $resolvedServiceName = $this->_aliases[$serviceName];
        }

        $className = 'EngineBlock_Corto_Module_Service_' . ucfirst($resolvedServiceName);
        if (strtolower(substr($className, -1 * strlen('service'))) === "service") {
            $className = substr($className, 0, -1 * strlen('service'));
        }
        if (class_exists($className, true)) {
            $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
            /** @var $serviceName EngineBlock_Corto_Module_Service_Abstract */
            $service = $this->factoryService($className, $this->_server);
            $service->serve($serviceName, $diContainer->getSymfonyRequest());
            return;
        }

        throw new EngineBlock_Corto_Module_Services_Exception(
            sprintf(
                'Unable to load service "%s" (resolved to "%s") tried className "%s"!',
                $serviceName,
                $resolvedServiceName,
                $className
            )
        );
    }

    /**
     * Creates services objects with their own specific needs
     *
     * @param string $className
     * @param EngineBlock_Corto_ProxyServer $server
     * @return EngineBlock_Corto_Module_Service_Abstract
     */
    private function factoryService($className, EngineBlock_Corto_ProxyServer $server)
    {
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        switch($className) {
            case EngineBlock_Corto_Module_Service_ProvideConsent::class :
                return new EngineBlock_Corto_Module_Service_ProvideConsent(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getConsentFactory(),
                    $diContainer->getConsentService(),
                    $diContainer->getAuthenticationStateHelper(),
                    $diContainer->getTwigEnvironment(),
                    $diContainer->getProcessingStateHelper(),
                    $diContainer->getDiscoverySelectionService()
                );
            case EngineBlock_Corto_Module_Service_ProcessConsent::class :
                return new EngineBlock_Corto_Module_Service_ProcessConsent(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getConsentFactory(),
                    $diContainer->getAuthenticationStateHelper(),
                    $diContainer->getProcessingStateHelper()
                );
            case EngineBlock_Corto_Module_Service_AssertionConsumer::class :
                return new EngineBlock_Corto_Module_Service_AssertionConsumer(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getSession(),
                    $diContainer->getProcessingStateHelper(),
                    $diContainer->getStepupGatewayCallOutHelper(),
                    $diContainer->getServiceProviderFactory()
                );
            case EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer::class :
                return new EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer(
                    $server,
                    $diContainer->getProcessingStateHelper()
                );
            case EngineBlock_Corto_Module_Service_StepupAssertionConsumer::class :
                return new EngineBlock_Corto_Module_Service_StepupAssertionConsumer(
                    $server,
                    $diContainer->getSession(),
                    $diContainer->getProcessingStateHelper(),
                    $diContainer->getStepupGatewayCallOutHelper(),
                    $diContainer->getLoaRepository()
                );
            case EngineBlock_Corto_Module_Service_SingleSignOn::class :
                return new EngineBlock_Corto_Module_Service_SingleSignOn(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getTwigEnvironment(),
                    $diContainer->getServiceProviderFactory(),
                    $diContainer->getDiscoverySelectionService()
                );
            case EngineBlock_Corto_Module_Service_ContinueToIdp::class :
                return new EngineBlock_Corto_Module_Service_ContinueToIdp(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getTwigEnvironment(),
                    $diContainer->getServiceProviderFactory()
                );
            default :
                return new $className($server, $diContainer->getXmlConverter(), $diContainer->getTwigEnvironment());
        }
    }
}
