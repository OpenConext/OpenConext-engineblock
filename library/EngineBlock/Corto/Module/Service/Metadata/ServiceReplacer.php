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
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Service;

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
     * @param AbstractConfigurationEntity $proxyEntity
     * @param string $serviceName
     * @param bool $required (use either REQUIRED or OPTIONAL const)
     */
    public function __construct(AbstractConfigurationEntity $proxyEntity, $serviceName, $required)
    {
        $this->serviceName = $serviceName;
        $this->supportedBindings = $this->getSupportedBindingsFromProxy($proxyEntity, $required);
    }

    /**
     * @param AbstractConfigurationEntity $entity
     * @param $location
     */
    public function replace(AbstractConfigurationEntity $entity, $location)
    {
        $serviceName = lcfirst($this->serviceName . 's');

        $entity->$serviceName = array();
        if (empty($this->supportedBindings)) {
            return;
        }

        foreach($this->supportedBindings as $binding) {
            $entity->{$serviceName}[] = new Service($location, $binding);
        }
    }

    /**
     * Builds a list of services supported by the proxy
     *
     * @param AbstractConfigurationEntity $proxyEntity
     * @param bool $required
     * @return array
     * @throws Exception
     */
    private function getSupportedBindingsFromProxy(AbstractConfigurationEntity $proxyEntity, $required)
    {
        $serviceName = lcfirst($this->serviceName . 's');

        if (!isset($proxyEntity->$serviceName)) {
            if ($required == self::OPTIONAL) {
                return;
            }

            throw new EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception(
                "No service '$serviceName' is configured in EngineBlock metadata"
            );
        }

        $services = $proxyEntity->$serviceName;
        if (!is_array($services)) {
            throw new EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception(
                "Service '$this->serviceName' in EngineBlock metadata is not an array"
            );
        }

        $supportedBindings = $this->parseBindingsFromServices($services);

        if (count($supportedBindings) === 0 && $required == self::REQUIRED) {
            throw new EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception(
                "No '$serviceName' service bindings configured in EngineBlock metadata"
            );
        }

        return $supportedBindings;
    }

    /**
     * @param Service[] $services
     * @return string[]
     * @throws EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     */
    private function parseBindingsFromServices(array $services)
    {
        $supportedBindings = array();
        foreach($services as $serviceInfo) {
            if (!isset($serviceInfo->binding)) {
                throw new EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception(
                    "Service '$this->serviceName' configured without a Binding in EngineBlock metadata"
                );
            }

            $binding = $serviceInfo->binding;
            if (!in_array($binding, $this->knownBindings)) {
                throw new EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception(
                    "Service '$this->serviceName' has an invalid binding '$binding' configured in EngineBlock metadata"
                );
            }

            $supportedBindings[] = $binding;
        }

        return $supportedBindings;
    }
}
