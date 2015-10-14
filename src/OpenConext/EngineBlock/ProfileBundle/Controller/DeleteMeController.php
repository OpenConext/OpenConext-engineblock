<?php

namespace OpenConext\EngineBlock\ProfileBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_User;
use EngineBlock_View;
use Surfnet_Zend_Auth_Adapter_Saml;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeleteMeController
{
    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(EngineBlock_View $engineBlockView, UrlGeneratorInterface $urlGenerator)
    {
        $this->engineBlockView = $engineBlockView;
        $this->urlGenerator = $urlGenerator;
    }

    public function indexAction()
    {
        $authenticationAdapter = new Surfnet_Zend_Auth_Adapter_Saml();
        $authenticationResult  = $authenticationAdapter->authenticate();

        $currentUser = new EngineBlock_User($authenticationResult->getIdentity());
        $currentUser->delete();

        return new RedirectResponse($this->urlGenerator->generate('profile_delete_user_success'));
    }

    public function successAction()
    {
        return new Response($this->engineBlockView->render('Profile/View/DeleteUser/Success.phtml'));
    }
}
