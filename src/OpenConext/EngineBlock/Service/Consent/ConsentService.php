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

namespace OpenConext\EngineBlock\Service;

use Exception;
use OpenConext\EngineBlock\Authentication\Dto\Consent;
use OpenConext\EngineBlock\Authentication\Dto\ConsentList;
use OpenConext\EngineBlock\Authentication\Model\Consent as ConsentEntity;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\Value\Saml\EntityId;
use Psr\Log\LoggerInterface;

final class ConsentService implements ConsentServiceInterface
{
    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConsentRepository $consentRepository,
        MetadataService $metadataService,
        LoggerInterface $logger
    ) {
        $this->consentRepository = $consentRepository;
        $this->metadataService   = $metadataService;
        $this->logger            = $logger;
    }

    /**
     * @param string $userId
     * @return ConsentList
     */
    public function findAllFor($userId)
    {
        try {
            $consents = $this->consentRepository->findAllFor($userId);
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('An exception occurred while fetching consents the user has given ("%s")', $e->getMessage()),
                0,
                $e
            );
        }

        return new ConsentList(array_filter(array_map([$this, 'createConsentDtoFromConsentEntity'], $consents)));
    }

    /**
     * @param string $userId
     * @return int
     */
    public function countAllFor($userId)
    {
        try {
            $consents = $this->consentRepository->findAllFor($userId);
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('An exception occurred while fetching consents the user has given ("%s")', $e->getMessage()),
                0,
                $e
            );
        }

        return count($consents);
    }

    /**
     * @param ConsentEntity $consent
     * @return Consent|null
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) it is used as callable in the findForAll() method
     */
    private function createConsentDtoFromConsentEntity(ConsentEntity $consent)
    {
        $entityId        = $consent->getServiceProviderEntityId();
        $serviceProvider = $this->metadataService->findServiceProvider(new EntityId($entityId));

        if ($serviceProvider === null) {
            return null;
        }

        return new Consent($consent, $serviceProvider);
    }
}
