<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_View;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;
    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        LoggerInterface $logger
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView = $engineBlockView;
        $this->logger = $logger;
    }

    public function unableToReceiveMessageAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/UnableToReceiveMessage.phtml'),
            400
        );
    }

    public function voMembershipRequiredAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/Vomembershiprequired.phtml'),
            403
        );
    }

    public function sessionLostAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/Feedback/SessionLost.phtml'), 400);
    }

    public function unknownIssuerAction(Request $request)
    {
        $viewData = array(
            'entity-id' => $request->get('entity-id'),
            'destination' => $request->get('destination')
        );

        $body = $this->engineBlockView->setData($viewData)->render('Authentication/View/Feedback/UnknownIssuer.phtml');

        return new Response($body, 404);
    }

    public function noIdpsAction()
    {
        // from: https://github.com/OpenConext/OpenConext-engineblock/blob/b1ee14b96fff6a0dc203ad3c8a707a8661e9a402/
        //              application/modules/Authentication/Controller/Feedback.php#L76
        // @todo Send 4xx or 5xx header?

        return new Response($this->engineBlockView->render('Authentication/View/Feedback/NoIdps.phtml'));
    }

    public function invalidAcsLocationAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/InvalidAcsLocation.phtml'),
            400
        );
    }

    public function unknownServiceProviderAction(Request $request)
    {
        $viewData = array('entity-id' => $request->get('entity-id'));

        $body = $this->engineBlockView
            ->setData($viewData)
            ->render('Authentication/View/Feedback/UnknownServiceProvider.phtml');

        return new Response($body, 400);
    }
//
//    public function missingRequiredFieldsAction()
//    {
//        $this->_getResponse()->setStatus(400, 'Bad Request');
//    }
//
//    public function noConsentAction()
//    {
//
//    }
//
//    public function customAction()
//    {
//        $proxyServer = new EngineBlock_Corto_ProxyServer();
//        $proxyServer->startSession();
//    }

//
//    public function invalidAcsBindingAction()
//    {
//        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
//    }
//
//    public function receivedErrorStatusCodeAction()
//    {
//        // @todo Send 4xx or 5xx header?
//    }
//
//    public function receivedInvalidResponseAction()
//    {
//        // @todo Send 4xx or 5xx header?
//    }
//
//    public function receivedInvalidSignedResponseAction()
//    {
//        // @todo Send 4xx or 5xx header?
//    }
}
