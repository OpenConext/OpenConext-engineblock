<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_ProxyServer;
use EngineBlock_View;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Mimics the previous methodology, will be refactored
 *  see https://www.pivotaltracker.com/story/show/107565968
 */
class FeedbackController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig,
        LoggerInterface $logger
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
        $this->logger = $logger;

        // we have to start the old session in order to be able to retrieve the feedback info
        $server = new EngineBlock_Corto_ProxyServer($twig);
        $server->startSession();
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unableToReceiveMessageAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/UnableToReceiveMessage.phtml'),
            400
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function voMembershipRequiredAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/Vomembershiprequired.phtml'),
            403
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function sessionLostAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/Feedback/SessionLost.phtml'), 400);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownIssuerAction(Request $request)
    {
        $viewData = [
            'entity-id' => $request->get('entity-id'),
            'destination' => $request->get('destination')
        ];

        $body = $this->engineBlockView->setData($viewData)->render('Authentication/View/Feedback/UnknownIssuer.phtml');

        return new Response($body, 404);
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function noIdpsAction()
    {
        // @todo Send 4xx or 5xx header?

        return new Response($this->engineBlockView->render('Authentication/View/Feedback/NoIdps.phtml'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function invalidAcsLocationAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/InvalidAcsLocation.phtml'),
            400
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unsupportedSignatureMethodAction(Request $request)
    {
        return new Response(
            $this->engineBlockView
                ->setData([
                    'signature-method' => $request->get('signature-method')
                ])
                ->render('Authentication/View/Feedback/UnsupportedSignatureMethod.phtml'),
            400
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownServiceProviderAction(Request $request)
    {
        $viewData = ['entity-id' => $request->get('entity-id')];

        $body = $this->engineBlockView
            ->setData($viewData)
            ->render('Authentication/View/Feedback/UnknownServiceProvider.phtml');

        return new Response($body, 400);
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function missingRequiredFieldsAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/MissingRequiredFields.phtml'),
            400
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function customAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/Feedback/Custom.phtml'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function invalidAcsBindingAction()
    {
        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
        return new Response($this->engineBlockView->render('Authentication/View/Feedback/InvalidAcsBinding.phtml'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function receivedErrorStatusCodeAction()
    {
        // @todo Send 4xx or 5xx header?
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/ReceivedErrorStatusCode.phtml')
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function signatureVerificationFailedAction()
    {
        // @todo Send 4xx or 5xx header?
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/ReceivedInvalidSignedResponse.phtml')
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function receivedInvalidResponseAction()
    {
        // @todo Send 4xx or 5xx header?
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/ReceivedInvalidResponse.phtml')
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function noConsentAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/Feedback/NoConsent.phtml'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownServiceAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/UnknownService.phtml'),
            400
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function authorizationPolicyViolationAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/AuthorizationPolicyViolation.phtml'),
            400
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownPreselectedIdpAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/UnknownPreselectedIdp.phtml'),
            400
        );
    }

    /**
     * @return Response
     */
    public function stuckInAuthenticationLoopAction()
    {
        return new Response(
            $this->engineBlockView->render('Authentication/View/Feedback/StuckInAuthenticationLoop.phtml'),
            400
        );
    }
}
