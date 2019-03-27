<?php

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

class EngineBlock_Corto_Module_Services extends EngineBlock_Corto_Module_Abstract
{
    protected $_aliases = array(
        'spCertificateService'          => 'Certificate',
        'idpCertificateService'         => 'Certificate',
        'spMetadataService'             => 'Metadata',
        'idpMetadataService'            => 'Metadata',
        'unsolicitedSingleSignOnService'=> 'singleSignOn',
        'debugSingleSignOnService'      => 'singleSignOn',
    );

    private $movedServices = [
        'ProcessConsent',
        'ProvideConsent'
    ];

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
        // Moved legacy services are loaded from the DI container
        if ($this->isMovedService($serviceName)){
            $service = $this->loadMovedService($serviceName);
            $service->serve();
        } elseif (class_exists($className, true)) {
            /** @var $serviceName EngineBlock_Corto_Module_Service_Abstract */
            $service = $this->factoryService($className, $this->_server);
            $service->serve($serviceName);
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

        switch ($className) {
            case EngineBlock_Corto_Module_Service_AssertionConsumer::class :
                return new EngineBlock_Corto_Module_Service_AssertionConsumer(
                    $server,
                    $diContainer->getXmlConverter(),
                    $diContainer->getSession()
                );
                break;
            default :
                return new $className($server, $diContainer->getXmlConverter(), $diContainer->getTwigEnvironment());
        }
    }

    private function loadMovedService($serviceName)
    {
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        switch ($serviceName) {
            case 'ProvideConsent':
                return $diContainer->getConsentProvider();
                break;
            case 'ProcessConsent':
                return $diContainer->getConsentProcessor();
                break;
        }
    }

    private function isMovedService($serviceName)
    {
        return in_array($serviceName, $this->movedServices);
    }
}
