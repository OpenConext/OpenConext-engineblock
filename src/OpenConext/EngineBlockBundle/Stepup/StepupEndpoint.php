<?php
/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Stepup;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\RuntimeException;

class StepupEndpoint
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $ssoLocation;

    /**
     * @var string
     */
    private $keyFile;


    public function __construct($entityId, $ssoLocation, $keyFile)
    {
        Assertion::string($entityId, 'EntityId should be a string');
        Assertion::string($ssoLocation, 'SSO location should be a string');
        Assertion::string($keyFile, 'KeyFile should be a string');
        Assertion::file($keyFile, sprintf("Keyfile '%s' should be a valid file", $keyFile));

        $this->entityId = $entityId;
        $this->ssoLocation = $ssoLocation;
        $this->keyFile = $keyFile;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getSsoLocation()
    {
        return $this->ssoLocation;
    }

    /**
     * @return string
     */
    public function getKeyFile()
    {
        return $this->keyFile;
    }
}
