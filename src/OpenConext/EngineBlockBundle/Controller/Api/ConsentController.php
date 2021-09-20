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

namespace OpenConext\EngineBlockBundle\Controller\Api;

use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Service\ConsentServiceInterface;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiInternalServerErrorHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function sprintf;

final class ConsentController
{
    /**
     * @var ConsentServiceInterface
     */
    private $consentService;

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FeatureConfigurationInterface $featureConfiguration,
        ConsentServiceInterface $consentService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->featureConfiguration = $featureConfiguration;
        $this->consentService       = $consentService;
    }

    public function userAction($userId, Request $request)
    {
        if (!$request->isMethod(Request::METHOD_GET)) {
            throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), [Request::METHOD_GET]);
        }

        if (!$this->featureConfiguration->isEnabled('api.consent_listing')) {
            throw new ApiNotFoundHttpException('Consent listing API is disabled');
        }

        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_PROFILE')) {
            throw new ApiAccessDeniedHttpException(
                'Access to the content listing API requires the role ROLE_API_USER_PROFILE'
            );
        }

        try {
            $consentList = $this->consentService->findAllFor($userId)->jsonSerialize();
        } catch (RuntimeException $e) {
            throw new ApiInternalServerErrorHttpException(
                sprintf(
                    'An unknown error occurred while fetching a list of services the user has given consent for to ' .
                    'release attributes to ("%s")',
                    $e->getMessage()
                ),
                $e
            );
        }

        return new JsonResponse($consentList, Response::HTTP_OK);
    }
}
