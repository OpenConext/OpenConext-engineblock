<?php

namespace OpenConext\EngineBlock\ApiBundle\Controller;

use OpenConext\EngineBlock\ApiBundle\Exception\RuntimeException;
use OpenConext\EngineBlock\ApiBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlock\ApiBundle\Http\Exception\ApiInternalServerErrorHttpException;
use OpenConext\EngineBlock\ApiBundle\Service\ConsentService;
use OpenConext\EngineBlock\ApiBundle\Service\FeaturesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ConsentController
{
    /**
     * @var ConsentService
     */
    private $consentService;

    /**
     * @var FeaturesService
     */
    private $featuresService;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        ConsentService $consentService,
        FeaturesService $featuresService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->consentService       = $consentService;
        $this->featuresService      = $featuresService;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function userAction($userId)
    {
        if (!$this->featuresService->consentListingIsEnabled()) {
            throw new NotFoundHttpException('Consent listing API is disabled');
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
