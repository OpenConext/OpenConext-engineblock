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

    const DEFAULT_REQUEST_BINDING  = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const DEFAULT_RESPONSE_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    // @todo move this to translations?
    const META_TOU_COMMENT = 'Use of this metadata is subject to the Terms of Use at http://www.edugain.org/policy/metadata-tou_1_0.txt';

    const INTRODUCTION_EMAIL = 'introduction_email';

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
            /** @var $serviceName EngineBlock_Corto_Module_Service_Abstract */
            $service = $this->factoryService($className, $this->_server);
            $service->serve($serviceName);
            return;
        }

        throw new EngineBlock_Corto_Module_Services_Exception(
            "Unable to load service '$serviceName' (resolved to '$resolvedServiceName') tried className '$className'!"
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
            case 'EngineBlock_Corto_Module_Service_ProvideConsent' :
                return new EngineBlock_Corto_Module_Service_ProvideConsent(
                    $server,
                    $diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER],
                    $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY]
                );
            case 'EngineBlock_Corto_Module_Service_ProcessConsent' :
                $preferredNameAttributeFilter = new EngineBlock_User_PreferredNameAttributeFilter();
                return new EngineBlock_Corto_Module_Service_ProcessConsent(
                    $server,
                    $diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER],
                    $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY],
                    $diContainer[EngineBlock_Application_DiContainer::MAILER],
                    $preferredNameAttributeFilter
                );
            default :
                return new $className(
                    $server,
                    $diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER]
                );
        }
    }
}
