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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Configuration\TestFeatureConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use RuntimeException;

final class FunctionalTestingFeatureConfiguration implements FeatureConfigurationInterface
{
    /**
     * @var FeatureConfiguration
     */
    private $featureConfiguration;

    /**
     * @var Feature[]
     */
    private $featureConfigurationFixture;

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    public function __construct(FeatureConfigurationInterface $featureConfiguration, AbstractDataStore $dataStore)
    {
        $this->featureConfiguration = $featureConfiguration;
        $this->dataStore            = $dataStore;

        $this->featureConfigurationFixture = $dataStore->load();
    }

    public function hasFeature($featureKey)
    {
        return $this->featureConfiguration->hasFeature($featureKey);
    }

    public function isEnabled($featureKey)
    {
        if (isset($this->featureConfigurationFixture[$featureKey])) {
            return $this->featureConfigurationFixture[$featureKey];
        }

        return $this->featureConfiguration->isEnabled($featureKey);
    }

    public function save($featureKey, $value)
    {
        if (!$this->featureConfiguration->hasFeature($featureKey)) {
            throw new RuntimeException(sprintf(
                'Cannot save fixture for feature "%s": it is not configured in the actual feature configuration',
                $featureKey
            ));
        }

        $this->featureConfigurationFixture[$featureKey] = $value;
        $this->featureConfiguration->setFeature(new Feature($featureKey, $value));
        $this->dataStore->save($this->featureConfigurationFixture);
    }

    public function clean()
    {
        $this->featureConfigurationFixture = [];
        $this->dataStore->save($this->featureConfigurationFixture);
    }
}
