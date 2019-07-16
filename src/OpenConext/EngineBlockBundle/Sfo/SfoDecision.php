<?php
/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Sfo;

use OpenConext\EngineBlock\Exception\InvalidSfoConfigurationException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class SfoDecision
{
    /**
     * @var string|null
     */
    private $idpLoa;
    /**
     * @var string|null
     */
    private $spLoa;
    /**
     * @var bool
     */
    private $spNoToken;

    /**
     * @param IdentityProvider $idp
     * @param ServiceProvider $sp
     * @throws InvalidSfoConfigurationException
     */
    public function __construct(IdentityProvider $idp, ServiceProvider $sp)
    {
        $this->idpLoa = $idp->getCoins()->stepupConnections()->getLoa($sp->entityId);
        $this->spLoa = $sp->getCoins()->stepupRequireLoa();
        $this->spNoToken = $sp->getCoins()->stepupAllowNoToken();

        if ($this->spLoa && $this->idpLoa) {
            throw new InvalidSfoConfigurationException(sprintf(
                'Both IdP "%s" and SP "%s" where configured to use stepup authentication. This is not allowed',
                $idp->entityId,
                $sp->entityId
            ));
        }
    }

    /**
     * @return  bool
     */
    public function shouldUseSfo()
    {
        return ($this->spLoa || $this->idpLoa);
    }

    /**
     * @return string|null
     */
    public function getSfoLoa()
    {
        if ($this->spLoa) {
            return $this->spLoa;
        }

        if ($this->idpLoa) {
            return $this->idpLoa;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function allowNoToken()
    {
        if ($this->spLoa || $this->idpLoa) {
            return $this->spNoToken;
        }

        return false;
    }
}
