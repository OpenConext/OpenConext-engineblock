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

/**
 * By default the feature configuration enables the features, they can be reset/disabled
 * by calling the setFeature method.
 */
class TestFeatureConfiguration implements FeatureConfigurationInterface
{
    /**
     * @var Feature[]
     */
    private $features = [];

    public function __construct()
    {
        $this->setFeature(new Feature('api.deprovision', true));
        $this->setFeature(new Feature('api.metadata_push', true));
        $this->setFeature(new Feature('api.consent_listing', true));
        $this->setFeature(new Feature('eb.run_all_manipulations_prior_to_consent', false));
        $this->setFeature(new Feature('eb.block_user_on_violation', true));
        $this->setFeature(new Feature('eb.encrypted_assertions', true));
        $this->setFeature(new Feature('eb.encrypted_assertions_require_outer_signature', true));
        $this->setFeature(new Feature('eb.enable_sso_notification', false));
        $this->setFeature(new Feature('eb.feature_enable_consent', true));
    }

    public function setFeature(Feature $feature): void
    {
        $this->features[$feature->getFeatureKey()] = $feature;
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
