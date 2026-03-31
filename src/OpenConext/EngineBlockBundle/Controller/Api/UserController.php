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

use OpenConext\EngineBlock\Service\NameIdLookupService;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\ApiNotFoundHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class UserController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly FeatureConfigurationInterface $featureConfiguration,
        private readonly NameIdLookupService $nameIdLookupService,
        private readonly LoggerInterface $logger
    ) {
    }

    private function getCallerUsername(): string
    {
        return $this->tokenStorage->getToken()?->getUserIdentifier() ?? 'unknown';
    }

    #[Route(path: '/info/users/nameid', name: 'api_users_nameid', methods: ['POST'], defaults: ['_format' => 'json'])]
    public function nameIdAction(Request $request): JsonResponse
    {
        if (!$this->featureConfiguration->isEnabled('api.users_nameid')) {
            throw new ApiNotFoundHttpException('NameID lookup API is disabled');
        }

        $this->assertAuthorized();

        $entries = $this->decodeJsonArray($request);

        $this->logger->info('NameID lookup requested', [
            'caller' => $this->getCallerUsername(),
            'count'  => count($entries),
        ]);

        $results = [];
        foreach ($entries as $entry) {
            $this->assertEntryHasRequiredFields($entry, ['schacHomeOrganization', 'uid', 'sp_entityid']);
            $results[] = $this->nameIdLookupService->resolveNameId(
                $entry['schacHomeOrganization'],
                $entry['uid'],
                $entry['sp_entityid']
            );
        }

        return new JsonResponse($results, Response::HTTP_OK);
    }

    #[Route(path: '/info/users/id', name: 'api_users_id', methods: ['POST'], defaults: ['_format' => 'json'])]
    public function userIdentityAction(Request $request): JsonResponse
    {
        if (!$this->featureConfiguration->isEnabled('api.users_id')) {
            throw new ApiNotFoundHttpException('User identity lookup API is disabled');
        }

        $this->assertAuthorized();

        $nameIds = $this->decodeJsonArray($request);

        $this->logger->info('User identity lookup requested', [
            'caller' => $this->getCallerUsername(),
            'count'  => count($nameIds),
        ]);

        $results = [];
        foreach ($nameIds as $nameId) {
            if (!is_string($nameId)) {
                throw new BadApiRequestHttpException('Each entry in the request must be a string NameID');
            }
            if (!preg_match('/^[0-9a-f]{40}$/i', $nameId)) {
                throw new BadApiRequestHttpException(
                    sprintf('Invalid NameID format "%s": must be a 40-character hexadecimal SHA1 string', $nameId)
                );
            }
            $results[] = $this->nameIdLookupService->resolveUserIdentity($nameId);
        }

        return new JsonResponse($results, Response::HTTP_OK);
    }

    private function assertAuthorized(): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            throw new AuthenticationCredentialsNotFoundException(
                'The token storage contains no authentication token.'
            );
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_API_USER_NAMEID_LOOKUP'], null)) {
            throw new ApiAccessDeniedHttpException(
                'Access to the NameID lookup API requires the role ROLE_API_USER_NAMEID_LOOKUP'
            );
        }
    }

    private function decodeJsonArray(Request $request): array
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new BadApiRequestHttpException(
                sprintf('Request body must be a valid JSON array. JSON error: %s', json_last_error_msg())
            );
        }

        return $data;
    }

    private function assertEntryHasRequiredFields(mixed $entry, array $requiredFields): void
    {
        if (!is_array($entry)) {
            throw new BadApiRequestHttpException('Each entry in the request must be a JSON object');
        }

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $entry)) {
                throw new BadApiRequestHttpException(
                    sprintf('Missing required field "%s" in request entry', $field)
                );
            }

            if (!is_string($entry[$field]) || $entry[$field] === '') {
                throw new BadApiRequestHttpException(
                    sprintf('Field "%s" must be a non-empty string', $field)
                );
            }
        }
    }
}
