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

use InvalidArgumentException;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Exception\InvalidArgumentException as EngineBlockInvalidArgumentException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlock\Service\DeprovisionService;
use OpenConext\EngineBlockBundle\Http\Exception\ApiInternalServerErrorHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function sprintf;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class DeprovisionController
{
    /**
     * @var DeprovisionService
     */
    private $deprovisionService;

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    private $applicationName;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FeatureConfigurationInterface $featureConfiguration
     * @param DeprovisionService $deprovisionService
     * @param string $applicationName
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FeatureConfigurationInterface $featureConfiguration,
        DeprovisionService $deprovisionService,
        $applicationName
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->featureConfiguration = $featureConfiguration;
        $this->deprovisionService   = $deprovisionService;
        $this->applicationName      = $applicationName;
    }

    /**
     * @param Request $request
     * @param string $collabPersonId
     * @return JsonResponse
     */
    public function userDataAction(Request $request, $collabPersonId)
    {
        $this->assertRequestMethod($request, [Request::METHOD_GET, Request::METHOD_DELETE]);
        $this->assertDeprovisionApiIsEnabled();
        $this->assertUserHasDeprovisionRole();

        $id = $this->createCollabPersonIdValueObject($collabPersonId);

        $userData = $this->deprovisionService->read($id);

        if ($request->isMethod(Request::METHOD_DELETE)) {
            $this->deprovisionService->delete($id);
        }

        return $this->createResponse('OK', $userData);
    }

    /**
     * @param Request $request
     * @param string $collabPersonId
     * @return JsonResponse
     */
    public function dryRunAction(Request $request, $collabPersonId)
    {
        $this->assertRequestMethod($request, [Request::METHOD_DELETE]);
        $this->assertDeprovisionApiIsEnabled();
        $this->assertUserHasDeprovisionRole();

        $userData = $this->deprovisionService->read(
            $this->createCollabPersonIdValueObject($collabPersonId)
        );

        return $this->createResponse('OK', $userData);
    }


    public function removeAction(Request $request): JsonResponse
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), [Request::METHOD_POST]);
        }

        if (!$this->featureConfiguration->isEnabled('api.consent_remove')) {
            throw new ApiNotFoundHttpException('Consent remove API is disabled');
        }

        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_DEPROVISION')) {
            throw new ApiAccessDeniedHttpException(
                'Access to the consent removal API requires the role ROLE_API_USER_DEPROVISION'
            );
        }

        $userId = $request->get('collabPersonId', false);
        $serviceProviderEntityId = $request->get('serviceProviderEntityId', false);
        if (!$userId || !$serviceProviderEntityId) {
            throw new EngineBlockInvalidArgumentException('The required data for removing the consent is not present in the request parameters');
        }

        try {
            $user = $this->createCollabPersonIdValueObject($userId);
            $removed = $this->deprovisionService->deleteOneConsentFor($user, $serviceProviderEntityId);
        } catch (RuntimeException $e) {
            throw new ApiInternalServerErrorHttpException(
                sprintf(
                    'An unknown error occurred while removing a service the user has given consent for to ' .
                    'release attributes to ("%s")',
                    $e->getMessage()
                ),
                $e
            );
        }

        return new JsonResponse($removed, Response::HTTP_OK);
    }

    /**
     * @param string $status
     * @param array $userData
     * @param string|null $message
     * @return JsonResponse
     */
    private function createResponse($status, array $userData, $message = null)
    {
        $responseData = [
            'status'  => $status,
            'name'    => $this->applicationName,
            'data'    => $userData,
        ];

        if ($message !== null) {
            $responseData['message'] = $message;
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    /**
     * @throws ApiNotFoundHttpException
     */
    private function assertDeprovisionApiIsEnabled()
    {
        if (!$this->featureConfiguration->isEnabled('api.deprovision')) {
            throw new ApiNotFoundHttpException('Deprovision API is disabled');
        }
    }

    /**
     * @param Request $request
     * @param array $expectedMethods
     *
     * @throws ApiMethodNotAllowedHttpException
     */
    private function assertRequestMethod(Request $request, array $expectedMethods)
    {
        foreach ($expectedMethods as $expectedMethod) {
            if ($request->isMethod($expectedMethod)) {
                return;
            }
        }

        throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), $expectedMethods);
    }

    /**
     * @throws ApiAccessDeniedHttpException
     */
    private function assertUserHasDeprovisionRole()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_DEPROVISION')) {
            throw new ApiAccessDeniedHttpException(
                'Access to the content listing API requires the role ROLE_API_USER_DEPROVISION'
            );
        }
    }

    /**
     * @param string $id
     * @return CollabPersonId
     *
     * @throws ApiNotFoundHttpException
     */
    private function createCollabPersonIdValueObject($id)
    {
        try {
            return new CollabPersonId($id);
        } catch (InvalidArgumentException $e) {
            throw new ApiNotFoundHttpException(
                sprintf(
                    'User ID is not valid: "%s"',
                    $e->getMessage()
                )
            );
        }
    }
}
