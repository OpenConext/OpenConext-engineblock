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
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\RequestAccessMailer;
use OpenConext\EngineBlock\Validator\RequestValidator;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig_Environment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Due to the compatibility requirements
 */
class IdentityProviderController implements AuthenticationLoopThrottlingController
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

    /**
     * @var RequestAccessMailer
     */
    private $requestAccessMailer;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $authenticationStateHelper;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var RequestValidator
     */
    private $unsolicitedRequestValidator;

    /**
     * @var RequestValidator
     */
    private $bindingValidator;

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig,
        LoggerInterface $loggerInterface,
        RequestAccessMailer $requestAccessMailer,
        RequestValidator $requestValidator,
        RequestValidator $bindingValidator,
        RequestValidator $unsolicitedRequestValidator,
        AuthenticationStateHelperInterface $authenticationStateHelper,
        FeatureConfigurationInterface $featureConfiguration
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
        $this->logger = $loggerInterface;
        $this->requestAccessMailer = $requestAccessMailer;
        $this->requestValidator = $requestValidator;
        $this->bindingValidator = $bindingValidator;
        $this->unsolicitedRequestValidator = $unsolicitedRequestValidator;
        $this->authenticationStateHelper = $authenticationStateHelper;
        $this->featureConfiguration = $featureConfiguration;
    }

    /**
     * The SSO action
     *
     *  Currently supported request method / binding combinations for SSO are:
     *
     *  | SAML Binding     | Request method | Parameter name |
     *  | ---------------- | -------------- | -------------- |
     *  | HttpRedirect     | GET            | SAMLRequest    |
     *  | HTTPPost         | POST           | SAMLRequest    |
     *
     * @param Request $request
     * @param null|string $keyId
     * @param null|string $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function singleSignOnAction(Request $request, $keyId = null, $idpHash = null)
    {
        $this->requestValidator->isValid($request);
        $this->bindingValidator->isValid($request);

        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->singleSignOn($idpHash);

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param Request $request
     * @param null|string $keyId
     * @param null|string $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException If the IdP-initiated flow has been disabled by config
     */
    public function unsolicitedSingleSignOnAction(Request $request, $keyId = null, $idpHash = null)
    {
        if (!$this->featureConfiguration->isEnabled('eb.feature_enable_idp_initiated_flow')) {
            throw new NotFoundHttpException();
        }

        $this->unsolicitedRequestValidator->isValid($request);

        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->unsolicitedSingleSignOn($idpHash);

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function processConsentAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function requestAccessAction(Request $request)
    {
        $body = $this->twig->render(
            '@theme/Authentication/View/IdentityProvider/request-access.html.twig',
            ['queryParameters' => $request->query->all()]
        );

        return new Response($body);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function performRequestAccessAction(Request $request)
    {
        $invalid = $this->validateRequest($request);

        if (count($invalid)) {
            $viewData = [];
            foreach ($invalid as $name) {
                $viewData[$name . 'Error'] = true;
            }

            $viewData['queryParameters'] = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $body = $this->twig->render(
                '@theme/Authentication/View/IdentityProvider/request-access.html.twig',
                $viewData
            );

            return new Response($body, 400);
        }

        $postedVariables = $request->request;
        if ($postedVariables->get('idpEntityId', false) !== false) {
            $this->requestAccessMailer->sendRequestAccessEmailForIdp(
                $postedVariables->get('spName'),
                $postedVariables->get('spEntityId'),
                $postedVariables->get('institution'),
                $postedVariables->get('idpEntityId'),
                $postedVariables->get('name'),
                $postedVariables->get('email'),
                $postedVariables->get('comment')
            );
        } else {
            $this->requestAccessMailer->sendRequestAccessEmailForInstitution(
                $postedVariables->get('spName'),
                $postedVariables->get('spEntityId'),
                $postedVariables->get('institution'),
                $postedVariables->get('name'),
                $postedVariables->get('email'),
                $postedVariables->get('comment')
            );
        }

        return new Response(
            $this->twig->render('@theme/Authentication/View/IdentityProvider/perform-request-access.html.twig')
        );
    }

    /**
     * Rudimentary validation, ported from
     * https://github.com/OpenConext/OpenConext-engineblock/blob/b1ee14b96fff6a0dc203ad3c8a707a8661e9a402/
     *                      application/modules/Authentication/Controller/IdentityProvider.php#L246
     *
     * @param Request $request
     * @return array
     */
    private function validateRequest(Request $request)
    {
        $invalid = [];
        foreach ($request->request->all() as $key => $value) {
            if (empty($value)) {
                $invalid[] = $key;

                continue;
            }

            if ($key === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $invalid[] = $key;
            }
        }

        return $invalid;
    }
}
