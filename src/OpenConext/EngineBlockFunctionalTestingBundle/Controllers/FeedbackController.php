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

use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Controllers
 * @SuppressWarnings("PMD")
 */
class FeedbackController extends AbstractController
{

    private Environment $twig;

    private FeedbackStateHelperInterface $feedbackStateHelper;

    public function __construct(
        Environment $twig,
        FeedbackStateHelperInterface $feedbackStateHelper
    ) {
        $this->twig = $twig;
        $this->feedbackStateHelper = $feedbackStateHelper;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function feedbackAction(Request $request)
    {
        $key = $this->getTemplate($request);
        $feedbackInfo = $this->getFeedbackInfo($request);
        $parameters = $this->getTemplateParameters($request);

        $template = sprintf(
            '@theme/Authentication/View/Feedback/%s.html.twig',
            $key
        );

        $this->feedbackStateHelper->storeFeedbackInfo($feedbackInfo);

        return new Response($this->twig->render($template, $parameters), 200);
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getTemplate(Request $request)
    {
        $key = $request->query->getString('template');
        if (!$key) {
            $key = 'session-lost';
        }

        return $key;
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getFeedbackInfo(Request $request)
    {
        $default = '{
            "requestId":"5cb4bd3879b49",
            "ipAddress":"192.168.66.98",
            "artCode":"31914"
        }';

        $feedbackInfo = $request->query->getString('feedback-info', $default);

        $feedbackInfo = json_decode($feedbackInfo, true);
        if (!empty($feedbackInfo['IdentityProvider']) || !empty($feedbackInfo['IdP'])) {
            $feedbackInfo['identityProviderName'] = 'OpenConext Identities Inc';
        }

        return $feedbackInfo;
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getTemplateParameters(Request $request)
    {
        $default = '{}';

        $parameters = $request->query->getString('parameters', $default);

        $parameters = json_decode($parameters, true);

        return $parameters;
    }
}
