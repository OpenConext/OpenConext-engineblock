<?php

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;

final class Feature
{
    /**
     * @var bool
     */
    private $isEnabled;

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
