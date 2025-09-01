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

use Exception;
use OpenConext\EngineBlock\Metadata\Entity\Assembler\MetadataAssemblerInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataPushRepository;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiInternalServerErrorHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Request\JsonRequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Static calls, factories, and having to check HTTP methods which is
 *                                                 usually done by Symfony
 * @SuppressWarnings(PHPMD.CyclomaticComplexity) Extensive role validation
 * @SuppressWarnings(PHPMD.NPathComplexity) Extensive role validation
 */
class ConnectionsController
{
    /**
     * @var MetadataAssemblerInterface
     */
    private $pushMetadataAssembler;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    /**
     * @var DoctrineMetadataPushRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $memoryLimit;

    /**
     * @param MetadataAssemblerInterface $assembler
     * @param TokenStorageInterface $tokenStorage
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param FeatureConfigurationInterface $featureConfiguration
     * @param DoctrineMetadataPushRepository $repository
     * @param string|null $memoryLimit
     */
    public function __construct(
        MetadataAssemblerInterface $assembler,
        TokenStorageInterface $tokenStorage,
        AccessDecisionManagerInterface $accessDecisionManager,
        FeatureConfigurationInterface $featureConfiguration,
        DoctrineMetadataPushRepository $repository,
        $memoryLimit
    ) {
        $this->pushMetadataAssembler           = $assembler;
        $this->tokenStorage                    = $tokenStorage;
        $this->accessDecisionManager           = $accessDecisionManager;
        $this->featureConfiguration            = $featureConfiguration;
        $this->repository                      = $repository;
        $this->memoryLimit                     = $memoryLimit;
    }

    /**
     * @Route("/api/connections", name="api_connections", defaults={"_format"="json"})
     */
    public function pushConnectionsAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), [Request::METHOD_POST]);
        }

        if (!$this->featureConfiguration->isEnabled('api.metadata_push')) {
            throw new ApiNotFoundHttpException('Metadata push API is disabled');
        }

        $this->assertAuthorized();

        if ($this->memoryLimit) {
            ini_set('memory_limit', $this->memoryLimit);
        }

        $body = JsonRequestHelper::decodeContentOf($request);

        if (!is_object($body) || !isset($body->connections) || !is_object($body->connections)) {
            throw new BadApiRequestHttpException('Unrecognized structure for JSON');
        }

        try {
            $roles = $this->pushMetadataAssembler->assemble($body->connections);
        } catch (Exception $exception) {
            throw new BadApiRequestHttpException(sprintf('Unable to assemble the pushed metadata: %s', $exception->getMessage()), $exception);
        }

        unset($body);

        try {
            $result = $this->repository->synchronize($roles);
        } catch (Exception $exception) {
            throw new ApiInternalServerErrorHttpException('Unable to synchronize the assembled roles to the repository', $exception);
        }

        return new JsonResponse($result);
    }

    private function assertAuthorized(): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            throw new AuthenticationCredentialsNotFoundException('The token storage contains no authentication token.');
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_API_USER_METADATA_PUSH'], null)) {
            throw new ApiAccessDeniedHttpException(
                'Access to the metadata push API requires the role ROLE_API_USER_METADATA_PUSH'
            );
        }
    }
}
