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

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;

final class Feature
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var string
     */
    private $featureKey;

    /**
     * @param string $featureKey the key with which this feature is identified
     * @param bool $isEnabled
     */
    public function __construct($featureKey, $isEnabled)
    {
        Assertion::nonEmptyString($featureKey, 'featureKey');
        Assertion::boolean($isEnabled);

        $this->featureKey = $featureKey;
        $this->isEnabled  = $isEnabled;
    }

    /**
     * @return string
     */
    public function getFeatureKey()
    {
        return $this->featureKey;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
