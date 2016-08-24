<?php

namespace OpenConext\EngineBlockBundle\Controller\Api;

use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Response\JsonResponse;
use OpenConext\EngineBlock\Service\MetadataService;
use OpenConext\EngineBlockBundle\Http\Response\JsonHelper;
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
     * @var FeatureConfiguration
     */
    private $featureConfiguration;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FeatureConfiguration $featureConfiguration,
        MetadataService $metadataService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->featureConfiguration = $featureConfiguration;
        $this->metadataService      = $metadataService;
    }

    public function idpAction(Request $request)
    {
        $entityIdValue = $request->query->get('entity-id');

        if (!$this->featureConfiguration->isEnabled('api.metadata_api')) {
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
            return new JsonResponse('null', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(JsonHelper::serializeIdentityProvider($identityProvider), JsonResponse::HTTP_OK);
    }
}
