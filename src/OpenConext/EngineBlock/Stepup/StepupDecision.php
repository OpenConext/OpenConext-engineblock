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

namespace OpenConext\EngineBlock\Stepup;

use OpenConext\EngineBlock\Exception\InvalidStepupConfigurationException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;
use Psr\Log\LoggerInterface;
use function count;

class StepupDecision
{
    /**
     * @var string|null
     */
    private $idpLoa = null;
    /**
     * @var string|null
     */
    private $spLoa = null;
    /**
     * @var Loa[]
     */
    private $authnRequestLoas = [];
    /**
     * @var Loa[]
     */
    private $pdpLoas = [];
    /**
     * @var bool
     */
    /**
     * @var string|null
     */
    private $idpResponseLoa = null;

    private $spNoToken;

    private $logger;

    /**
     * @throws InvalidStepupConfigurationException
     */
    public function __construct(
        IdentityProvider $idp,
        ServiceProvider $sp,
        array $authnRequestLoas,
        array $pdpLoas,
        string|null $idpResponseLoa,
        LoaRepository $loaRepository,
        LoggerInterface $logger
    ) {

        $this->logger = $logger;

        $idpLoa = $idp->getCoins()->stepupConnections()->getLoa($sp->entityId);
        // Only load the IdP LoA if configured in the stepup connection coin data
        if ($idpLoa) {
            $this->idpLoa = $loaRepository->getByIdentifier($idpLoa);
        }

        $spLoa = $sp->getCoins()->stepupRequireLoa();
        // Only load the SP LoA if configured in Manage
        if ($spLoa) {
            $this->spLoa = $loaRepository->getByIdentifier($spLoa);
        }

        $this->spNoToken = $sp->getCoins()->stepupAllowNoToken();

        // Only set idpResponseLoa if provided and valid. Use getByIdentifier and ignore when not found.
        if ($idpResponseLoa) {
            try {
                $this->idpResponseLoa = $loaRepository->getByIdentifier($idpResponseLoa);
            } catch (\Exception $e) {
                // The repository will throw when identifier is not known; log and ignore invalid response LoA
                $this->logger->debug(sprintf('StepupDecision: IdP Response LoA "%s" is invalid and will be ignored', $idpResponseLoa));
            }
        }

        foreach ($pdpLoas as $loaId) {
            $this->pdpLoas[] = $loaRepository->getByIdentifier($loaId);
        }
        foreach ($authnRequestLoas as $loa) {
            $this->authnRequestLoas[] = $loa;
        }
    }

    public function shouldUseStepup(): bool
    {
        // If the highest level is 1, no step up callout is required.
        $isLoaAsked = $this->getStepupLoa();
        if ($isLoaAsked && $isLoaAsked->getLevel() === Loa::LOA_1) {
            return false;
        }
        // If the Loa is reached by the IDP, no stepup callout is required.
        if ($this->checkIDPLoaIsSufficient($isLoaAsked)) {
            $this->logger->info('StepupDecision: IdP Response LoA is sufficient, no Stepup required');
            return false;
        }
        return $isLoaAsked instanceof Loa;
    }

    private function isLoaRequirementSet(): bool
    {
        // If the highest level is 1, no step up callout is requred.
        if ($this->getStepupLoa() && $this->getStepupLoa()->getLevel() === Loa::LOA_1) {
            return false;
        }
        return ($this->spLoa || $this->idpLoa || count($this->authnRequestLoas) > 0 || count($this->pdpLoas) > 0);
    }

    /**
     * Find the highest level among all ways to configure a LoA.
     */
    public function getStepupLoa(): ?Loa
    {
        $this->logger->debug('StepupDecision: determine highest LoA');

        $desiredLevels = $this->pdpLoas;
        $desiredLevels += $this->authnRequestLoas;
        if ($this->spLoa) {
            $desiredLevels[] = $this->spLoa;
        }
        if ($this->idpLoa) {
            $desiredLevels[] = $this->idpLoa;
        }

        if (count($desiredLevels) == 0) {
            $this->logger->info('StepupDecision: no level set, no Stepup required');
            return null;
        }

        $highestLevel = reset($desiredLevels);
        foreach ($desiredLevels as $level) {
            if ($level->levelIsHigherOrEqualTo($highestLevel)) {
                $highestLevel = $level;
            }
        }

        $logData = [
            'pdp' => array_map(function (Loa $l):string {
                return $l->getIdentifier();
            }, $this->pdpLoas),
            'authnRequest' => array_map(function (Loa $l):string {
                return $l->getIdentifier();
            }, $this->authnRequestLoas),
            'metadata_sp' => $this->spLoa ? [$this->spLoa->getIdentifier()] : [],
            'metadata_idp' => $this->idpLoa ? [$this->idpLoa->getIdentifier()] : [],
        ];
        $this->logger->info(sprintf('StepupDecision: requiring LoA %s', $highestLevel->getIdentifier()), $logData);
        return $highestLevel;
    }

    /**
     * Check if the IDP response LoA is sufficient for the requested LoA.
     */
    private function checkIDPLoaIsSufficient($isLoaAsked): bool
    {
        if ($this->idpResponseLoa) {
            return false;
        }

        // If the IdP did not provide a LoA in the response, it cannot satisfy the requirement
        if (!$this->idpResponseLoa) {
            return false;
        }

        // Check whether the IdP response LoA level meets or exceeds the requested LoA
        return $this->idpResponseLoa->levelIsHigherOrEqualTo($isLoaAsked);
    }


    public function allowNoToken(): bool
    {
        if ($this->isLoaRequirementSet()) {
            return $this->spNoToken;
        }

        return false;
    }
}
