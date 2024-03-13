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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use Exception;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockStepupGateway;
use SAML2\Constants;
use SAML2\HTTPRedirect;
use SAML2\Response as SamlResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig_Environment;

class StepupMockController extends Controller
{
    /**
     * @var MockStepupGateway
     */
    private $mockStepupGateway;
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(MockStepupGateway $mockStepupGateway, Twig_Environment $twig)
    {
        $this->mockStepupGateway = $mockStepupGateway;
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @return string|Response
     */
    public function ssoAction(Request $request)
    {
        try {
            // Check binding
            if (!$request->isMethod(Request::METHOD_GET)) {
                throw new BadRequestHttpException(sprintf(
                    'Could not receive AuthnRequest from HTTP Request: expected a GET method, got %s',
                    $request->getMethod()
                ));
            }

            // Parse available responses
            $responses = $this->getAvailableResponses($request);

            $redirectBinding = new HTTPRedirect();
            $message = $redirectBinding->receive();

            // Present response
            $body = $this->twig->render(
                '@OpenConextEngineBlockFunctionalTesting/Sso/consumeAssertion.html.twig',
                [
                    'receivedAuthnRequest' => $message->toUnsignedXML()->ownerDocument->saveXml(),
                    'responses' => $responses,
                ]
            );

            return new Response($body);
        } catch (BadRequestHttpException $e) {
            return new Response($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    private function getAvailableResponses(Request $request)
    {
        $results = [];

        // Parse successfull loa3
        $samlResponse = $this->mockStepupGateway->handleSsoSuccess($request, $this->getFullRequestUri($request));
        $results['success'] = $this->getResponseData($request, $samlResponse);

        // Parse successfull loa3 with changed audience
        $samlResponse = $this->mockStepupGateway->handleSsoSuccess($request, $this->getFullRequestUri($request), true);
        $results['success-audience'] = $this->getResponseData($request, $samlResponse);

        // Parse successfull loa2
        $samlResponse = $this->mockStepupGateway->handleSsoSuccessLoa2($request, $this->getFullRequestUri($request));
        $results['loa2'] = $this->getResponseData($request, $samlResponse);

        // Parse user cancelled
        $samlResponse = $this->mockStepupGateway->handleSsoFailure(
            $request,
            $this->getFullRequestUri($request),
            Constants::STATUS_RESPONDER,
            Constants::STATUS_AUTHN_FAILED,
            'Authentication cancelled by user'
        );
        $results['user-cancelled'] = $this->getResponseData($request, $samlResponse);

        // Parse unmet Loa
        $samlResponse = $this->mockStepupGateway->handleSsoFailure(
            $request,
            $this->getFullRequestUri($request),
            Constants::STATUS_RESPONDER,
            Constants::STATUS_NO_AUTHN_CONTEXT
        );
        $results['unmet-loa'] = $this->getResponseData($request, $samlResponse);

        // Parse unknown
        $samlResponse = $this->mockStepupGateway->handleSsoFailure(
            $request,
            $this->getFullRequestUri($request),
            Constants::STATUS_RESPONDER,
            Constants::STATUS_AUTHN_FAILED
        );
        $results['unknown'] = $this->getResponseData($request, $samlResponse);

        return $results;
    }

    /**
     * @param Request $request
     * @param SamlResponse $samlResponse
     * @return array
     */
    private function getResponseData(Request $request, SamlResponse $samlResponse)
    {
        $rawResponse = $this->mockStepupGateway->parsePostResponse($samlResponse);

        return [
            'acu' => $samlResponse->getDestination(),
            'rawResponse' => $rawResponse,
            'encodedResponse' => base64_encode($rawResponse),
            'relayState' => $request->request->get(MockStepupGateway::PARAMETER_RELAY_STATE),
        ];
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getFullRequestUri(Request $request)
    {
        return $request->getSchemeAndHttpHost() . $request->getBasePath() . $request->getRequestUri();
    }
}
