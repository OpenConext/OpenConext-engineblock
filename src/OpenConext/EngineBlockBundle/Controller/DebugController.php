<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DebugController implements AuthenticationLoopThrottlingController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        SessionInterface $session
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->session = $session;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function debugSpConnectionAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        $proxyServer->debugSingleSignOn();

        // Authentication state needs to be registered here as the debug flow differs from the regular flow,
        // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
        $authenticationState = $this->session->get('authentication_state');

        $requestId = '_00000000-0000-0000-0000-000000000000';
        $authenticationState->startAuthenticationOnBehalfOf(
            $requestId,
            new Entity(new EntityId('debug_sp'), EntityType::SP())
        );

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
