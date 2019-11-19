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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_ContinueToIdp implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var ServiceProviderFactory
     */
    private $_serviceProviderFactory;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Twig_Environment $twig,
        ServiceProviderFactory $serviceProviderFactory
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->twig = $twig;
        $this->_serviceProviderFactory = $serviceProviderFactory;
    }

    /**
     * Handle the forwarding of the user to the proper IdP0 after the WAYF screen.
     *
     * @param string $serviceName
     * @param Request $httpRequest
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     * @throws EngineBlock_Exception
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $selectedIdp = urldecode($_REQUEST['idp']);
        if (!$selectedIdp) {
            throw new EngineBlock_Corto_Module_Services_Exception(
                'No IdP selected after WAYF'
            );
        }

        // Retrieve the request from the session.
        $id      = $_POST['ID'];
        if (!$id) {
            throw new EngineBlock_Exception(
                'Missing ID for AuthnRequest after WAYF',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_server->getLogger());
        $request = $authnRequestRepository->findRequestById($id);

        if (!$request) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                'Session lost after WAYF'
            );
        }

        // Flush log if SP or IdP has additional logging enabled
        if ($request->isDebugRequest()) {
            $sp = $this->getEngineSpRole($this->_server);
        } else {
            $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        }
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($selectedIdp);
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->flushLog('Activated additional logging for the SP or IdP');

            $log = $application->getLogInstance();
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return ServiceProvider
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $keyId = $proxyServer->getKeyId();
        if (!$keyId) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $serviceProvider = $this->_serviceProviderFactory->createEngineBlockEntityFrom($keyId);
        return ServiceProvider::fromServiceProviderEntity($serviceProvider);
    }
}
