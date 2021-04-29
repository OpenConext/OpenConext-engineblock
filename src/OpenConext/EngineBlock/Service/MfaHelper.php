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

use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Metadata\TransparentMfaEntity;
use Psr\Log\LoggerInterface;
use function sprintf;

class MfaHelper implements MfaHelperInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    public function __construct(LoggerInterface $logger, MetadataRepositoryInterface $metadataRepository)
    {
        $this->logger = $logger;
        $this->metadataRepository = $metadataRepository;
    }

    public function isTransparent(string $spEntityId, string $idpEntityId): bool
    {
        $this->logger->debug(sprintf('Test if SP %s is configured with transparant_authn_context via the IdP', $spEntityId));
        $remoteIdP = $this->metadataRepository->findIdentityProviderByEntityId($idpEntityId);
        if (!$remoteIdP) {
            $this->logger->warning('The IdP can not be found');
            return false;
        }
        $mfaEntities = $remoteIdP->getCoins()->mfaEntities();
        $mfaEntity = $mfaEntities->findByEntityId($spEntityId);
        if (!$mfaEntity) {
            $this->logger->debug('The SP is not an MFA entity');
            return false;
        }
        return $mfaEntity instanceof TransparentMfaEntity;
    }
}
