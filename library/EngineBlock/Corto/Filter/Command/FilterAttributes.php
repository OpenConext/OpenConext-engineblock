<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;

class EngineBlock_Corto_Filter_Command_FilterAttributes extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    /**
     * @var array
     */
    private $filterAttributes;

    public function __construct(FeatureConfigurationInterface $featureConfiguration, ?array $filterAttributes)
    {
        $this->featureConfiguration = $featureConfiguration;
        $this->filterAttributes = $filterAttributes;
    }

    /**
     * Required for manipulation response attributes.
     *
     * @return array
     */
    public function getResponseAttributes(): array
    {
        return $this->_responseAttributes;
    }

    /**
     * If the feature `feature_filter_attributes` is enabled, remove all the configured `filter_attributes` from the
     * response attributes.
     */
    public function execute(): void
    {
        if ($this->featureConfiguration->isEnabled('eb.feature_filter_attributes')) {
            $this->filterAttributes();
        }
    }

    private function filterAttributes(): void
    {
        if (null !== $this->filterAttributes) {
            foreach ($this->filterAttributes as $filterAttribute) {
                unset($this->_responseAttributes[$filterAttribute]);
            }
        }
    }
}
