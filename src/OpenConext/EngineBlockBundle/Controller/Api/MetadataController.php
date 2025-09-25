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

use OpenConext\EngineBlock\Service\MetadataServiceInterface;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiMethodNotAllowedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Response\JsonResponse;
use OpenConext\EngineBlockBundle\Http\Response\JsonHelper;
use OpenConext\Value\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\EntityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Static calls, factories, and having to check HTTP methods which is
 *                                                 usually done by Symfony
 */
final class MetadataController
{
    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var FeatureConfiguration
     */
    private $featureConfiguration;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AccessDecisionManagerInterface $accessDecisionManager,
        FeatureConfiguration $featureConfiguration,
        MetadataServiceInterface $metadataService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->featureConfiguration = $featureConfiguration;
        $this->metadataService      = $metadataService;
    }

    /**
     * @Route("/metadata/idp", name="api_metadata_idp", defaults={"_format"="json"})
     */
    public function idpAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_GET)) {
            throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), [Request::METHOD_GET]);
        }

        $entityIdValue = $request->query->get('entity-id');

        if (!$this->featureConfiguration->isEnabled('api.metadata_api')) {
            throw new ApiNotFoundHttpException('Metadata API is disabled');
        }

        $this->assertAuthorized();

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
            throw new ApiNotFoundHttpException();
        }

        return new JsonResponse(JsonHelper::serializeIdentityProvider($identityProvider), JsonResponse::HTTP_OK);
    }

    private function assertAuthorized(): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            throw new AuthenticationCredentialsNotFoundException('The token storage contains no authentication token.');
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_API_USER_PROFILE'], null)) {
            throw new ApiAccessDeniedHttpException(
                'Access to the Metadata API requires the role ROLE_API_USER_PROFILE'
            );
        }
    }
}
