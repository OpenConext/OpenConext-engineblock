<?php
namespace OpenConext\EngineBlockBundle\Configuration;

interface FeatureConfigurationInterface
{
    /**
     * @param string $featureKey
     * @return bool
     */
    public function hasFeature($featureKey);

    /**
     * @param string $featureKey
     * @return bool
     */
    public function isEnabled($featureKey);
}
