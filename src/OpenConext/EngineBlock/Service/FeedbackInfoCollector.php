<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service;

use DateTime;
use DateTimeInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Request\RequestId;
use OpenConext\EngineBlockBundle\Exception\Art;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

/**
 * If trouble occurs, this class gathers the details from the session.
 */
class FeedbackInfoCollector implements FeedbackInfoCollectorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RequestId $requestId,
        private readonly MetadataRepositoryInterface $metadataRepository,
        private readonly LocaleProvider $localeProvider,
        private readonly FeedbackStateHelperInterface $feedbackStateHelper,
    ) {
    }

    public function collect(Throwable $exception): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $feedbackInfo = [
            'datetime'   => (new DateTime())->format(DateTimeInterface::ATOM),
            'requestUrl' => $request !== null
                ? sprintf('%s%s', $request->getSchemeAndHttpHost(), $request->getPathInfo())
                : 'N/A',
            'requestId'  => $this->requestId->get(),
            'ipAddress'  => $request?->getClientIp() ?? 'N/A',
            'artCode'    => Art::forException($exception),
        ];

        $bucket = $this->feedbackStateHelper->getActiveFlowContext();

        $spEntityId = $bucket['originalServiceProvider'] ?? $bucket['serviceProvider'] ?? null;
        if ($spEntityId !== null) {
            $feedbackInfo['serviceProvider']     = $spEntityId;
            $feedbackInfo['serviceProviderName'] = $this->getEntityDisplayName('sp', $spEntityId);
        }

        if (isset($bucket['proxyServiceProvider'])) {
            $feedbackInfo['proxyServiceProvider'] = $bucket['proxyServiceProvider'];
        }

        if (isset($bucket['identityProvider'])) {
            $idpEntityId = $bucket['identityProvider'];
            $feedbackInfo['identityProvider']     = $idpEntityId;
            $feedbackInfo['identityProviderName'] = $this->getEntityDisplayName('idp', $idpEntityId);
        }

        return $feedbackInfo;
    }

    private function getEntityDisplayName(string $type, string $entityId): string
    {
        $locale = $this->localeProvider->getLocale();
        try {
            $entity = $type === 'sp'
                ? $this->metadataRepository->fetchServiceProviderByEntityId($entityId)
                : $this->metadataRepository->fetchIdentityProviderByEntityId($entityId);
            return $entity->getDisplayName($locale);
        } catch (EntityNotFoundException) {
            return '';
        }
    }
}
