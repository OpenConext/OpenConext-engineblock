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

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use RuntimeException;
use SAML2\AuthnRequest;
use SAML2\HTTPPost;
use SAML2\HTTPRedirect;
use SAML2\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\ResponseFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Compat\Container;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) just does a lot of manual lifting :(
 */
class IdentityProviderController extends Controller
{
    /**
     * @var EntityRegistry
     */
    private $mockIdpRegistry;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(EntityRegistry $mockIdpRegistry, ResponseFactory $responseFactory)
    {
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param $idpName
     * @return Response
     */
    public function metadataAction($idpName)
    {
        $entityDescriptor = $this->mockIdpRegistry->get($idpName)->getEntityDescriptor();

        return new Response(
            $entityDescriptor->toXML()->ownerDocument->saveXML(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * @param Request $request
     * @param $idpName
     * @return Response
     * @throws RuntimeException
     */
    public function singleSignOnAction(Request $request, $idpName)
    {
        if ($request->isMethod('GET')) {
            $redirectBinding = new HTTPRedirect();
            $message = $redirectBinding->receive();
        } elseif ($request->isMethod('POST')) {
            $postBinding = new HTTPPost();
            $message = $postBinding->receive();
        } else {
            throw new RuntimeException('Unsupported HTTP method');
        }

        if (!$message instanceof AuthnRequest) {
            throw new RuntimeException(sprintf('Unknown message type: "%s"', get_class($message)));
        }
        $authnRequest = $message;

        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        /** @var ResponseFactory $responseFactory */
        $response = $this->responseFactory->createForEntityWithRequest($mockIdp, $authnRequest);

        $destination = ($authnRequest->getAssertionConsumerServiceURL() ?
                $authnRequest->getAssertionConsumerServiceURL() :
                $response->getDestination());

        /* set the destination element of the response to the ACS URL */
        $response->setDestination($destination);

        if ($mockIdp->mustUseHttpRedirect()) {
            $redirect = new HTTPRedirect();
            $redirect->setDestination($destination);
            $url = $redirect->getRedirectURL($response);
            return new RedirectResponse($url);
        }

        /** @var Container $container */
        $container = Utils::getContainer();
        $authnRequestXml = $container->getLastDebugMessageOfType(Container::DEBUG_TYPE_IN);
        $responseXml = $response->toXml();

        $container->postRedirect(
            $destination,
            [
                'authnRequestXml'=> htmlentities($authnRequestXml),
                'SAMLResponse' => base64_encode($responseXml),
            ]
        );
        return $container->getPostResponse();
    }
}
