<?php

namespace OpenConext\EngineBlockBundle\Controller\Api;

use EngineBlock_ApplicationSingleton;
use OpenConext\Component\EngineBlockMetadata\Entity\Assembler\JanusPushMetadataAssembler;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Request\JsonRequestHelper;
use OpenConext\EngineBlock\Service\FeaturesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConnectionsController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var FeaturesService
     */
    private $featuresService;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param AuthorizationCheckerInterface    $authorizationChecker
     * @param FeaturesService                  $featuresService
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        AuthorizationCheckerInterface $authorizationChecker,
        FeaturesService $featuresService
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->authorizationChecker = $authorizationChecker;
        $this->featuresService = $featuresService;
    }

    public function pushConnectionsAction(Request $request)
    {
        if (!$this->featuresService->metadataPushIsEnabled()) {
            return new JsonResponse(null, 404);
        }

        if (!$this->authorizationChecker->isGranted(['ROLE_API_USER_JANUS'])) {
            throw new ApiAccessDeniedHttpException();
        }

        ini_set('memory_limit', '265M');

        $body = JsonRequestHelper::decodeContentOf($request);

        if (!is_object($body) || !isset($body->connections) && !is_object($body->connections)) {
            throw new BadApiRequestHttpException('Unrecognized structure for JSON');
        }

        $assembler = new JanusPushMetadataAssembler();
        $roles     = $assembler->assemble($body->connections);

        $diContainer = $this->engineBlockApplicationSingleton->getDiContainer();
        $doctrineRepository = DoctrineMetadataRepository::createFromConfig([], $diContainer);

        $result = $doctrineRepository->synchronize($roles);

        return new JsonResponse($result);
    }
}
