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
use OpenConext\EngineBlockBundle\Metadata\Service\MetadataServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetadataController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var MetadataServiceInterface
     */
    private $spMetadataService;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        MetadataServiceInterface $spMetadataService
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->spMetadataService = $spMetadataService;
    }

    /**
     * @param null|string $keyId
     * @return RedirectResponse|Response
     */
    public function idpMetadataAction($keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idPMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
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

        $metadataXml = $this->spMetadataService->metadataFor(
            'https://engine.vm.openconext.org/authentication/sp/metadata',
            $keyId
        );

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param null|string $keyId
     * @param Request     $request
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
     * @param null|string $keyId
     * @param Request     $request
     * @return Response
     */
    public function edugainMetadataAction(Request $request, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->edugainMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
