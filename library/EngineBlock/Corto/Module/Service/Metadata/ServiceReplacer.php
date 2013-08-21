<?php
/**
 * Replaces services like SingleSignOn and SingleLogout with services of EngineBlock
 * 
 * It works as follows
 * 
 * - Entity has no service configured, Proxy has no service configured -> Service not configured
 * - Entity has service configured, Proxy has no service configured -> Service will be removed
 * - Entity has service configured, Proxy has no service configured and not optional -> Error
 * - Entity has service configured, Proxy has service configured -> Service replaced by proxy configuration
 * - Entity has no service configured, Proxy has service configured -> Service replaced by proxy configuration
 */

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception as Exception;

class EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer
{
    const REQUIRED = true;
    const OPTIONAL = false;

    private $serviceName;
    
    /**
     * @var array
     */
    private $knownBindings = array(
        EngineBlock_Corto_Module_Services::BINDING_TYPE_HTTP_REDIRECT,
        EngineBlock_Corto_Module_Services::BINDING_TYPE_HTTP_POST
    );

    /**
     * @var array
     */
    private $supportedBindings;

    /**
     * @param array $proxyEntity
     * @param string $serviceName
     * @param bool $required (use either REQUIRED or OPTIONAL const)
     */
    public function __construct(array $proxyEntity, $serviceName, $required)
    {
        $this->serviceName = $serviceName;
        $this->supportedBindings = $this->getSupportedBindingsFromProxy($proxyEntity, $required);
    }

    /**
     * @param array &$entity
     * @param string $location
     */
    public function replace(array &$entity, $location)
    {
        $entity[$this->serviceName] = array();
        if (empty($this->supportedBindings)) {
            return;
        }

        foreach($this->supportedBindings as $binding) {
            $entity[$this->serviceName][] = array(
                'Location'=> $location,
                'Binding' => $binding
            );
        }
    }

    /**
     * Builds a list of services supported by the proxy
     *
     * @param array $proxyEntity
     * @param bool $required
     * @return array
     * @throws Exception
     */
    private function getSupportedBindingsFromProxy(array $proxyEntity, $required)
    {
        if (!isset($proxyEntity[$this->serviceName])) {
            if ($required == self::OPTIONAL) {
                return;
            }

            throw new Exception("'No service '$this->serviceName' is configured in EngineBlock metadata");
        }

        $services = $proxyEntity[$this->serviceName];
        if (!is_array($services)) {
            throw new Exception("Service '$this->serviceName' in EngineBlock metadata is not an array");
        }

        $supportedBindings = $this->parseBindingsFromServices($services);

        if (count($supportedBindings) === 0 && $required == self::REQUIRED) {
            throw new Exception("No '$this->serviceName' service bindings configured in EngineBlock metadata");
        }

        return $supportedBindings;
    }

    /**
     * @param array $services
     * @return array
     * @throws EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     */
    private function parseBindingsFromServices(array $services)
    {
        $supportedBindings = array();
        foreach($services as $serviceInfo) {
            if (!isset($serviceInfo['Binding'])) {
                throw new Exception("Service '$this->serviceName' configured without a Binding in EngineBlock metadata");
            }

            $binding = $serviceInfo['Binding'];
            if (!in_array($binding, $this->knownBindings)) {
                throw new Exception("Service '$this->serviceName' has an invalid binding '$binding' configured in EngineBlock metadata");
            }

            $supportedBindings[] = $binding;
        }

        return $supportedBindings;
    }
}