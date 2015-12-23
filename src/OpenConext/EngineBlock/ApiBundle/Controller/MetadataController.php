<?php

namespace OpenConext\EngineBlock\ApiBundle\Controller;

use OpenConext\EngineBlock\ApiBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlock\ApiBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlock\ApiBundle\Http\Response\JsonResponse;
use OpenConext\EngineBlock\ApiBundle\Service\FeaturesService;
use OpenConext\EngineBlock\ApiBundle\Service\MetadataService;
use OpenConext\EngineBlock\CompatibilityBundle\Http\Response\JsonHelper;
use OpenConext\Value\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\EntityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class MetadataController
{
    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var FeaturesService
     */
    private $featuresService;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        MetadataService $metadataService,
        FeaturesService $featuresService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->metadataService      = $metadataService;
        $this->featuresService      = $featuresService;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function idpAction(Request $request)
    {
        $entityIdValue = $request->query->get('entity-id');

        if (!$this->featuresService->metadataApiIsEnabled()) {
            throw new NotFoundHttpException('Metadata API is disabled');
        }

        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_PROFILE')) {
            throw new ApiAccessDeniedHttpException(
                'Access to the Metadata API requires the role ROLE_API_USER_PROFILE'
            );
        }

        try {
            $entityId = new EntityId($entityIdValue);
        } catch (InvalidArgumentException $exception) {
            throw new BadApiRequestHttpException(sprintf(
                'Could not get metadata for IdP: invalid EntityId format ("%s")',
                $exception->getMessage()
            ));
        }

        $identityProvider = $this->metadataService->findIdentityProvider($entityId);

        if ($identityProvider === null) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(JsonHelper::serializeIdentityProvider($identityProvider), JsonResponse::HTTP_OK);
    }
}
