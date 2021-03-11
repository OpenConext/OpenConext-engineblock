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
     * @var array
     */
    private $pdpLoas = [];
    /**
     * @var bool
     */
    private $spNoToken;

    /**
     * @param IdentityProvider $idp
     * @param ServiceProvider $sp
     * @param array $pdpLoas
     * @param LoaRepository $loaRepository
     * @throws InvalidStepupConfigurationException
     */
    public function __construct(
        IdentityProvider $idp,
        ServiceProvider $sp,
        array $pdpLoas,
        LoaRepository $loaRepository
    ) {
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

        foreach ($pdpLoas as $loaId) {
            $this->pdpLoas[] = $loaRepository->getByIdentifier($loaId);
        }
    }

    /**
     * @return  bool
     */
    public function shouldUseStepup()
    {
        return ($this->spLoa || $this->idpLoa || count($this->pdpLoas) > 0);
    }

    /**
     * Find the highest level among all ways to configure a LoA.
     * @return Loa|null
     */
    public function getStepupLoa()
    {
        $desiredLevels = $this->pdpLoas;
        if ($this->spLoa) {
            $desiredLevels[] = $this->spLoa;
        }
        if ($this->idpLoa) {
            $desiredLevels[] = $this->idpLoa;
        }

        if (count($desiredLevels) == 0) {
            return null;
        }

        $highestLevel = reset($desiredLevels);
        foreach ($desiredLevels as $level) {
            if ($level->levelIsHigherOrEqualTo($highestLevel)) {
                $highestLevel = $level;
            }
        }

        return $highestLevel;
    }

    /**
     * @return bool
     */
    public function allowNoToken()
    {
        if ($this->spLoa || $this->idpLoa || count($this->pdpLoas) > 0) {
            return $this->spNoToken;
        }

        return false;
    }
}
