<?php

namespace OpenConext\EngineBlock\ApiBundle\Controller;

use EngineBlock_ApplicationSingleton;
use OpenConext\Component\EngineBlockMetadata\Entity\Assembler\JanusPushMetadataAssembler;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\DoctrineMetadataRepository;
use OpenConext\EngineBlock\ApiBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlock\ApiBundle\Http\Request\JsonRequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ConnectionsController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     */
    public function __construct(EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton)
    {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
    }

    public function pushConnectionsAction(Request $request)
    {
        ini_set('memory_limit', '265M');

        $body = JsonRequestHelper::decodeContentOf($request);

        if (!is_object($body) || !isset($body->connections) && !is_object($body->connections)) {
            throw new BadApiRequestHttpException('Unrecognized structure for JSON');
        }

        $assembler = new JanusPushMetadataAssembler();
        $roles     = $assembler->assemble($body->connections);

        $doctrineRepository = DoctrineMetadataRepository::createFromConfig(
            array(),
            $this->engineBlockApplicationSingleton->getDiContainer()
        );

        $result             = $doctrineRepository->synchronize($roles);

        return new JsonResponse($result);
    }
}
