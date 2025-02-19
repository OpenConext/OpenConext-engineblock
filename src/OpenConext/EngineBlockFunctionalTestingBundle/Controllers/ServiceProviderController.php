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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use DOMDocument;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\AuthnRequestFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat\Container;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use SAML2\HTTPPost;
use SAML2\HTTPRedirect;
use SAML2\Response as SAMLResponse;
use SAML2\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Controllers
 * @SuppressWarnings("PMD")
 */
class ServiceProviderController extends AbstractController
{
    /**
     * @var EntityRegistry
     */
    private $mockSpRegistry;

    /**
     * @var EngineBlock
     */
    private $engineBlock;

    public function __construct(EntityRegistry $spRegistry, EngineBlock $engineBlock)
    {
        $this->mockSpRegistry = $spRegistry;
        $this->engineBlock = $engineBlock;
    }

    /**
     * @param $spName
     * @return RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function triggerLoginRedirectAction($spName)
    {
        if (!$this->mockSpRegistry->has($spName)) {
            throw new BadRequestHttpException(sprintf('No SP found for "%s"', $spName));
        }

        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $factory = new AuthnRequestFactory();
        $authnRequest = $factory->createForRequestFromTo(
            $sp,
            $this->engineBlock
        );

        $redirect = new HTTPRedirect();
        $url = $redirect->getRedirectURL($authnRequest);

        if (isset($sp->getEntityDescriptor()->getExtensions()['Malformed'])) {
            $url = str_replace('SAMLRequest', 'AuthNRequest', $url);
        }

        return new RedirectResponse($url);
    }

    /**
     * @param $spName
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function triggerLoginPostAction($spName)
    {
        if (!$this->mockSpRegistry->has($spName)) {
            throw new BadRequestHttpException(sprintf('No SP found for "%s"', $spName));
        }

        $factory = new AuthnRequestFactory();
        $sp = $this->mockSpRegistry->get($spName);
        $authnRequest = $factory->createForRequestFromTo(
            $sp,
            $this->engineBlock
        );

        $redirect = new HTTPPost();
        $redirect->send($authnRequest);

        /** @var Container $container */
        $container = Utils::getContainer();
        $response = $container->getPostResponse();

        if (isset($sp->getEntityDescriptor()->getExtensions()['Malformed'])) {
            $body = $response->getContent();
            $response->setContent(str_replace('SAMLRequest', 'AuthNRequest', $body));
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     */
    public function assertionConsumerAction(Request $request)
    {
        $previous = libxml_disable_entity_loader(true);
        try {
            $httpPostBinding = new HTTPPost();
            $message = $httpPostBinding->receive();
        } catch (\Exception $e1) {
            try {
                $httpRedirectBinding = new HTTPRedirect();
                $message = $httpRedirectBinding->receive();
            } catch (\Exception $e2) {
                throw new \RuntimeException('Unable to retrieve SAML message?', 1, $e1);
            }
        }

        if (!$message instanceof SAMLResponse) {
            throw new \RuntimeException(sprintf('Unrecognized message type received: "%s"', get_class($message)));
        }

        $xml = base64_decode($request->get('SAMLResponse'));

        // Format the XML
        $doc = new DomDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $xml = $doc->saveXML();

        libxml_disable_entity_loader($previous);

        return new Response(
            $xml,
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * @param $spName
     * @return Response
     */
    public function metadataAction($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        return new Response(
            $mockSp->getEntityDescriptor()->toXML()->ownerDocument->saveXML(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }
}
