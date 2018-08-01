<?php

namespace OpenConext\EngineBlockBundle\Controller\Api;

use OpenConext\EngineBlock\Metadata\Entity\Assembler\PushMetadataAssembler;
use OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Request\JsonRequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Static calls, factories, and having to check HTTP methods which is
 *                                                 usually done by Symfony
 */
class ConnectionsController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var FeatureConfiguration
     */
    private $featureConfiguration;

    /**
     * @var DoctrineMetadataRepository
     */
    private $repository;

    /**
     * @param AuthorizationCheckerInterface    $authorizationChecker
     * @param FeatureConfiguration             $featureConfiguration
     * @param DoctrineMetadataRepository       $repository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FeatureConfiguration $featureConfiguration,
        DoctrineMetadataRepository $repository
    ) {
        $this->authorizationChecker            = $authorizationChecker;
        $this->featureConfiguration            = $featureConfiguration;
        $this->repository                      = $repository;
    }

    public function pushConnectionsAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), [Request::METHOD_POST]);
        }

        if (!$this->featureConfiguration->isEnabled('api.metadata_push')) {
            throw new ApiNotFoundHttpException('Metadata push API is disabled');
        }

        if (!$this->authorizationChecker->isGranted(['ROLE_API_USER_METADATA_PUSH'])) {
            throw new ApiAccessDeniedHttpException(
                'Access to the metadata push API requires the role ROLE_API_USER_METADATA_PUSH'
            );
        }

        ini_set('memory_limit', '265M');

        $body = JsonRequestHelper::decodeContentOf($request);

        if (!is_object($body) || !isset($body->connections) && !is_object($body->connections)) {
            throw new BadApiRequestHttpException('Unrecognized structure for JSON');
        }

        $assembler = new PushMetadataAssembler();
        $roles     = $assembler->assemble($body->connections);
        $result    = $this->repository->synchronize($roles);

        return new JsonResponse($result);
    }
}
