<?php

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;

final class FeatureConfiguration
{
    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @param Feature[] $features indexed by feature key
     */
    public function __construct($features)
    {
        Assertion::allIsInstanceOf($features, Feature::class);
        Assertion::allString(array_keys($features), 'All keys for features must be a string (the feature key itself).');

        $this->features = $features;
    }

    /**
     * @param string $featureKey
     * @return bool
     */
    public function hasFeature($featureKey)
    {
        Assertion::nonEmptyString($featureKey, 'featureKey');

        return array_key_exists($featureKey, $this->features);
    }

    /**
     * @param string $featureKey
     * @return bool
     */
    public function isEnabled($featureKey)
    {
        if (!$this->hasFeature($featureKey)) {
            throw new LogicException(sprintf(
                'Cannot state if feature "%s" is enabled as it does not exist. Please ensure that you configured it '
                . 'correctly or verify with hasFeature() that the feature exists.',
                $featureKey
            ));
        }

        return $this->features[$featureKey]->isEnabled();
    }
}
