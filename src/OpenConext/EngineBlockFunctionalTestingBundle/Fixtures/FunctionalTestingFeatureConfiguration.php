<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
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

    public function __construct(FeatureConfiguration $featureConfiguration, AbstractDataStore $dataStore)
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
        $this->dataStore->save($this->featureConfigurationFixture);
    }

    public function clean()
    {
        $this->featureConfigurationFixture = [];
        $this->dataStore->save($this->featureConfigurationFixture);
    }
}
