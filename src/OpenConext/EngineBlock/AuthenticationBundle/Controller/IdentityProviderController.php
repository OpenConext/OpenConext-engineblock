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

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_View;
use Exception;
use OpenConext\EngineBlock\AuthenticationBundle\Service\RequestAccessMailer;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Due to the compatibility requirements
 */
class IdentityProviderController
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

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        LoggerInterface $loggerInterface,
        RequestAccessMailer $requestAccessMailer
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
        $this->logger                          = $loggerInterface;
        $this->requestAccessMailer = $requestAccessMailer;
    }

    /**
     * @param null|string $virtualOrganization
     * @param null|string $keyId
     * @param null|string $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function singleSignOnAction($virtualOrganization = null, $keyId = null, $idpHash = null)
    {
        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization !== null) {
            $cortoAdapter->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->singleSignOn($idpHash);

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param null $virtualOrganization
     * @param null $keyId
     * @param null $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function unsolicitedSingleSignOnAction($virtualOrganization = null, $keyId = null, $idpHash = null)
    {
        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization !== null) {
            $cortoAdapter->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        $cortoAdapter->singleSignOn($idpHash);

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
            ->setData($request->query->all())
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
            $viewData = array();
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
        if ($postedVariables->get('institution', false) !== false) {
            $this->requestAccessMailer->sendRequestAccessToInstitutionEmail(
                $postedVariables->get('spEntityId'),
                $postedVariables->get('name'),
                $postedVariables->get('email'),
                $postedVariables->get('institution'),
                $postedVariables->get('comment')
            );
        } else {
            $this->requestAccessMailer->sendRequestAccessEmail(
                $postedVariables->get('idpEntityId'),
                $postedVariables->get('spEntityId'),
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
        $invalid = array();
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
