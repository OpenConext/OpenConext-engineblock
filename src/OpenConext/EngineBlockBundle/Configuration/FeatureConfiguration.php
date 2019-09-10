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

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;

class FeatureConfiguration implements FeatureConfigurationInterface
{
    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @param Feature[] $features indexed by feature key
     */
    public function __construct(array $features)
    {
        Assertion::allIsInstanceOf($features, Feature::class);
        Assertion::allString(array_keys($features), 'All keys for features must be a string (the feature key itself).');

        $this->features = $features;
    }

    public function hasFeature($featureKey)
    {
        Assertion::nonEmptyString($featureKey, 'featureKey');

        return array_key_exists($featureKey, $this->features);
    }

    public function isEnabled($featureKey)
    {
        if (!$this->hasFeature($featureKey)) {
            $features = implode(
                ', ',
                array_map(
                    function (Feature $feature) {
                        return $feature->getFeatureKey();
                    },
                    $this->features
                )
            );
            throw new LogicException(
                sprintf(
                    'Cannot state if feature "%s" is enabled as it does not exist. Please ensure that you configured it '
                    .'correctly or verify with hasFeature() that the feature exists. Features configured: "%s"',
                    $featureKey,
                    $features
                )
            );
        }

        return $this->features[$featureKey]->isEnabled();
    }
}
