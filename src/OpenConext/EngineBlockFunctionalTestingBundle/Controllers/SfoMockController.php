<?php
/**
 * Copyright 2019 SURFnet B.V.
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
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockSfoGateway;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig_Environment;

class SfoMockController extends Controller
{
    const PARAMETER_REQUEST = 'SAMLRequest';
    const PARAMETER_RELAY_STATE = 'RelayState';

    /**
     * @var MockSfoGateway
     */
    private $mockSfoGateway;
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(MockSfoGateway $mockSfoGateway, Twig_Environment $twig)
    {
        $this->mockSfoGateway = $mockSfoGateway;
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
            if (!$request->isMethod(Request::METHOD_POST)) {
                throw new BadRequestHttpException(sprintf(
                    'Could not receive AuthnRequest from HTTP Request: expected a POST method, got %s',
                    $request->getMethod()
                ));
            }

            // Decode samlrequest
            $decodedSamlRequest = base64_decode($request->request->get(self::PARAMETER_REQUEST), true);

            // Parse request
            $samlResponse = $this->mockSfoGateway->handleSso($decodedSamlRequest, $this->getFullRequestUri($request));
            $rawResponse = $this->mockSfoGateway->parsePostResponse($samlResponse);
            $relayState = $request->request->get(self::PARAMETER_RELAY_STATE);

            // Encode response
            $encodedResponse = base64_encode($rawResponse);

            // Present response
            $body = $this->twig->render(
                '@OpenConextEngineBlockFunctionalTesting/Sso/consumeAssertion.html.twig',
                [
                    'acu' => $samlResponse->getDestination(),
                    'response' => $encodedResponse,
                    'relayState' => $relayState
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
     * @return string
     */
    private function getFullRequestUri(Request $request)
    {
        return $request->getSchemeAndHttpHost() . $request->getBasePath() . $request->getRequestUri();
    }
}
