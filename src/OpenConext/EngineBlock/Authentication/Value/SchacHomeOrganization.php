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

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

/**
 * Represents the value of a SAML Attribute with the attribute name
 * urn:mace:terena.org:attribute-def:schacHomeOrganization
 */
final class SchacHomeOrganization
{
    const URN_MACE = 'urn:mace:terena.org:attribute-def:schacHomeOrganization';

    /**
     * @var string
     */
    private $schacHomeOrganization;

    /**
     * @param string $schacHomeOrganization
     */
    public function __construct($schacHomeOrganization)
    {
        Assertion::nonEmptyString($schacHomeOrganization, 'schacHomeOrganization');

        $this->schacHomeOrganization = $schacHomeOrganization;
    }

    /**
     * @return string
     */
    public function getSchacHomeOrganization()
    {
        return $this->schacHomeOrganization;
    }

    /**
     * @param SchacHomeOrganization $other
     * @return bool
     */
    public function equals(SchacHomeOrganization $other)
    {
        return $this->schacHomeOrganization === $other->schacHomeOrganization;
    }

    public function __toString()
    {
        return sprintf('SchacHomeOrganization(%s)', $this->schacHomeOrganization);
    }
}
