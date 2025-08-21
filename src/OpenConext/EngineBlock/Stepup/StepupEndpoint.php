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

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\InvalidStepupConfigurationException;
use Assert\AssertionFailedException;

class StepupEndpoint
{
    /**
     * @var string|null
     */
    private $entityId;

    /**
     * @var string|null
     */
    private $ssoLocation;

    /**
     * @var string|null
     */
    private $keyFile;

    /**
     * @var bool
     */
    private $isValidated;

    public function __construct(?string $entityId, ?string $ssoLocation, ?string $keyFile)
    {
        $this->entityId = $entityId;
        $this->ssoLocation = $ssoLocation;
        $this->keyFile = $keyFile;
        $this->isValidated = false;
    }

    /**
     * @throws InvalidStepupConfigurationException
     */
    public function getEntityId() : string
    {
        $this->validate();
        return $this->entityId;
    }

    /**
     * @throws InvalidStepupConfigurationException
     */
    public function getSsoLocation() : string
    {
        $this->validate();
        return $this->ssoLocation;
    }

    /**
     * @throws InvalidStepupConfigurationException
     */
    public function getKeyFile() : string
    {
        $this->validate();
        return $this->keyFile;
    }

    /**
     * @throws InvalidStepupConfigurationException
     */
    private function validate() : void
    {
        if ($this->isValidated) {
            return;
        }

        try {
            Assertion::string($this->entityId, 'stepup.gateway.sfo.entity_id should be a string');
            Assertion::string($this->ssoLocation, 'stepup.gateway.sfo.sso_location should be a string');
            Assertion::string($this->keyFile, 'stepup.gateway.sfo.key_file should be a string');
            Assertion::file($this->keyFile, "stepup.gateway.sfo.key_file should be a valid file, not '$this->keyFile'");
        } catch (AssertionFailedException $e) {
            throw new InvalidStepupConfigurationException(sprintf('Invalid stepup endpoint configuration: %s', $e->getMessage()));
        }

        $this->isValidated = true;
    }
}
