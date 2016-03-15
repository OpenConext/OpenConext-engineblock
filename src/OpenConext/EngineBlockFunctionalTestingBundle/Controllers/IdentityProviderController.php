<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
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
     * @throws \RuntimeException
     */
    public function singleSignOnAction(Request $request, $idpName)
    {
        if ($request->isMethod('GET')) {
            $redirectBinding = new \SAML2_HTTPRedirect();
            $message = $redirectBinding->receive();
        } elseif ($request->isMethod('POST')) {
            $postBinding = new \SAML2_HTTPPost();
            $message = $postBinding->receive();
        } else {
            throw new \RuntimeException('Unsupported HTTP method');
        }

        if (!$message instanceof \SAML2_AuthnRequest) {
            throw new \RuntimeException('Unknown message type: ' . get_class($message));
        }
        $authnRequest = $message;

        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        /** @var ResponseFactory $responseFactory */
        $response = $this->responseFactory->createForEntityWithRequest($mockIdp, $authnRequest);

        $destination = ($mockIdp->hasDestinationOverride() ?
            $mockIdp->getDestinationOverride() :
            ($authnRequest->getAssertionConsumerServiceURL() ?
                $authnRequest->getAssertionConsumerServiceURL() :
                $response->getDestination()));

        if ($mockIdp->mustUseHttpRedirect()) {
            $redirect = new \SAML2_HTTPRedirect();
            $redirect->setDestination($destination);
            $url = $redirect->getRedirectURL($response);
            return new RedirectResponse($url);
        }

        /** @var Container $container */
        $container = \SAML2_Utils::getContainer();
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
