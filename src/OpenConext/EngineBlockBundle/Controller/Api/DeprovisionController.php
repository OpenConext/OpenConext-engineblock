<?php

namespace OpenConext\EngineBlockBundle\Controller\Api;

use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlock\Service\DeprovisionService;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

        $userData = $this->deprovisionService->read($collabPersonId);

        if ($request->isMethod(Request::METHOD_DELETE)) {
            $this->deprovisionService->delete($collabPersonId);
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

        $userData = $this->deprovisionService->read($collabPersonId);

        return $this->createResponse('OK', $userData);
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
}
