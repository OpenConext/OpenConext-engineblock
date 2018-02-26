<?php

/**
 * Copyright 2015 SURFnet bv
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
use EngineBlock_View;
use Exception;
use OpenConext\EngineBlock\Service\RequestAccessMailer;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * @var EngineBlock_View
     */
    private $engineBlockView;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestAccessMailer
     */
    private $requestAccessMailer;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        LoggerInterface $loggerInterface,
        RequestAccessMailer $requestAccessMailer,
        Session $session
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
        $this->logger                          = $loggerInterface;
        $this->requestAccessMailer             = $requestAccessMailer;
        $this->session                         = $session;
    }

    /**
     * @param null|string $keyId
     * @param null|string $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function singleSignOnAction($keyId = null, $idpHash = null)
    {
        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->singleSignOn($idpHash);

        $spEntityId      = EngineBlock_ApplicationSingleton::getInstance()->authenticationStateSpEntityId;
        $serviceProvider = new Entity(new EntityId($spEntityId), EntityType::SP());

        $authenticationState = $this->session->get('authentication_state');
        $authenticationState->startAuthenticationOnBehalfOf($serviceProvider);

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param null|string $keyId
     * @param null|string $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function unsolicitedSingleSignOnAction($keyId = null, $idpHash = null)
    {
        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->unsolicitedSingleSignOn($idpHash);

        $spEntityId      = EngineBlock_ApplicationSingleton::getInstance()->authenticationStateSpEntityId;
        $serviceProvider = new Entity(new EntityId($spEntityId), EntityType::SP());

        $authenticationState = $this->session->get('authentication_state');
        $authenticationState->startAuthenticationOnBehalfOf($serviceProvider);

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
        $body = $this->engineBlockView
            ->setData([
                'queryParameters' => $request->query->all()
            ])
            ->render('Authentication/View/IdentityProvider/RequestAccess.phtml');

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

            $body = $this->engineBlockView
                ->setData($viewData)
                ->render('Authentication/View/IdentityProvider/RequestAccess.phtml');

            return new Response($body);
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
            $this->engineBlockView->render('Authentication/View/IdentityProvider/PerformRequestAccess.phtml')
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
