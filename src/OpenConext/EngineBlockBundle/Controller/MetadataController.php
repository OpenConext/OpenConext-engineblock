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
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Xml\MetadataProvider;
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
     * @var MetadataProvider
     */
    private $metadataService;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        MetadataProvider $metadataService,
        RouterInterface $router
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->metadataService = $metadataService;
        $this->router = $router;
    }

    public function idpMetadataAction(string $keyId = null): Response
    {
        $metadataXml = $this->metadataService->metadataForIdp($keyId);

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function spMetadataAction(string $keyId): Response
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $metadataXml = $this->metadataService->metadataForSp($keyId);

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function allIdpsMetadataAction(Request $request, string $keyId = null): Response
    {
        $spEntityId = $request->query->get('sp-entity-id', null);

        $metadataXml = $this->metadataService->metadataForIdps($spEntityId, $keyId);

        // 6. Return the signed metadata as an XML response
        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function stepupMetadataAction(string $keyId = null): Response
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $metadataXml = $this->metadataService->metadataForStepup($keyId);

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function signingCertificateAction(string $keyId = null): Response
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $cert = $this->metadataService->certificate($keyId);
        $response = new Response($cert);
        $response->headers->set('Content-Type', 'application/x-pem-file');

        return $response;
    }
}
