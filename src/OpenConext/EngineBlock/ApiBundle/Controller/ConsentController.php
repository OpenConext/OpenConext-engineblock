<?php

namespace OpenConext\EngineBlock\ApiBundle\Controller;

use OpenConext\EngineBlock\ApiBundle\Exception\RuntimeException;
use OpenConext\EngineBlock\ApiBundle\Http\Exception\ApiInternalServerErrorHttpException;
use OpenConext\EngineBlock\ApiBundle\Service\ConsentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ConsentController
{
    /**
     * @var ConsentService
     */
    private $consentService;

    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }

    public function userAction($userId)
    {
        try {
            $consentList = $this->consentService->findAll($userId)->jsonSerialize();
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
