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

use EngineBlock_Corto_ProxyServer;
use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Mimics the previous methodology, will be refactored
 *  see https://www.pivotaltracker.com/story/show/107565968
 * @SuppressWarnings(PMD.TooManyMethods)
 */
class FeedbackController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TranslatorInterface $translator,
        Twig_Environment $twig,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
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
            $this->twig->render('@theme/Authentication/View/Feedback/unable-to-receive-message.html.twig'),
            400
        );
    }

    public function unknownErrorAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/unknown-error.html.twig'),
            500
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function sessionLostAction()
    {
        return new Response($this->twig->render('@theme/Authentication/View/Feedback/session-lost.html.twig'), 400);
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function sessionNotStartedAction()
    {
        return new Response($this->twig->render('@theme/Authentication/View/Feedback/session-not-started.html.twig'), 400);
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function noIdpsAction()
    {
        // @todo Send 4xx or 5xx header?

        return new Response($this->twig->render('@theme/Authentication/View/Feedback/no-idps.html.twig'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function invalidAcsLocationAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/invalid-acs-location.html.twig'),
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
            $this->twig
                ->render(
                    '@theme/Authentication/View/Feedback/unsupported-signature-method.html.twig',
                    [
                        'signatureMethod' => $request->get('signature-method')
                    ]
                ),
            400
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unsupportedAcsLocationSchemeAction()
    {
        return new Response(
            $this->twig
                ->render(
                    '@theme/Authentication/View/Feedback/unsupported-acs-location-scheme.html.twig'
                ),
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
        $entityId = $request->get('entity-id');

        // Add feedback info from url
        $customFeedbackInfo['EntityID'] = $entityId;
        $this->setFeedbackInformationOnSession($request->getSession(), $customFeedbackInfo);

        $body = $this->twig->render(
            '@theme/Authentication/View/Feedback/unknown-service-provider.html.twig',
            [
                'entityId' => $entityId
            ]
        );

        return new Response($body, 400);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownIdentityProviderAction(Request $request)
    {
        // Add feedback info from url
        $customFeedbackInfo['EntityID'] = $request->get('entity-id');
        $customFeedbackInfo['Destination'] = $request->get('destination');

        $this->setFeedbackInformationOnSession($request->getSession(), $customFeedbackInfo);

        $body = $this->twig->render('@theme/Authentication/View/Feedback/unknown-identity-provider.html.twig');

        return new Response($body, 404);
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function missingRequiredFieldsAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/missing-required-fields.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function authnContextClassRefBlacklistedAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/authn-context-class-ref-blacklisted.html.twig'),
            403
        );
    }


    /**
     * @return Response
     */
    public function invalidMfAuthnContextClassRefAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/invalid-mfa-authn-context-class-ref.html.twig'),
            403
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function invalidAttributeValueAction(Request $request)
    {
        $feedbackInfo = $request->getSession()->get('feedbackInfo');

        $attributeName = $feedbackInfo['attributeName'];
        $attributeValue = $feedbackInfo['attributeValue'];

        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Feedback/invalid-attribute-value.html.twig',
                [
                    'attributeName' => $attributeName,
                    'attributeValue' => $attributeValue,
                ]
            ),
            403
        );
    }

    public function metadataEntityNotFoundAction(Request $request)
    {
        // The exception message is used on the error page. As mostly developers or other tech-savvy people will see
        // this message. The ExceptionListener is responsible for setting the message on the feedback_custom field.
        $session = $request->getSession();
        if ($session->has('feedback_custom')) {
            $message = $session->get('feedback_custom');
        } else {
            // This should never occur, when it does, this error page is called from outside the application context
            // or the exception that shows this page was triggered elsewhere in code without a message.
            $message = 'More elaborate error details could not be found..';
        }

        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Feedback/metadata-entity-not-found.html.twig',
                [
                    'message' => $message,
                ]
            ),
            404
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function customAction(Request $request)
    {
        $currentLocale = $this->translator->getLocale();

        $title = $this->translator->trans('error_generic');
        $description = $this->translator->trans('error_generic_desc');

        $session = $request->getSession();
        if ($session->has('feedback_custom')) {
            $feedbackCustom = $session->get('feedback_custom');
            if (isset($feedbackCustom['title'][$currentLocale])) {
                $title = $feedbackCustom['title'][$currentLocale];
            }

            if (isset($feedbackCustom['description'][$currentLocale])) {
                $description = $feedbackCustom['description'][$currentLocale];
            }
        }

        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Feedback/custom.html.twig',
                [
                    'title' => $title,
                    'description' => $description,
                ]
            )
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function invalidAcsBindingAction()
    {
        // @todo Send 4xx or 5xx header depending on invalid binding came from request or configured metadata
        return new Response($this->twig->render('@theme/Authentication/View/Feedback/invalid-acs-binding.html.twig'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function receivedErrorStatusCodeAction()
    {
        // @todo Send 4xx or 5xx header?
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/received-error-status-code.html.twig')
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
            $this->twig->render('@theme/Authentication/View/Feedback/received-invalid-signed-response.html.twig')
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
            $this->twig->render('@theme/Authentication/View/Feedback/received-invalid-response.html.twig')
        );
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function unknownRequesterIdInAuthnRequestAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/unknown-requesterid-in-authnrequest.html.twig'),
            400
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function authorizationPolicyViolationAction(Request $request)
    {
        $locale = $this->translator->getLocale();
        $logo = null;
        $policyDecisionMessage = null;

        $session = $request->getSession();
        if ($session->has('error_authorization_policy_decision')) {
            /** @var PolicyDecision $policyDecision */
            $policyDecision = $session->get('error_authorization_policy_decision');

            if ($policyDecision->hasLocalizedDenyMessage()) {
                $policyDecisionMessage = $policyDecision->getLocalizedDenyMessage($locale, 'en');
            } elseif ($policyDecision->hasStatusMessage()) {
                $policyDecisionMessage = $policyDecision->getStatusMessage();
            }
            $logo = $policyDecision->getIdpLogo();
        }


        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Feedback/authorization-policy-violation.html.twig',
                [
                    'logo' => $logo,
                    'policyDecisionMessage' => $policyDecisionMessage,
                ]
            ),
            400
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function unknownPreselectedIdpAction(Request $request)
    {
        // Add feedback info from url
        $customFeedbackInfo['Idp Hash'] = $request->get('idp-hash');
        $this->setFeedbackInformationOnSession($request->getSession(), $customFeedbackInfo);

        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/unknown-preselected-idp.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function stuckInAuthenticationLoopAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/stuck-in-authentication-loop.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function noAuthenticationRequestReceivedAction(Request $request)
    {
        // The exception message is used on the error page. As mostly developers or other tech-savvy people will see
        // this message. The ExceptionListener is responsible for setting the message on the feedback_custom field.
        $session = $request->getSession();
        if ($session->has('feedback_custom')) {
            $message = $session->get('feedback_custom');
        } else {
            // This should never occur, when it does, this error page is called from outside the application context
            // or the exception that shows this page was triggered elsewhere in code without a message.
            $message = 'More elaborate error details could not be found..';
        }

        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Feedback/no-authentication-request-received.html.twig',
                [
                    'message' => $message,
                ]
            ),
            400
        );
    }

    /**
     * @return Response
     */
    public function clockIssueAction()
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/clock-issue.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function stepupCalloutUserCancelledAction(Request $request)
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/stepup-callout-user-cancelled.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function stepupCalloutUnmetLoaAction(Request $request)
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/stepup-callout-unmet-loa.html.twig'),
            400
        );
    }

    /**
     * @return Response
     */
    public function stepupCalloutUnknownAction(Request $request)
    {
        return new Response(
            $this->twig->render('@theme/Authentication/View/Feedback/stepup-callout-unknown.html.twig'),
            400
        );
    }

    /**
     * @param SessionInterface $session
     * @param array $customFeedbackInfo
     */
    private function setFeedbackInformationOnSession(SessionInterface $session, array $customFeedbackInfo)
    {
        $feedbackInfo = $session->get('feedbackInfo', []);
        $session->set('feedbackInfo', array_merge($customFeedbackInfo, $feedbackInfo));
    }
}
