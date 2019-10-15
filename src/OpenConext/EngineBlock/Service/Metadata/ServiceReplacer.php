<?php declare(strict_types=1);

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
namespace OpenConext\EngineBlock\Service\Metadata;

use OpenConext\EngineBlock\Exception\ServiceReplacingException;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Service;
use SAML2\Constants;

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
class ServiceReplacer
{
    const REQUIRED = true;
    const OPTIONAL = false;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var array
     */
    private $knownBindings = array(
        Constants::BINDING_HTTP_REDIRECT,
        Constants::BINDING_HTTP_POST,
    );

    /**
     * @var array
     */
    private $supportedBindings;

    /**
     * @param AbstractRole $proxyEntity
     * @param string $serviceName
     * @param bool $required (use either REQUIRED or OPTIONAL const)
     */
    public function __construct(AbstractRole $proxyEntity, string $serviceName, bool $required)
    {
        $this->serviceName = $serviceName;
        $this->supportedBindings = $this->getSupportedBindingsFromProxy($proxyEntity, $required);
    }

    public function replace(AbstractRole $entity, string $location): void
    {
        $serviceName = lcfirst($this->serviceName.'s');

        $entity->$serviceName = array();
        if (empty($this->supportedBindings)) {
            return;
        }

        foreach ($this->supportedBindings as $binding) {
            $entity->{$serviceName}[] = new Service($location, $binding);
        }
    }

    /**
     * Builds a list of services supported by the proxy
     * @throws ServiceReplacingException
     */
    private function getSupportedBindingsFromProxy(AbstractRole $proxyEntity, bool $required) : ?array
    {
        $serviceName = lcfirst($this->serviceName.'s');

        if (!isset($proxyEntity->$serviceName)) {
            if ($required == self::OPTIONAL) {
                return null;
            }

            throw new ServiceReplacingException(
                sprintf('No service "%s" is configured in EngineBlock metadata', $serviceName)
            );
        }

        $services = $proxyEntity->$serviceName;
        if (!is_array($services)) {
            throw new ServiceReplacingException(
                sprintf('Service "%s" in EngineBlock metadata is not an array', $this->serviceName)
            );
        }

        $supportedBindings = $this->parseBindingsFromServices($services);

        if (count($supportedBindings) === 0 && $required == self::REQUIRED) {
            throw new ServiceReplacingException(
                sprintf('No "%s" service bindings configured in EngineBlock metadata', $serviceName)
            );
        }

        return $supportedBindings;
    }

    /**
     * @param Service[] $services
     * @return string[]
     * @throws ServiceReplacingException
     */
    private function parseBindingsFromServices(array $services): array
    {
        $supportedBindings = array();
        foreach ($services as $serviceInfo) {
            if (!isset($serviceInfo->binding)) {
                throw new ServiceReplacingException(
                    sprintf('Service "%s" configured without a Binding in EngineBlock metadata', $this->serviceName)
                );
            }

            $binding = $serviceInfo->binding;
            if (!in_array($binding, $this->knownBindings)) {
                throw new ServiceReplacingException(
                    sprintf(
                        'Service "%s" has an invalid binding "%s" configured in EngineBlock metadata',
                        $this->serviceName,
                        $binding
                    )
                );
            }

            $supportedBindings[] = $binding;
        }

        return $supportedBindings;
    }
}
