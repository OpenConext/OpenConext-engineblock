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

        $authenticationState = $this->session->get('authentication_state');
        $authenticationState->startAuthenticationOnBehalfOf(new Entity(new EntityId('debug_sp'), EntityType::SP()));

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
