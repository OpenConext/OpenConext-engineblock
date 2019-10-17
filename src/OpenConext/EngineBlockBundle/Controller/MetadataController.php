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
            $this->getAbsoluteUrlForRoute('metadata_idp'),
            $this->getAbsoluteUrlForRoute('authentication_idp_sso'),
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
            $this->getAbsoluteUrlForRoute('metadata_sp'),
            $this->getAbsoluteUrlForRoute('authentication_sp_consume_assertion'),
            $keyId
        );

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * Render the IdPs metadata
     *
     * The following steps are required to generate the IdPs metadata:
     * 1. Determine if the IdP metadata is requested for a specific SP based on the 'sp-entity-id' parameter.
     * 2. Load the EngineBlock IdP entity (used to override the certificates and contact persons)
     * 3. Load the IdPs (either based on 'allowedIdpEntityIds' of the specified IdP, or loading all.
     * 4. Replace the services of the IdP's (SSO and SLO)
     * 5. Render and sign the document
     * 6. Return the signed metadata as an XML response
     *
     * @param Request $request
     * @param null|string $keyId
     * @return RedirectResponse|Response
     */
    public function allIdpsMetadataAction(Request $request, $keyId = null)
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        // 1. Determine if the IdP metadata is requested for a specific SP based on the 'sp-entity-id' parameter.
        $spEntityId = $request->query->get('sp-entity-id', null);

        // 2..5
        $engineEntityId = $this->getAbsoluteUrlForRoute('metadata_idp');
        $ssoLocation = $this->getAbsoluteUrlForRoute('authentication_idp_sso');
        $metadataXml = $this->metadataService->metadataForIdps($engineEntityId, $ssoLocation, $spEntityId, $keyId);

        // 6. Return the signed metadata as an XML response
        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param null|string $keyId
     * @return Response
     */
    public function stepupMetadataAction($keyId = null)
    {
        if (empty($keyId)) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $metadataXml = $this->metadataService->metadataForStepup(
            $this->getAbsoluteUrlForRoute('authentication_stepup_consume_assertion'),
            $keyId
        );

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param string $route
     * @return string
     */
    private function getAbsoluteUrlForRoute(string $route)
    {
        return $this->router->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
