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

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\EngineBlockBundle\Metadata\Service\MetadataService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MetadataController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        MetadataService $metadataService,
        RouterInterface $router
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->metadataService = $metadataService;
        $this->router = $router;
    }

    /**
     * @param null|string $keyId
     * @return RedirectResponse|Response
     */
    public function idpMetadataAction($keyId = null)
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $metadataXml = $this->metadataService->metadataForIdp(
            $this->getEntityId('metadata_idp'),
            $this->getEntityId('authentication_idp_sso'),
            $keyId
        );

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param string $keyId
     * @return Response
     */
    public function spMetadataAction(string $keyId)
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $metadataXml = $this->metadataService->metadataForSp(
            $this->getEntityId('metadata_sp'),
            $this->getEntityId('authentication_sp_consume_assertion'),
            $keyId
        );

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param null|string $keyId
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function allIdpsMetadataAction(Request $request, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idPsMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param string $route
     * @return string
     */
    private function getEntityId(string $route)
    {
        return $this->router->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
